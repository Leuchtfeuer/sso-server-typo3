<?php

namespace Bitmotion\SingleSignon\Plugin;

use Bitmotion\SingleSignon\Domain\Model\Session;
use Bitmotion\SingleSignon\Domain\Repository\SessionRepository;
use Bitmotion\SingleSignon\UserData\UserDataSourceInterface;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2003-2012 Dietrich Heise (typo3-ext@bitmotion.de)
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

use Bitmotion\SingleSignon\UserMapping;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Plugin\AbstractPlugin;

/**
 * Plugin Controller of the SSO Server.
 * It generates frontend output (SSO App logon url) or directly redirects to one SSO App
 *
 * @author Dietrich Heise <typo3-ext@bitmotion.de>
 * @author Helmut Hummel (info@helhum.io)
 */
class PluginController extends AbstractPlugin
{
    /**
     * @var FrontendUserAuthentication
     */
    public static $loggedOffUserAuthenticationObject;

    /**
     * @var string
     */
    public $prefixId = 'tx_singlesignon_pi1';

    /**
     * The extension key.
     *
     * @var string
     */
    public $extKey = 'single_signon';

    /**
     * @var bool
     */
    protected static $debug = false;

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
     * @var UserMapping
     */
    protected $userMapping;

    /**
     * @var SessionRepository
     */
    protected $sessionRepository;

    /**
     * @var \TYPO3\CMS\Core\Service\FlexFormService
     */
    protected $flexFormService;

    /**
     * @param SessionRepository $sessionRepository
     * @param UserMapping $userMapping
     * @param \TYPO3\CMS\Core\Service\FlexFormService $flexFormService
     */
    public function __construct(SessionRepository $sessionRepository = null, UserMapping $userMapping = null, FlexFormService $flexFormService = null)
    {
        parent::__construct();
        $this->sessionRepository = $sessionRepository ?: new SessionRepository($GLOBALS['TYPO3_DB']);
        $this->userMapping = $userMapping ?: new UserMapping();
        $this->flexFormService = $flexFormService ?: GeneralUtility::makeInstance(FlexFormService::class);
    }

