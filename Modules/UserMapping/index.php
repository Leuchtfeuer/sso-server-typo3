<?php

use TYPO3\CMS\Core\Utility\GeneralUtility;

/** @var \Bitmotion\SingleSignon\Module\ModuleController $SOBE */
$SOBE = GeneralUtility::makeInstance('Bitmotion\\SingleSignon\\Module\\ModuleController');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();
