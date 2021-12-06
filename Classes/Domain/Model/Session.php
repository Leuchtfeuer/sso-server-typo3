<?php

namespace Bitmotion\SingleSignon\Domain\Model;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Helmut Hummel <helmut.hummel@typo3.org>
 *  (c) 2021 Yassine Abid <yassine.abid@leuchtfeuer.com>
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

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class Session
 */
class Session
{
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
    protected $appId;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var int
     */
    protected $timestamp;

    /**
     * @param string $sessionHash
     * @param string $userId
     * @param string $appId
     * @param array $data
     */
    public function __construct($sessionHash, $userId, $appId, array $data)
    {
        $this->sessionHash = $sessionHash;
        $this->userId = $userId;
        $this->appId = $appId;
        $this->data = $data;

        $context = GeneralUtility::makeInstance(Context::class);
        $this->timestamp = $context->getPropertyFromAspect('date', 'timestamp');
    }

    /**
     * @return array
     */
    public function getValues()
    {
        $values = [];
        foreach (get_object_vars($this) as $name => $value) {
            $values[GeneralUtility::camelCaseToLowerCaseUnderscored($name)] = $value;
        }

        return $values;
    }
}
