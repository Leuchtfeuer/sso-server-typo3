<?php
$GLOBALS['LANG']->includeLLFile('EXT:single_signon/mod1/locallang.xml');
// This checks permissions and exits if the users has no permission for entry.
$GLOBALS['BE_USER']->modAccess($MCONF, 1);

/** @var \Bitmotion\SingleSignon\Module\ModuleController $SOBE */
$SOBE = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Bitmotion\\SingleSignon\\Module\\ModuleController');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();
