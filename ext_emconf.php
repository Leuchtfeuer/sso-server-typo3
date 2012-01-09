<?php

########################################################################
# Extension Manager/Repository config file for ext: "naw_single_signon"
#
# Auto generated 16-02-2007 10:54
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Single Sign-On',
	'description' => 'The Typo3 Single Sign-On (SSO) extension provides seamless integration of third-party (i.e. non-Typo3) applications (TPAs) into Typo3. This includes end-user access to TPAs with no additional logon (for authenticated Typo3-users, via "SSO Link" provided by Typo3), role-based integration of the SSO-link into Typo3 navigation or content and a sophisticated security architecture',
	'category' => 'plugin',
	'shy' => 0,
	'dependencies' => 'cms',
	'conflicts' => '',
	'priority' => '',
	'module' => 'mod1',
	'state' => 'beta',
	'internal' => 0,
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author' => 'Dietrich Heise',
	'author_email' => 'typo3-ext@naw.info',
	'author_company' => '',
	'version' => '2.0.1',
	'_md5_values_when_last_written' => 'a:30:{s:40:"class.tx_nawsinglesignon_usermapping.php";s:4:"23d6";s:21:"ext_conf_template.txt";s:4:"9d48";s:12:"ext_icon.gif";s:4:"f924";s:17:"ext_localconf.php";s:4:"40e1";s:14:"ext_tables.php";s:4:"5d51";s:14:"ext_tables.sql";s:4:"a1e2";s:15:"flexform_ds.xml";s:4:"101a";s:13:"locallang.xml";s:4:"9800";s:33:"locallang_csh_nawsinglesignon.xml";s:4:"a693";s:27:"locallang_csh_ttcontent.xml";s:4:"a693";s:16:"locallang_db.xml";s:4:"7458";s:17:"locallang_tca.xml";s:4:"e97b";s:14:"pi1/ce_wiz.gif";s:4:"7060";s:36:"pi1/class.tx_nawsinglesignon_pi1.php";s:4:"9712";s:44:"pi1/class.tx_nawsinglesignon_pi1_wizicon.php";s:4:"3730";s:13:"pi1/clear.gif";s:4:"cc11";s:17:"pi1/locallang.xml";s:4:"f6ae";s:14:"mod1/clear.gif";s:4:"cc11";s:13:"mod1/conf.php";s:4:"b03b";s:14:"mod1/index.php";s:4:"a29f";s:18:"mod1/locallang.xml";s:4:"2330";s:22:"mod1/locallang_mod.xml";s:4:"033b";s:25:"mod1/mapping_help1_en.png";s:4:"e4e1";s:25:"mod1/mapping_help2_en.png";s:4:"f8e6";s:25:"mod1/mapping_help3_en.png";s:4:"0978";s:24:"mod1/mapping_help_en.gif";s:4:"c3f7";s:22:"mod1/single-signon.css";s:4:"8a9f";s:19:"mod1/top_header.gif";s:4:"bde7";s:20:"mod1/usermapping.gif";s:4:"3161";s:27:"doc/manual-doc_sso_doku.sxw";s:4:"72c2";}',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
);

?>