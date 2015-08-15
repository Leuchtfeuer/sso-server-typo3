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
 * Class Tx_NawSingleSignon_Domain_Model_Session
 */
class Tx_NawSingleSignon_Domain_Model_Session {

	/**
	 * @var string
	 */
	protected $sessionHash;

	/**
	 * @var string
	 */
	protected $userId;

	/**
	 * @var string
	 */
	protected $tpaId;

	/**
	 * @var array
	 */
	protected $data;

	/**
	 * @var array
	 */
	protected $timestamp;

	/**
	 * @param string $sessionHash
	 * @param string $userId
	 * @param string $tpaId
	 * @param array $data
	 */
	public function __construct($sessionHash, $userId, $tpaId, array $data) {
		$this->sessionHash = $sessionHash;
		$this->userId = $userId;
		$this->tpaId = $tpaId;
		$this->data = $data;
		$this->timestamp = $GLOBALS['EXEC_TIME'];
	}

	/**
	 * @return array
	 */
	public function getValues() {
		$values = array();
		foreach (get_object_vars($this) as $name => $value) {
			$values[t3lib_div::camelCaseToLowerCaseUnderscored($name)] = $name === 'data' ? serialize($value) : $value;
		}

		return $values;
	}

}