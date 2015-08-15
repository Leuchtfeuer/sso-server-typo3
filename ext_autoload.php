<?php
$extPath = t3lib_extMgm::extPath('naw_single_signon');

return array(
	'tx_nawsinglesignon_pi1' => $extPath . 'Classes/Plugin/class.tx_nawsinglesignon_pi1.php',
	'tx_nawsinglesignon_pi1_wizicon' => $extPath . 'Classes/Plugin/class.tx_nawsinglesignon_pi1_wizicon.php',
	'tx_nawsinglesignon_usermapping' => $extPath . 'Classes/class.tx_nawsinglesignon_usermapping.php',
	'tx_nawsinglesignon_module1' => $extPath . 'Classes/Module/class.tx_nawsinglesignon_module1.php',
	'tx_nawsinglesignon_configuration_flexformarrayconverter' => $extPath . 'Classes/Configuration/FlexFormService.php',
	'tx_nawsinglesignon_domain_model_session' => $extPath . 'Classes/Domain/Model/Session.php',
	'tx_nawsinglesignon_domain_repository_sessionrepository' => $extPath . 'Classes/Domain/Repository/SessionRepository.php',
);
