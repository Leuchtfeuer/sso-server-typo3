<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2003-2012 Dietrich Heise (typo3-ext@naw.info)
 *  (c) 2012-2015 Helmut Hummel (info@helhum.io)
 *  All rights reserved
 *
 *  This script is part of the Typo3 project. The Typo3 project is
 *  free software; you can redistribute it and/or modify
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Plugin 'Single Sign-On' for the 'naw_single_signon' extension.
 * The Main Class of this extenstion to display Content in the Frontend
 *
 * @author Dietrich Heise <typo3-ext@naw.info>
 * @author Helmut Hummel (info@helhum.io)
 */
class tx_nawsinglesignon_pi1 extends tslib_pibase {

	/**
	 * @var tslib_feUserAuth
	 */
	public static $loggedOffUserAuthenticationObject;

	/**
	 * @var string
	 */
	public $prefixId = 'tx_nawsinglesignon_pi1';

	/**
	 * Path to this script relative to the extension dir.
	 *
	 * @var string
	 */
	public $scriptRelPath = 'Classes/Plugin/class.tx_nawsinglesignon_pi1.php';

	/**
	 * The extension key.
	 *
	 * @var string
	 */
	public $extKey = 'naw_single_signon'; //

	/**
	 * @var bool
	 */
	protected static $debug = FALSE;

	/**
	 * @var int
	 */
	protected static $minimumLinkLifetime;

	/**
	 * @var string
	 */
	protected $sso_version = '2.1';

	/**
	 * Extension configuration
	 *
	 * @var array
	 */
	protected $extConf;

	/**
	 * @var tx_nawsinglesignon_usermapping
	 */
	protected $userMapping;

	/**
	 * @var Tx_NawSingleSignon_Domain_Repository_SessionRepository
	 */
	protected $sessionRepository;

	/**
	 * @param Tx_NawSingleSignon_Domain_Repository_SessionRepository $sessionRepository
	 * @param tx_nawsinglesignon_usermapping $userMapping
	 */
	public function __construct(Tx_NawSingleSignon_Domain_Repository_SessionRepository $sessionRepository = NULL, tx_nawsinglesignon_usermapping $userMapping = NULL) {
		$this->sessionRepository = $sessionRepository ?: new Tx_NawSingleSignon_Domain_Repository_SessionRepository($GLOBALS['TYPO3_DB']);
		$this->userMapping = $userMapping ?: new tx_nawsinglesignon_usermapping();
	}

	/**
	 * Create a link or redirect for a third party application (tpa)
	 *
	 * @param string  $content: Here the content will given
	 * @param array  $conf: the conf array
	 * @return string  $this->pi_wrapInBaseClass($content)
	 */
	function main($content, $conf) {

		if (empty($this->getTypoScriptFrontendController()->fe_user->user['uid'])) {
			return $this->pi_wrapInBaseClass($this->pi_getLL('no_usermapping'));
		}

		$this->conf = array_replace_recursive($conf, Tx_NawSingleSignon_Configuration_FlexFormArrayConverter::convertFlexFormContentToArray($this->cObj->data['pi_flexform']));
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['naw_single_signon']);

		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		try {
			$this->checkSsl();
			$tpaLogonUrl = $this->generateTpaLogonUrl();
			$content .= $this->getPluginContent($tpaLogonUrl);
		} catch (Exception $exception) {
			$content .= htmlspecialchars($this->pi_getLL($exception->getMessage(), $exception->getMessage()));
		}

		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * Create logoff URLs
	 *
	 * @param string  $content: Here the content will given
	 * @param array  $conf: the conf array
	 * @return string  $content
	 */
	function logoff($content, $conf) {
		if (empty(self::$loggedOffUserAuthenticationObject)) {
			return '';
		}
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['naw_single_signon']);
		$activeSessions = $this->sessionRepository->findBySessionId(self::$loggedOffUserAuthenticationObject->id);

