<?php

namespace Bitmotion\SingleSignon\UserData;

use Bitmotion\SingleSignon\Domain\Repository\FrontendUserGroupRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUserGroup;
use TYPO3\CMS\Extbase\Object\ObjectManager;

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

class FrontendUserDataSource implements UserDataSourceInterface
{
    /** @var FrontendUserGroupRepository */
    private $frontendUserGroupRespository;

    /** @var array */
    private $user;

    public function __construct()
    {
        $this->frontendUserGroupRespository = GeneralUtility::makeInstance(ObjectManager::class)
            ->get(FrontendUserGroupRepository::class);
        $this->user = empty($GLOBALS['TSFE']->fe_user->user) ? [] : $GLOBALS['TSFE']->fe_user->user;
    }

    /**
     * @param array $preFetchedUserData
     * @param array $configuration
     * @return array
     */
    public function fetchUserData(array $preFetchedUserData, array $configuration)
    {
        $requestedUserDataFields = $configuration['userDataFields'];

        $compiledUserData = array_intersect_key(
            $this->user,
            array_flip($requestedUserDataFields)
        );

        if (!empty($compiledUserData['usergroup'])) {
            $groupIds = explode(',', $compiledUserData['usergroup']);
            $userGroupNames = [];
            foreach ($groupIds as $groupId) {
                /** @var FrontendUserGroup|null $userGroup */
                $userGroup = $this->frontendUserGroupRespository->findOneByUid((int)$groupId);
                if ($userGroup) {
                    $userGroupNames[] = $userGroup->getTitle();
                }
            }
            $compiledUserData['usergroup'] = implode(',', $userGroupNames);
        }

        return array_replace_recursive($preFetchedUserData, $compiledUserData);
    }
}
