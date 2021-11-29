<?php
defined('TYPO3_MODE') or die('Access denied.');

if (TYPO3_MODE == 'BE') {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('tools', 'txsinglesignonM1', '', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('single_signon') . 'Modules/UserMapping/');
    $GLOBALS['TBE_MODULES_EXT']['xMOD_db_new_content_el']['addElClasses']['Bitmotion\\SingleSignon\\Plugin\\Wizicon'] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('single_signon') . 'Classes/Plugin/Wizicon.php';
}
