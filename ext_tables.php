<?php
defined ('TYPO3_MODE') or die('Access denied.');

if (TYPO3_MODE == 'BE') {
	t3lib_extMgm::addModule('tools', 'txsinglesignonM1', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/');
	$GLOBALS['TBE_MODULES_EXT']['xMOD_db_new_content_el']['addElClasses']['tx_singlesignon_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY) . 'Classes/Plugin/class.tx_singlesignon_pi1_wizicon.php';
}

if (is_callable('t3lib_div::loadTCA')) {
	t3lib_div::loadTCA('tt_content');
}

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY . '_pi1'] = 'layout,select_key,pages';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY . '_pi1'] = 'pi_flexform';

t3lib_extMgm::addPiFlexFormValue($_EXTKEY . '_pi1', 'FILE:EXT:single_signon/Configuration/Flexform/flexform_ds.xml');
t3lib_extMgm::addPlugin(array('LLL:EXT:single_signon/Resources/Private/Language/locallang_tca.php:single_signon', 'single_signon_pi1'));

t3lib_extMgm::addStaticFile($_EXTKEY, 'pi1/static/', 'Bitmotion SSO');

