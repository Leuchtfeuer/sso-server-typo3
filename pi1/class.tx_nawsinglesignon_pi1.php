<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2003-2012 Dietrich Heise (typo3-ext@naw.info)
 *  (c) 2012 Helmut Hummel (helmut.hummel@typo3.org)
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

require_once(PATH_tslib . 'class.tslib_pibase.php');

/**
 * Plugin 'Single Sign-On' for the 'naw_single_signon' extension.
 * The Main Class of this extenstion to display Content in the Frontend
 *
 * @author Dietrich Heise <typo3-ext@naw.info>
 * @author Helmut Hummel (helmut.hummel@typo3.org)
 */
class tx_nawsinglesignon_pi1 extends tslib_pibase {
	var $prefixId = 'tx_nawsinglesignon_pi1';
	// Same as class name
	var $scriptRelPath = 'pi1/class.tx_nawsinglesignon_pi1.php'; // Path to this script relative to the extension dir.
	var $extKey = 'naw_single_signon'; // The extension key.

	/**
	 * @var bool
	 */
	protected static $debug = FALSE;

	/**
	 * @var string
	 */
	protected $sso_version = '2.0';

	/**
	 * Extension configuration
	 *
	 * @var array
	 */
	protected $extConf;

	/**
	 * create a link or redirect for a third party application (tpa)
	 *
	 * @param string  $content: Here the content will given
	 * @param array  $conf: the conf array
	 * @return string  $this->pi_wrapInBaseClass($content)
	 */
	function main($content, $conf) {

		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['naw_single_signon']);
		$this->pi_initPIflexForm();

		try {
			$this->checkSsl();
			$tpaLogonUrl = $this->generateTpaLogonUrl();
			$content .= $this->getPluginContent($tpaLogonUrl);
		} catch (Exception $exception) {
			$content .= $exception->getMessage();
		}

		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * Check if force SSL has been set an throw exception if the page is not requesteg via https then
	 *
	 * @throws Exception
	 */
	protected function checkSsl() {
		if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'forcessl', 'sDEF3') && !t3lib_div::getIndpEnv('TYPO3_SSL')) {
			// no SSL page but required!
			throw new Exception($this->pi_getLL('no_ssl'));
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
		$validUntilTimestamp = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'linklifetime', 'sDEF3') + time();

		$userId = ($this->getMappedUser($GLOBALS['TSFE']->fe_user->user['uid']));
		if (!$userId) {
			throw new Exception($this->pi_getLL('no_usermapping'));
		}

		if (!$this->getMappedUser($GLOBALS['TSFE']->fe_user->user['uid'])) {
			throw new Exception($this->pi_getLL('no_user'));
		}

		// Create Signing Data
		if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'flag_create', 'sDEF3')) {
			$create_modify = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'flag_create', 'sDEF3');
		} else {
			$create_modify = '0';
		}

		$flags = base64_encode('create_modify=' . $create_modify);

		$userData = $this->getUserData();
		$this->debug($userData);

		$ssoData = array(
			'version' => $this->sso_version,
			'user' => $userId,
			'tpa_id' => $this->pi_getFFvalue($this->cObj->data['pi_flexform'],
			'tpaid', 'sDEF'),
			'expires' => $validUntilTimestamp,
			'action' => 'logon',
			'flags' => $flags,
			'userdata' => base64_encode($userData),
		);

		$this->debug($ssoData);