    /**
     * Create a link or redirect for an SSO App
     *
     * @param string  $content: Here the content will given
     * @param array  $conf: the conf array
     * @return string  $this->pi_wrapInBaseClass($content)
     */
    public function main($content, $conf)
    {
        if (empty($this->getTypoScriptFrontendController()->fe_user->user['uid'])) {
            return $this->pi_wrapInBaseClass($this->pi_getLL('no_usermapping'));
        }

        $this->conf = array_replace_recursive($conf, $this->flexFormService->convertFlexFormContentToArray($this->cObj->data['pi_flexform']));
        $this->extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('single_signon');

        $this->pi_setPiVarDefaults();
        $this->pi_loadLL('EXT:single_signon/Resources/Private/Language/Plugin/locallang.xml');

        try {
            $this->checkSsl();
            $appLogonUrl = $this->generateSsoAppLogonUrl();
            $content .= $this->getPluginContent($appLogonUrl);
        } catch (\Exception $exception) {
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
    public function logoff($content, $conf)
    {
        if (empty(self::$loggedOffUserAuthenticationObject)) {
            return '';
        }
        $this->extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('single_signon');
        $activeSessions = $this->sessionRepository->findBySessionId(self::$loggedOffUserAuthenticationObject->id);

        $logoffUrls = [];
        foreach ($activeSessions as $session) {
            $metaData = unserialize($session['data']);
            $this->conf = $metaData['config'];

            $ssoData = [
                'version' => $metaData['ssoData']['version'],
                'user' => $metaData['ssoData']['user'],
                'app_id' => $metaData['ssoData']['app_id'],
                'expires' => (int)($this->conf['linklifetime']) + $GLOBALS['EXEC_TIME'],
                'action' => 'logoff',
            ];

            $logoffUrls[] = $this->generateSsoAppUrl($ssoData);
            $this->sessionRepository->deleteBySessionHashUserIdAppId(
                $session['session_hash'],
                $session['user_id'],
                $session['app_id']
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
     * @throws \Exception
     */
    protected function checkSsl()
    {
        if ($this->conf['forcessl'] && !GeneralUtility::getIndpEnv('TYPO3_SSL')) {
            // no SSL page but required!
            throw new \Exception('no_ssl', 1439646265);
        }
    }

    /**
     * Generates the logon URL for the SSO App
     *
     * @return string
     * @throws \Exception
     */
    protected function generateSsoAppLogonUrl()
    {
        // Calculate link expire time
        $linkLifetime = (int)($this->conf['linklifetime']);

        // Create Signing Data
        $version = $this->sso_version;
        $userName = $this->userMapping->findUsernameForUserAndMapping($this->getTypoScriptFrontendController()->fe_user, $this->conf['usermapping']);
        $appId = $this->conf['appId'];
        $validUntilTimestamp = $linkLifetime + $GLOBALS['EXEC_TIME'];
        $action = 'logon';
        $flags = base64_encode('create_modify=' . (string)(int)((bool)$this->conf['flag_create']));
        $userData = $this->getUserData();

        $ssoData = [
            'version' => $version,
            'user' => $userName,
            'app_id' => $appId,
            'expires' => $validUntilTimestamp,
            'action' => $action,
            'flags' => $flags,
            'userdata' => $this->encodeUserData($userData),
        ];

        $finalUrl = $this->generateSsoAppUrl($ssoData);

        $this->debug($userData);
        $this->debug($ssoData);
        $this->debug($finalUrl);

        $this->calculateAndStoreMinimumLifetime($linkLifetime);
        $this->sessionRepository->addOrUpdateSession(
            new Session(
                $this->getTypoScriptFrontendController()->fe_user->id,
                $this->getTypoScriptFrontendController()->fe_user->user['uid'],
                $appId,
                [
                    'ssoData' => $ssoData,
                    'config' => $this->conf,
                ]
            )
        );

        return $finalUrl;
    }

    /**
     * Renders the Plugin and returns the content
     *
     * @param $appLogonUrl
     * @return string
     * @throws \Exception
     */
    protected function getPluginContent($appLogonUrl)
    {
        $content = '';
        $contentType = $this->conf['contenttype'];
        switch ($contentType) {
            // Open in new window (requires JavaScript) (0)
            case 0:
                $this->addSsoAppUrlInNewWindowJavaScriptToHtmlHeader($appLogonUrl);
            break;
            // Open here (HTTP redirect) (works well without frames) (1)
            case 1:
                HttpUtility::redirect($appLogonUrl);
            break;
            // Display Link in Content (2)
            case 2:
                $content = $this->getSsoAppLinkTag($appLogonUrl);
                $this->addMetaRefreshToHtmlHeader();
            break;
            // New Window (JavaScript) AND Link in Content (3)
            // TODO: This mode make absolutely no sense. Remove it?
            case 3:
                $this->addSsoAppUrlInNewWindowJavaScriptToHtmlHeader($appLogonUrl);
                $content = $this->getSsoAppLinkTag($appLogonUrl);
                $this->addMetaRefreshToHtmlHeader();
            break;
            // Output URL as string only
            case 4:
                $content = htmlspecialchars($appLogonUrl);
            break;
            // Output error message
            default:
                throw new \Exception('Action invalid: ' . $contentType, 1439646266);
        }

        return $content;
    }

    /**
     * Signs a string by either using command line SSL or PHP builtin SSL
     *
     * @param $stringToBeSigned
     * @return string SSL signature as byte stream
     * @throws \Exception
     */
    protected function getSslSignatureForString($stringToBeSigned)
    {
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
                    throw new \Exception('no_ssl_key_found', 1439646267);
                }
                $privateKeyString = fread($filePointer, 8192);
                fclose($filePointer);
                $privateKeyResource = openssl_pkey_get_private($privateKeyString, $this->extConf['SSLPassphrase']);
                if (!$privateKeyResource) {
                    throw new \Exception('private_ssl_key_error', 1439646268);
                }
                // calculate the signature
                openssl_sign($stringToBeSigned, $signature, $privateKeyResource);
                // remove sign from memory
                openssl_free_key($privateKeyResource);
            // END OPENSSL Sign
            } else {
                throw new \Exception('no_openssl_inPHP', 1439646268);
            }
        }

        // debug mode: save binary signature to file
        if (self::$debug) {
            $tmp_signature_file = '/tmp/directsso_debug.signature';
            $tmp_file = @fopen($tmp_signature_file, 'w');
            fwrite($tmp_file, $signature);
            fclose($tmp_file);
            print '<br>Stored binary signature into ' . $tmp_signature_file;
            print '<br>bin2hex of binary signature: ' . bin2hex($signature);
        }

        return $signature;
    }

    /**
     * Determine, extract and base64 encode the user data which is going to be sent to the application
     *
     * @return array
     */
    protected function getUserData()
    {
        if (empty($this->conf['userDataSources.'])) {
            throw new \UnexpectedValueException('No user data source was found. Please check if the TypoScript is added and a data provider is configured.', 1441733003);
        }

        $requestedUserDataFields = array_merge(
            explode(',', $this->extConf['enable_fields']),
            explode(',', $this->conf['enable_fields'])
        );

        $userData = [];
        $dataSources = $this->conf['userDataSources.'];
        $dataSourcesKeys = ArrayUtility::filterAndSortByNumericKeys($dataSources);

        foreach ($dataSourcesKeys as $key) {
            $className = $dataSources[$key];
            if (!class_exists($className)) {
                throw new \UnexpectedValueException('Data source class name "' . $className . '" does not exist!', 1441731922);
            }
            $dataSource = GeneralUtility::makeInstance($className);
            if (!$dataSource instanceof UserDataSourceInterface) {
                throw new \UnexpectedValueException(
                    'Data source with class name "' . $className . '" ' .
                    'must implement interface "Bitmotion\SingleSignon\UserData\UserDataSourceInterface"',
                    1441731967
                );
            }

            $dataSourceConfiguration = isset($dataSources[$key . '.']) ? $dataSources[$key . '.'] : [];
            $dataSourceConfiguration['userDataFields'] = empty($dataSourceConfiguration['userDataFields']) ? $requestedUserDataFields : $dataSourceConfiguration['userDataFields'];

            $userData = $dataSource->fetchUserData(
                $userData,
                $dataSourceConfiguration
            );
        }

        return $userData;
    }

    /**
     * Currently encodes in the form: "name=John|email=info@example.com"
     * which is base64 encoded in the end.
     *
     * @param array $userData associative array
     * @return string
     */
    protected function encodeUserData(array $userData)
    {
        return base64_encode(
            implode(
                '|',
                array_map(function ($v, $k) { return $k . '=' . $v; }, $userData, array_keys($userData))
            )
        );
    }

    /**
     * Checks if the parameter really is a string and does not contain control characters.
     * If this is the case it encodes the value for URL
     *
     * @param $returnToUrl
     * @return string
     */
    protected function validateReturnToUrl($returnToUrl)
    {
        if (!is_string($returnToUrl)) {
            return '';
        }
        if (preg_match('#[[:cntrl:]])#', $returnToUrl)) {
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
    protected function implodeSsoData(array $ssoData)
    {
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
    protected function debug($variable)
    {
        if (self::$debug === true) {
            echo '<pre>' . htmlspecialchars(print_r($variable, true)) . '</pre>';
        }
    }

    /**
     * Generates a link tag with SSO App target URL
     *
     * @param $appLogonUrl
     * @return string
     */
    protected function getSsoAppLinkTag($appLogonUrl)
    {
        if ($this->conf['frametargetcustom']) {
            $linkTarget = $this->conf['frametargetcustom'];
        } else {
            $linkTarget = $this->conf['frametarget'];
        }

        // if no link description is set use the app_id
        if ($this->conf['linkdescription']) {
            $linkText = $this->conf['linkdescription'];
        } else {
            $linkText = $this->conf['appId'];
        }

        $content = $this->conf['html_before'];
        $additionalAttributes = [];
        if ($linkTarget === '_blank') {
            $additionalAttributes[] = 'onmousedown="setTimeout(function () {location.reload()},300);"';
        }
        $content .= '<a class="' . $this->conf['add_classes'] . '" ' . implode(' ', $additionalAttributes) . ' href="' . htmlspecialchars($appLogonUrl) . '" target="' . htmlspecialchars($linkTarget) . '">' . htmlspecialchars($linkText) . '</a>';
        $content .= $this->conf['html_after'];
        return $content;
    }

    /**
     * Add JavaScript to HTML header to open a new browser window or tab with SSO App URL
     *
     * @param string $appLogonUrl
     */
    protected function addSsoAppUrlInNewWindowJavaScriptToHtmlHeader($appLogonUrl)
    {
        $this->getTypoScriptFrontendController()->additionalHeaderData['Window_onload_' . $this->conf['appId']] = GeneralUtility::wrapJS('window.open(' . GeneralUtility::quoteJSvalue($appLogonUrl) . ');');
    }

    /**
     * @return DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }

    protected function addMetaRefreshToHtmlHeader()
    {
        if (!empty($this->extConf['refreshLinkPage'])) {
            $this->getTypoScriptFrontendController()->additionalHeaderData['tx_sso_meta_refresh'] = '<meta http-equiv="refresh" content="' . MathUtility::isIntegerInRange(self::$minimumLinkLifetime - 5, 5, 2000000000) . '; URL="' . htmlspecialchars($this->cObj->getUrlToCurrentLocation()) . '">';
        }
    }

    /**
     * @param $linkLifetime
     */
    protected function calculateAndStoreMinimumLifetime($linkLifetime)
    {
        if (!self::$minimumLinkLifetime) {
            self::$minimumLinkLifetime = $linkLifetime;
        } else {
            self::$minimumLinkLifetime = min(self::$minimumLinkLifetime, $linkLifetime);
        }
    }

    /**
     * @param $ssoData
     * @return string
     * @throws \Exception
     */
    protected function generateSsoAppUrl($ssoData)
    {
        // encode the signature in hex format
        $ssoData['signature'] = bin2hex($this->getSslSignatureForString($this->implodeSsoData($ssoData)));
        $ssoData['returnTo'] = $this->validateReturnToUrl(GeneralUtility::_GET('returnTo'));

        // Compose the final URL
        $finalUrl = $this->conf['targeturl'] . '?' . GeneralUtility::implodeArrayForUrl('', $ssoData, '', false, true);
        return $finalUrl;
    }
}
