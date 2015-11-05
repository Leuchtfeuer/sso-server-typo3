<?php
namespace Bitmotion\SingleSignon\Hook;

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

use Bitmotion\SingleSignon\Plugin\PluginController;
use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;

/**
 * Class LogoffListener
 */
class LogoffListener {

	/**
	 * fetchUserSession also triggers the logoff hook, so we must no only react on first call
	 *
	 * @var bool
	 */
	static protected $isProcessing = FALSE;

	/**
	 * @param array $params
	 * @param AbstractUserAuthentication $userAuthentication
	 */
	public function registerLogoff(array $params, AbstractUserAuthentication $userAuthentication) {
		if (self::$isProcessing || $userAuthentication->loginType !== 'FE') {
			return;
		}

		self::$isProcessing = TRUE;
		$userAuthentication = clone $userAuthentication;
		$userData = $userAuthentication->fetchUserSession(TRUE);
		self::$isProcessing = FALSE;

		if (empty($userData['uid'])) {
			return;
		}
		// This global var is used to trigger the condition which wraps the logoff URL generator
		$GLOBALS['TX_SINGLE_SIGNON']['logout'] = TRUE;

		// Attach the user authentication object for URL generation during this request
		$userAuthentication->user = $userData;
		PluginController::$loggedOffUserAuthenticationObject = $userAuthentication;
	}

}