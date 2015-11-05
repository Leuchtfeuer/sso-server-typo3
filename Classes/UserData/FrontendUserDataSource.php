<?php
namespace Bitmotion\SingleSignon\UserData;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Fetches the user data from the frontend user record of the currently logged in user
 */
class FrontendUserDataSource implements \Tx_SingleSignon_UserData_UserDataSourceInterface {

	/**
	 * @param array $preFetchedUserData
	 * @param array $configuration
	 * @return array
	 */
	public function fetchUserData(array $preFetchedUserData, array $configuration) {
		$requestedUserDataFields = $configuration['userDataFields'];

		$compiledUserData = array_intersect_key(
			$this->getTypoScriptFrontendController()->fe_user->user,
			array_flip($requestedUserDataFields)
		);

		if (!empty($compiledUserData['usergroup'])) {
			$groupIds = explode(',', $compiledUserData['usergroup']);
			$userGroupNames = array();
			foreach ($groupIds as $groupId) {
				$row = $this->getDatabaseConnection()->exec_SELECTgetSingleRow('*', 'fe_groups', 'uid=' . (int)$groupId);
				$userGroupNames[] = $row['title'];
			}
			$compiledUserData['usergroup'] = implode(',', $userGroupNames);
		}

		return array_replace_recursive($preFetchedUserData, $compiledUserData);

	}


	/**
	 * @return DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

	/**
	 * @return TypoScriptFrontendController
	 */
	protected function getTypoScriptFrontendController() {
		return $GLOBALS['TSFE'];
	}


}
