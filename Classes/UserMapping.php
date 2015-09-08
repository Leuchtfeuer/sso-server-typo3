<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2005-2006 Dietrich Heise <typo3-ext@bitmotion.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * @author  Dietrich Heise <typo3-ext@bitmotion.de>
 */
class tx_singlesignon_usermapping {

	/**
	 * Used as itemProcFunc in Flexform
	 *
	 * @param array $config
	 * @return array Item config
	 */
	public function getAvailableMappingItems($config) {
		// No Usermapping =0
		$config['items'][] = array($this->getLanguageService()->sL('LLL:EXT:single_signon/Resources/Private/Language/locallang_tca.php:single_signon.pi_flexform.no_usermapping'), '0');

		// configured Mappings
		$result = $this->getDatabaseConnection()->exec_SELECTquery('*', 'tx_singlesignon_properties', 'deleted=0');
		while ($row = $this->getDatabaseConnection()->sql_fetch_assoc($result)) {
			$config['items'][] = array($row['mapping_tablename'], $row['uid']);
		}
		return $config;
	}

	/**
	 * Return the mapped username for $feUser
	 *
	 * @param tslib_feUserAuth $feUser
	 * @param int $mappingId
	 * @return string  mapped username
	 * @throws Exception
	 */
	public function findUsernameForUserAndMapping(tslib_feUserAuth $feUser, $mappingId) {
		if (empty($feUser)) {
			throw new Exception('no_usermapping', 1439646263);
		}
		$mappingId = (int)$mappingId;
		// Default Table (mapping as it is)
		if ($mappingId === 0) {
			return $feUser->user['username'];
		}

		$result = $this->getDatabaseConnection()->exec_SELECTquery('*', 'tx_singlesignon_properties', 'deleted=0 AND uid=' . (int)$mappingId);
		$row = $this->getDatabaseConnection()->sql_fetch_assoc($result);

		// If allowall map undef-users to fe_usernames, else deny
		$allowAll = (bool)$row['allowall'];
		$sysfolder_id = (int)$row['sysfolder_id'];
		$mapping_defaultmapping = $row['mapping_defaultmapping'];

		$result = $this->getDatabaseConnection()->exec_SELECTquery('*', 'tx_singlesignon_usermap', 'mapping_id=' . (int)$mappingId . ' AND fe_uid=' . (int)$uid);
		$row = $this->getDatabaseConnection()->sql_fetch_assoc($result);

		if ((int)$feUser->user['pid'] !== $sysfolder_id) {
			throw new Exception('no_usermapping', 1439646264);
		}

		if (empty($row['mapping_username']) && $allowAll) {
			return $mapping_defaultmapping ?: $feUser->user['username'];
		}

		if (empty($row['mapping_username'])) {
			if (!$allowAll) {
				throw new Exception('No mapping was found and allow all was denied!', 1439646541);
			}
			return $mapping_defaultmapping ?: $feUser->user['username'];
		}

		return $row['mapping_username'];
	}

	/**
	 * @return t3lib_DB
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

	/**
	 * @return language
	 */
	protected function getLanguageService() {
		return $GLOBALS['LANG'];
	}

}
