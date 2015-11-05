<?php
$extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('single_signon');

return array(
	'tx_singlesignon_hook_logofflistener' => $extPath . 'Classes/Hook/LogoffListener.php',
);
