<?php
$extPath = t3lib_extMgm::extPath('single_signon');

return array(
	'tx_singlesignon_pi1' => $extPath . 'Classes/Plugin/PluginController.php',
	'tx_singlesignon_pi1_wizicon' => $extPath . 'Classes/Plugin/Wizicon.php',
	'tx_singlesignon_usermapping' => $extPath . 'Classes/UserMapping.php',
	'tx_singlesignon_module1' => $extPath . 'Classes/Module/ModuleController.php',
	'tx_singlesignon_configuration_flexformarrayconverter' => $extPath . 'Classes/Configuration/FlexFormService.php',
	'tx_singlesignon_domain_model_session' => $extPath . 'Classes/Domain/Model/Session.php',
	'tx_singlesignon_domain_repository_sessionrepository' => $extPath . 'Classes/Domain/Repository/SessionRepository.php',
	'tx_singlesignon_hook_logofflistener' => $extPath . 'Classes/Hook/LogoffListener.php',
);
