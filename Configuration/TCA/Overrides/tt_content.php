<?php

namespace Bitmotion\SingleSignon\Configuration\TCA\Overrides;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$extensionKey = 'single_signon';
ExtensionManagementUtility::addPlugin(
    ['LLL:EXT:single_signon/Resources/Private/Language/locallang_tca.php:single_signon', $extensionKey . '_pi1'],
    'list_type',
    $extensionKey
);
ExtensionManagementUtility::addStaticFile(
    $extensionKey,
    'Configuration/TypoScript/',
    'Bitmotion SSO'
);
ExtensionManagementUtility::addPiFlexFormValue(
    $extensionKey . '_pi1',
    'FILE:EXT:single_signon/Configuration/Flexform/flexform_ds.xml'
);
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$extensionKey . '_pi1'] = 'layout,select_key,pages';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$extensionKey . '_pi1'] = 'pi_flexform';
unset($extensionKey);
