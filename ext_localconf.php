<?php
defined('TYPO3_MODE') or die('Access denied.');

call_user_func(function()
{
    $extensionKey = 'single_signon';

    // Register plugin
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
        $extensionKey,
        'setup',
        trim('
            plugin.tx_singlesignon_pi1 = USER_INT
            plugin.tx_singlesignon_pi1.userFunc = Bitmotion\\SingleSignon\\Plugin\\PluginController->main
        ')
    );

    // Add default rendering for plugin
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
        'single_signon',
        'setup',
        'tt_content.list.20.single_signon_pi1 =< plugin.tx_singlesignon_pi1',
        'defaultContentRendering'
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
        'single_signon',
        'setup',
        "@import 'EXT:single_signon/Configuration/TypoScript/setup.typoscript'"
    );
});

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_pre_processing'][] = 'Bitmotion\\SingleSignon\\Hook\\LogoffListener->registerLogoff';