		$logoffUrls = array();
		foreach ($activeSessions as $session) {
			$metaData = unserialize($session['data']);
			$this->conf = $metaData['config'];

			$ssoData = array(
				'version' => $metaData['ssoData']['version'],
				'user' => $metaData['ssoData']['user'],
				'tpa_id' => $metaData['ssoData']['tpa_id'],
				'expires' => intval($this->conf['linklifetime']) + $GLOBALS['EXEC_TIME'],
				'action' => 'logoff',
			);

			$logoffUrls[] = $this->generateTpaUrl($ssoData);
			$this->sessionRepository->deleteBySessionHashUserIdTpaId(
				$session['session_hash'],
				$session['user_id'],
				$session['tpa_id']
			);
		}

		foreach ($logoffUrls as $url) {
			$content .= '<img src="' . htmlspecialchars($url) . '" style="display: none; "/>';
			$content .= htmlspecialchars('<img src="' . htmlspecialchars($url) . '" style="display: none; "/>');
		}

		return $content;
	}

	/**
	 * Check if force SSL has been set an throw exception if the page is not requesteg via https then
	 *
	 * @throws Exception
	 */
	protected function checkSsl() {
		if ($this->conf['forcessl'] && !t3lib_div::getIndpEnv('TYPO3_SSL')) {
			// no SSL page but required!
			throw new Exception('no_ssl', 1439646265);
		}
	}

	/**
	 * Generates the logon URL for the TPA
	 *
	 * @return string
	 * @throws Exception
	 */
	protected function generateTpaLogonUrl() {
		// Calculate link expire time
		$linkLifetime = intval($this->conf['linklifetime']);

		// Create Signing Data
		$version = $this->sso_version;
		$userName = $this->userMapping->findUsernameForUserAndMapping($this->getTypoScriptFrontendController()->fe_user, $this->conf['usermapping']);
		$tpaId = $this->conf['tpaid'];
		$validUntilTimestamp = $linkLifetime + $GLOBALS['EXEC_TIME'];
		$action = 'logon';
		$flags = base64_encode('create_modify=' . (string)intval((bool)$this->conf['flag_create']));
		$userData = $this->getUserData();

		$ssoData = array(
			'version' => $version,
			'user' => $userName,
			'tpa_id' => $tpaId,
			'expires' => $validUntilTimestamp,
			'action' => $action,
			'flags' => $flags,
			'userdata' => base64_encode($userData),
		);

		$finalUrl = $this->generateTpaUrl($ssoData);

		$this->debug($userData);
		$this->debug($ssoData);
		$this->debug($finalUrl);

		$this->calculateAndStoreMinimumLifetime($linkLifetime);
		$this->sessionRepository->addOrUpdateSession(
			new Tx_NawSingleSignon_Domain_Model_Session(
				$this->getTypoScriptFrontendController()->fe_user->id,
				$this->getTypoScriptFrontendController()->fe_user->user['uid'],
				$tpaId,
				array(
					'ssoData' => $ssoData,
					'config' => $this->conf,
				)
			)
		);

		return $finalUrl;
	}

	/**
	 * Renders the Plugin and returns the content
	 *
	 * @param $tpaLogonUrl
	 * @return string
	 * @throws Exception
	 */
	protected function getPluginContent($tpaLogonUrl) {
		$content = '';
		$contentType = $this->conf['contenttype'];
		switch ($contentType) {
			// Open in new window (requires JavaScript) (0)
			case 0:
				$this->addTpaUrlInNewWindowJavaScriptToHtmlHeader($tpaLogonUrl);
			break;
			// Open here (HTTP redirect) (works well without frames) (1)
			case 1:
				t3lib_utility_Http::redirect($tpaLogonUrl);
			break;
			// Display Link in Content (2)
			case 2:
				$content = $this->getTpaLinkTag($tpaLogonUrl);
				$this->addMetaRefreshToHtmlHeader();
			break;
			// New Window (JavaScript) AND Link in Content (3)
			// TODO: This mode make absolutely no sense. Remove it?
			case 3:
				$this->addTpaUrlInNewWindowJavaScriptToHtmlHeader($tpaLogonUrl);
				$content = $this->getTpaLinkTag($tpaLogonUrl);
				$this->addMetaRefreshToHtmlHeader();
			break;
			// Output URL as string only
			case 4:
				$content = htmlspecialchars($tpaLogonUrl);
			break;
			// Output error message
			default:
				throw new Exception('Action invalid: ' . $contentType, 1439646266);
		}

		return $content;
	}


	/**
	 * Signs a string by either using command line SSL or PHP builtin SSL
	 *
	 * @param $stringToBeSigned
	 * @return string SSL signature as byte stream
	 * @throws Exception
	 */
	protected function getSslSignatureForString($stringToBeSigned) {
		if ($this->extConf['externalOpenssl']) {
			$keyfile = $this->extConf['SSLPrivateKeyFile'];
			$external_command = 'echo -n "' . escapeshellcmd($stringToBeSigned) . '" |/usr/bin/openssl dgst -sha1 -sign' . escapeshellcmd($keyfile);
			$signature = shell_exec("$external_command");

			$this->debug('Calling OpenSSL via command line');
			$this->debug('Command executed: ' . $external_command);

			// Windows workaround
			// this is because under windows the signature / shell_exec result is cut in some cases
			// need to investigate OR at least beautify this workaround :-) /Ekki
			while (strlen($signature) != 256) {
				$this->debug('Key is not 256 bytes. Repeating...');
				$signature = shell_exec($external_command);
			}

		} else {

			$this->debug('Using compiled-in OpenSSL');

			if (function_exists('openssl_sign')) {
				// OPENSSL Sign (PHP - function)
				$filePointer = @fopen($this->extConf['SSLPrivateKeyFile'], 'r');
				if (!$filePointer) {
					throw new Exception('no_ssl_key_found', 1439646267);
				}
				$privateKeyString = fread($filePointer, 8192);
				fclose($filePointer);
				$privateKeyResource = openssl_pkey_get_private($privateKeyString, $this->extConf['SSLPassphrase']);
				// calculate the signature
				openssl_sign($stringToBeSigned, $signature, $privateKeyResource);
				// remove sign from memory
				openssl_free_key($privateKeyResource);
				// END OPENSSL Sign
			} else {
				throw new Exception('no_openssl_inPHP', 1439646268);
			}
		}

		# debug mode: save binary signature to file
		if (self::$debug) {
			$tmp_signature_file = '/tmp/sigsso_debug.signature';
			$tmp_file = @fopen($tmp_signature_file, "w");
			fwrite($tmp_file, $signature);
			fclose($tmp_file);
			print ('<br>Stored binary signature into ' . $tmp_signature_file);
			print ('<br>bin2hex of binary signature: ' . bin2hex($signature));
		}

		return $signature;
	}

	/**
	 * Determine, extract and base64 encode the user data which is going to be sent to the application
	 *
	 * @return string
	 */
	protected function getUserData() {
		$tablefields = $this->getDatabaseConnection()->admin_get_fields('fe_users');
		$userdata_tmp = '';
		$userdata_splitchar = ''; // set blank for first entry

		$tmp_enable = explode(',', $this->extConf['enable_fields']);
		$tmp2_enable = explode(',', $this->conf['enable_fields']);
		$fields_enable = array_merge($tmp_enable, $tmp2_enable);
		foreach ($tablefields as $i) {
			if ($this->getTypoScriptFrontendController()->fe_user->user[$i['Field']] AND in_array($i['Field'], $fields_enable)) {
				if ($i['Field'] == 'usergroup') {
					$groups = explode(',', $this->getTypoScriptFrontendController()->fe_user->user[$i['Field']]);
					$groupsdata_splitchar = '';
					$userdata_tmp .= $userdata_splitchar . $i['Field'] . '=';
					foreach ($groups as $j) {
						$result = $this->getDatabaseConnection()->exec_SELECTquery('*', 'fe_groups', 'uid=' . intval($j));
						while ($row = $this->getDatabaseConnection()->sql_fetch_assoc($result)) {
							$userdata_tmp .= $groupsdata_splitchar . $row['title'];
						}
						$groupsdata_splitchar = ',';
					}
				} else {
					$userdata_tmp .= $userdata_splitchar . $i['Field'] . '=' . $this->getTypoScriptFrontendController()->fe_user->user[$i['Field']];
				}
				$userdata_splitchar = '|'; //splitchar after first entry...
			}
		}

		return $userdata_tmp;
	}

	/**
	 * Checks if the parameter really is a string and does not contain control characters.
	 * If this is the case it encodes the value for URL
	 *
	 * @param $returnToUrl
	 * @return string
	 */
	protected function validateReturnToUrl($returnToUrl) {
		if (!is_string($returnToUrl)) {
			return '';
		} elseif (preg_match('#[[:cntrl:]])#', $returnToUrl)) {
				return '';
		}

		return $returnToUrl;
	}

	/**
	 * Implode the array in URL style but do not URL encode the values
	 *
	 * @param array $ssoData
	 * @return string
	 */
	protected function implodeSsoData(array $ssoData) {
		$str = '';
		foreach ($ssoData as $Akey => $AVal) {
				$str .= '&' . $Akey .
						'=' . $AVal;
			}
		return ltrim($str, '&');
	}

	/**
	 * Simple debug function.
	 * Just print out the given variable if debugging is activated
	 *
	 * @param $variable
	 */
	protected function debug($variable) {
		if (self::$debug === TRUE) {
			echo '<pre>' . htmlspecialchars(print_r($variable, TRUE)) . '</pre>';
		}
	}

	/**
	 * Generates a link tag with TPA target URL
	 *
	 * @param $tpaLogonUrl
	 * @return string
	 */
	protected function getTpaLinkTag($tpaLogonUrl) {
		if ($this->conf['frametargetcustom']) {
			$linkTarget = $this->conf['frametargetcustom'];
		} else {
			$linkTarget = $this->conf['frametarget'];
		}

		// if no link description is set use the tpa_id
		if ($this->conf['linkdescription']) {
			$linkText = $this->conf['linkdescription'];
		} else {
			$linkText = $this->conf['tpaid'];
		}

		$content = $this->conf['html_before'];
		$additionalAttributes = array();
		if ($linkTarget === '_blank') {
			$additionalAttributes[] = 'onMouseDown="location.reload()"';
		}
		$content .= '<a ' . implode(' ', $additionalAttributes) . ' href="' . htmlspecialchars($tpaLogonUrl) . '" target="' . htmlspecialchars($linkTarget) . '">' . htmlspecialchars($linkText) . '</a>';
		$content .= $this->conf['html_after'];
		return $content;
	}

	/**
	 * Add JavaScript to HTML header to open a new browser window or tab with TPA URL
	 *
	 * @param string $tpaLogonUrl
	 */
	protected function addTpaUrlInNewWindowJavaScriptToHtmlHeader($tpaLogonUrl) {
		$this->getTypoScriptFrontendController()->additionalHeaderData['Window_onload_' . $this->conf['tpaid']] = t3lib_div::wrapJS('window.open(' . t3lib_div::quoteJSvalue($tpaLogonUrl) . ');');
	}

	/**
	 * @return t3lib_DB
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

	/**
	 * @return tslib_fe
	 */
	protected function getTypoScriptFrontendController() {
		return $GLOBALS['TSFE'];
	}

	/**
	 * @return void
	 */
	protected function addMetaRefreshToHtmlHeader() {
		if (!empty($this->extConf['refreshLinkPage'])) {
			$this->getTypoScriptFrontendController()->additionalHeaderData['tx_sso_meta_refresh'] = '<meta http-equiv="refresh" content="' . t3lib_div::intInRange(self::$minimumLinkLifetime - 5, 5) . '; URL="' . htmlspecialchars($this->cObj->getUrlToCurrentLocation()) . '">';
		}
	}

	/**
	 * @param $linkLifetime
	 */
	protected function calculateAndStoreMinimumLifetime($linkLifetime) {
		if (!self::$minimumLinkLifetime) {
			self::$minimumLinkLifetime = $linkLifetime;
		} else {
			self::$minimumLinkLifetime = min(self::$minimumLinkLifetime, $linkLifetime);
		}
	}

	/**
	 * @param $ssoData
	 * @return string
	 * @throws Exception
	 */
	protected function generateTpaUrl($ssoData) {
		# encode the signature in hex format
		$ssoData['signature'] = bin2hex($this->getSslSignatureForString($this->implodeSsoData($ssoData)));
		$ssoData['returnTo'] = $this->validateReturnToUrl(t3lib_div::_GET('returnTo'));

		# Compose the final URL
		$finalUrl = $this->conf['targeturl'] . '?' . t3lib_div::implodeArrayForUrl('', $ssoData, '', FALSE, TRUE);
		return $finalUrl;
	}

}
