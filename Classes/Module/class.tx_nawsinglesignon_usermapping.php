<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2005-2006 Dietrich Heise <typo3-ext@naw.info>
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
 * @author  Dietrich Heise <typo3-ext@naw.info>
 */
class tx_nawsinglesignon_usermapping {

	public function usermapping($config) {
		// No Usermapping =0
		$config['items'][] = array($this->getLanguageService()->sL('LLL:EXT:naw_single_signon/locallang_tca.php:naw_single_signon.pi_flexform.no_usermapping'), '0');

		// configured Mappings
		$result = $this->getDatabaseConnection()->exec_SELECTquery('*', 'tx_nawsinglesignon_properties', 'deleted=0');
		while ($row = $this->getDatabaseConnection()->sql_fetch_assoc($result)) {
			$config['items'][] = array($row['mapping_tablename'], $row['uid']);
		}
		return $config;
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
