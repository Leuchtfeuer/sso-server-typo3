<?php
/** @var \Bitmotion\SingleSignon\Module\ModuleController $SOBE */
$SOBE = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Bitmotion\\SingleSignon\\Module\\ModuleController');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();
