<?php
	if (!defined ('TYPO3_MODE')) die ('Access denied.');

	if (TYPO3_MODE == 'BE') {
		t3lib_extMgm::addModule('tools', 'txnawsinglesignonM1', '', t3lib_extMgm::extPath($_EXTKEY).'mod1/');
	}

	t3lib_div::loadTCA('tt_content');

	$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1'] = 'layout,select_key,pages';
	$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1'] = 'pi_flexform';

	t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:naw_single_signon/flexform_ds.xml');
	t3lib_extMgm::addPlugin(Array('LLL:EXT:naw_single_signon/locallang_tca.php:naw_single_signon', 'naw_single_signon_pi1'));

	include_once(t3lib_extMgm::extPath('naw_single_signon') . 'class.tx_nawsinglesignon_usermapping.php');
	if (TYPO3_MODE == 'BE') $TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_nawsinglesignon_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_nawsinglesignon_pi1_wizicon.php';
?>
