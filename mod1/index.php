<?php
$LANG->includeLLFile('EXT:single_signon/mod1/locallang.xml');
// This checks permissions and exits if the users has no permission for entry.
$BE_USER->modAccess($MCONF, 1);

/** @var tx_singlesignon_module1 $SOBE */
$SOBE = t3lib_div::makeInstance('tx_singlesignon_module1');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();