//		$this->debug($this->implodeSsoData($ssoData));

		# encode the signature in hex format
		$ssoData['signature'] = bin2hex($this->getSslSignatureForString($this->implodeSsoData($ssoData)));
		$ssoData['returnTo'] = $this->validateReturnToUrl(t3lib_div::_GET('returnTo'));

		# Compose the final URL
		$finalUrl =  $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'targeturl', 'sDEF') . '?' . t3lib_div::implodeArrayForUrl('', $ssoData, '', FALSE, TRUE);
		$this->debug($finalUrl);

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
		$contentType = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'contenttype', 'sDEF2');
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
			break;
			// New Window (JavaScript) AND Link in Content (3)
			case 3:
				$this->addTpaUrlInNewWindowJavaScriptToHtmlHeader($tpaLogonUrl);
				$content = $this->getTpaLinkTag($tpaLogonUrl);
			break;
			// Output URL as string only
			case 4:
				$content = htmlspecialchars($tpaLogonUrl);
			break;
			// Output error message
			default:
				throw new Exception('Action invalid: ' . htmlspecialchars($contentType));
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
					throw new Exception($this->pi_getLL('no_ssl_key_found'));
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
				throw new Exception($this->pi_getLL('no_openssl_inPHP'));
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
	 * return the mapped username for $uid
	 *
	 * @param integer  $uid: the uid of the current fe_user
	 * @return string  mapped username
	 */
	protected function getMappedUser($uid) {
		if (!$uid) {
			return '';
		}
		$mapping_id = intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'usermapping', 'sDEF3'));

		// Default Table (mapping as it is)
		if ($mapping_id == 0) {
			return $GLOBALS['TSFE']->fe_user->user['username'];
		}

		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_nawsinglesignon_properties', 'deleted=0 AND uid=' . intval($mapping_id));
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);

		// If allowall map undef-users to fe_usernames, else deny
		$allowall = $row['allowall'];
		$sysfolder_id = $row['sysfolder_id'];
		$mapping_defaultmapping = $row['mapping_defaultmapping'];

		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_nawsinglesignon_usermap', 'mapping_id=' . intval($mapping_id) . ' AND fe_uid=' . intval($uid));
		$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);

		if ($GLOBALS['TSFE']->fe_user->user['pid'] != $sysfolder_id) {
			return '';
		}

		if (($row['mapping_username'] == '') && ($allowall == 1) && $mapping_defaultmapping) {
			return $mapping_defaultmapping;
		} elseif (($row['mapping_username'] == '') && ($allowall == 1)) {
			return $GLOBALS['TSFE']->fe_user->user['username'];
		}

		return $row['mapping_username'];
	}

	/**
	 * Determine, extract and base64 encode the user data which is going to be sent to the application
	 *
	 * @return string
	 */
	protected function getUserData() {
		$tablefields = $GLOBALS['TYPO3_DB']->admin_get_fields('fe_users');
		$userdata_tmp = '';
		$userdata_splitchar = ''; // set blank for first entry

		$tmp_enable = explode(',', $this->extConf['enable_fields']);
		$tmp2_enable = explode(',', $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'enable_fields', 'sDEF3'));
		$fields_enable = array_merge($tmp_enable, $tmp2_enable);
		foreach ($tablefields as $i) {
			if ($GLOBALS['TSFE']->fe_user->user[$i['Field']] AND in_array($i['Field'], $fields_enable)) {
				if ($i['Field'] == 'usergroup') {
					$groups = explode(',', $GLOBALS['TSFE']->fe_user->user[$i['Field']]);
					$groupsdata_splitchar = '';
					$userdata_tmp .= $userdata_splitchar . $i['Field'] . '=';
					foreach ($groups as $j) {
						$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_groups', 'uid=' . intval($j));
						while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)) {
							$userdata_tmp .= $groupsdata_splitchar . $row['title'];
						}
						$groupsdata_splitchar = ',';
					}
				} else {
					$userdata_tmp .= $userdata_splitchar . $i['Field'] . '=' . $GLOBALS['TSFE']->fe_user->user[$i['Field']];
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
		if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'frametargetcustom', 'sDEF2')) {
			$linkTarget = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'frametargetcustom', 'sDEF2');
		} else {
			$linkTarget = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'frametarget', 'sDEF2');
		}

		// if no link description is set use the tpa_id
		if ($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'linkdescription', 'sDEF')) {
			$linkText = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'linkdescription', 'sDEF');
		} else {
			$linkText = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'tpaid', 'sDEF');
		}

		$content = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'html_before', 'sDEF2');
		$additionalAttributes = array();
		if ($linkTarget === '_blank') {
			$additionalAttributes[] = 'onMouseDown="location.reload()"';
		}
		$content .= '<a ' . implode(' ', $additionalAttributes) . ' href="' . htmlspecialchars($tpaLogonUrl) . '" target="' . htmlspecialchars($linkTarget) . '">' . htmlspecialchars($linkText) . '</a>';
		$content .= $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'html_after', 'sDEF2');
		return $content;
	}

	/**
	 * Add JavaScript to HTML header to open a new browser window or tab with TPA URL
	 *
	 * @param string $tpaLogonUrl
	 */
	protected function addTpaUrlInNewWindowJavaScriptToHtmlHeader($tpaLogonUrl) {
		$GLOBALS['TSFE']->additionalHeaderData['Window_onload_' . $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'tpaid', 'sDEF')] = t3lib_div::wrapJS('window.open(' . t3lib_div::quoteJSvalue($tpaLogonUrl) . ');');
	}
}

if (defined('TYPO3_MODE') && isset($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/naw_single_signon/pi1/class.tx_nawsinglesignon_pi1.php'])) {
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/naw_single_signon/pi1/class.tx_nawsinglesignon_pi1.php']);
}
