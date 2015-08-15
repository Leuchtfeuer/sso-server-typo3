<?php
defined ('TYPO3_MODE') or die('Access denied.');

t3lib_extMgm::addPItoST43($_EXTKEY, 'Classes/Plugin/class.tx_nawsinglesignon_pi1.php', '_pi1', 'list_type', 0);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_pre_processing'][] = 'Tx_NawSingleSignon_Hook_LogoffListener->registerLogoff';