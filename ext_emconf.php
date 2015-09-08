<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "single_signon".
 *
 * Auto generated 26-06-2013 13:29
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Single Sign-On',
	'description' => 'The TYPO3 Single Sign-On extension (SSO Server) provides seamless integration of third-party (i.e. non-TYPO3) applications (SSO Apps) into TYPO3. This includes end-user access to SSO Apps with no additional logon.',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '2.1.1',
	'dependencies' => '',
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
	'author' => 'Dietrich Heise',
	'author_email' => 'typo3-ext@bitmotion.de',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'php' => '5.2.0-5.6.99',
			'typo3' => '4.5.39-4.7.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);

