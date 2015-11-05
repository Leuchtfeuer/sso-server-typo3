<?php
$extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('single_signon');

return array(
	'tx_singlesignon_pi1_wizicon' => $extPath . 'Classes/Plugin/Wizicon.php',
	'tx_singlesignon_usermapping' => $extPath . 'Classes/UserMapping.php',
	'tx_singlesignon_configuration_flexformarrayconverter' => $extPath . 'Classes/Configuration/FlexFormService.php',
	'tx_singlesignon_domain_model_session' => $extPath . 'Classes/Domain/Model/Session.php',
	'tx_singlesignon_domain_repository_sessionrepository' => $extPath . 'Classes/Domain/Repository/SessionRepository.php',
	'tx_singlesignon_hook_logofflistener' => $extPath . 'Classes/Hook/LogoffListener.php',
	'tx_singlesignon_userdata_frontenduserdatasource' => $extPath . 'Classes/UserData/FrontendUserDataSource.php',
	'tx_singlesignon_userdata_userdatasourceinterface' => $extPath . 'Classes/UserData/UserDataSourceInterface.php',
);
