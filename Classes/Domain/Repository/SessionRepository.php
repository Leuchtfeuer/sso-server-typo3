<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Helmut Hummel <helmut.hummel@typo3.org>
 *  All rights reserved
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the text file GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class Tx_NawSingleSignon_Domain_Repository_SessionRepository
 */
class Tx_NawSingleSignon_Domain_Repository_SessionRepository {

	/**
	 * @var string
	 */
	protected $tableName = 'tx_nawsinglesignon_sessions';

	/**
	 * @var t3lib_DB
	 */
	protected $databaseConnection;

	/**
	 * @param t3lib_DB $databaseConnection
	 */
	public function __construct(t3lib_DB $databaseConnection = NULL) {
		$this->databaseConnection = $databaseConnection ?: $GLOBALS['TYPO3_DB'];
	}

	/**
	 * Adds or updates the session table
	 *
	 * @param Tx_NawSingleSignon_Domain_Model_Session $session
	 */
	public function addOrUpdateSession(Tx_NawSingleSignon_Domain_Model_Session $session) {
		$values = array();
		foreach ($session->getValues() as $name => $value) {
			$values[$name] = is_scalar($value) ? $value : serialize($value);
		}
		$insertQuery = $this->databaseConnection->INSERTquery($this->tableName, $values);
		$this->databaseConnection->sql_query($insertQuery . $this->getOnDuplicateKeyStatement($session));
	}

	/**
	 * Removes the identifiers and adds ON DUPLICATE KEY statement for data values
	 *
	 * @param Tx_NawSingleSignon_Domain_Model_Session $session
	 * @return string
	 */
	protected function getOnDuplicateKeyStatement(Tx_NawSingleSignon_Domain_Model_Session $session) {
		$updateValues = array();
		foreach (array_slice($session->getValues(), 3) as $name => $value) {
			$updateValues[] = "$name=VALUES($name)";
		}
		return ' ON DUPLICATE KEY UPDATE ' . implode(', ', $updateValues);
	}

}