<?php
defined('TYPO3_MODE') or die('Access denied.');

// Register plugin
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
    $_EXTKEY,
    'setup',
    trim('
		plugin.tx_singlesignon_pi1 = USER_INT
		plugin.tx_singlesignon_pi1.userFunc = Bitmotion\\SingleSignon\\Plugin\\PluginController->main
	')
);

// Add default rendering for plugin
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
    $_EXTKEY,
    'setup',
    'tt_content.list.20.single_signon_pi1 =< plugin.tx_singlesignon_pi1',
    'defaultContentRendering'
);
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_pre_processing'][] = 'Bitmotion\\SingleSignon\\Hook\\LogoffListener->registerLogoff';
