<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "single_signon".
 *
 * Auto generated 08-09-2015 17:44
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Single Sign-On',
	'description' => 'The TYPO3 SSO Server provides seamless integration of third-party (i.e. non-TYPO3) applications (SSO Apps) into TYPO3. This includes end-user access to SSO Apps with no additional logon.',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '3.0.0',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'mod1',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 1,
	'lockType' => '',
	'author' => 'Bitmotion GmbH',
	'author_email' => 'typo3-ext@bitmotion.de',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '5.2.0-5.6.99',
			'typo3' => '6.2.0-7.99.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:37:{s:16:"ext_autoload.php";s:4:"d2c0";s:21:"ext_conf_template.txt";s:4:"e747";s:12:"ext_icon.gif";s:4:"f924";s:17:"ext_localconf.php";s:4:"a5df";s:14:"ext_tables.php";s:4:"5dc6";s:14:"ext_tables.sql";s:4:"21ca";s:23:"Classes/UserMapping.php";s:4:"606a";s:41:"Classes/Configuration/FlexFormService.php";s:4:"d4b2";s:32:"Classes/Domain/Model/Session.php";s:4:"39e3";s:47:"Classes/Domain/Repository/SessionRepository.php";s:4:"e3be";s:31:"Classes/Hook/LogoffListener.php";s:4:"0a18";s:35:"Classes/Module/ModuleController.php";s:4:"6462";s:35:"Classes/Plugin/PluginController.php";s:4:"b23a";s:26:"Classes/Plugin/Wizicon.php";s:4:"9dc0";s:38:"Configuration/Flexform/flexform_ds.xml";s:4:"d926";s:44:"Resources/Private/Language/locallang_tca.xml";s:4:"7bac";s:27:"doc/manual-doc_sso_doku.sxw";s:4:"72c2";s:28:"doc/manual-doc_sso_doku_.sxw";s:4:"72c2";s:14:"doc/manual.sxw";s:4:"b390";s:12:"doc/todo.sxw";s:4:"7aef";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"33a8";s:14:"mod1/index.php";s:4:"ce63";s:18:"mod1/locallang.xml";s:4:"892a";s:22:"mod1/locallang_mod.xml";s:4:"033b";s:25:"mod1/mapping_help1_en.png";s:4:"e4e1";s:25:"mod1/mapping_help2_en.png";s:4:"f8e6";s:25:"mod1/mapping_help3_en.png";s:4:"0978";s:24:"mod1/mapping_help_en.gif";s:4:"c3f7";s:22:"mod1/single-signon.css";s:4:"8a9f";s:19:"mod1/top_header.gif";s:4:"bde7";s:20:"mod1/usermapping.gif";s:4:"3161";s:14:"pi1/ce_wiz.gif";s:4:"7060";s:13:"pi1/clear.gif";s:4:"cc11";s:17:"pi1/locallang.xml";s:4:"f6ae";s:24:"pi1/static/constants.txt";s:4:"d41d";s:20:"pi1/static/setup.txt";s:4:"bd23";}',
);

?>