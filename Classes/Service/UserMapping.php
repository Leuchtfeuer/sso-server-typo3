<?php

namespace Bitmotion\SingleSignon\Service;

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

use Bitmotion\SingleSignon\Domain\Repository\MappingPropertyRepository;
use Bitmotion\SingleSignon\Domain\Repository\UserMappingRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * @author  Dietrich Heise <typo3-ext@bitmotion.de>
 */
class UserMapping
{
    const NO_USERMAPPING_LABEL_KEY = 'LLL:EXT:single_signon/Resources/Private/Language/locallang_tca.php:single_signon.pi_flexform.no_usermapping';
    /** @var UserMappingRepository */
    private $userMappingRepository;

    /** @var MappingPropertyRepository */
    private $mappingPropertyRepository;

    public function __construct()
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->userMappingRepository = $objectManager->get(UserMappingRepository::class);
        $this->mappingPropertyRepository = $objectManager->get(MappingPropertyRepository::class);
    }

    /**
     * Used as itemProcFunc in Flexform
     */
    public function getAvailableMappingItems(array $config): array
    {
        // No Usermapping =0
        $config['items'][] = [
            $this->getLanguageService()->sL(self::NO_USERMAPPING_LABEL_KEY),
            '0'
        ];

        // configured Mappings
        $properties = $this->mappingPropertyRepository->getAllEnabledProperties();
        if (empty($properties)) {
            return $config;
        }

        foreach ($properties as $property) {
            $config['items'][] = [$property['mapping_tablename'], $property['uid']];
        }

        return $config;
    }

    /**
     * Return the mapped username for $feUser
     *
     * @param FrontendUserAuthentication $feUser
     * @param int $mappingId
     * @return string  mapped username
     * @throws \Exception
     */
    public function findUsernameForUserAndMapping(FrontendUserAuthentication $feUser, $mappingId)
    {
        if (empty($feUser)) {
            throw new \Exception('no_usermapping', 1439646263);
        }
        $mappingId = (int)$mappingId;
        // Default Table (mapping as it is)
        if ($mappingId === 0) {
            return $feUser->user['username'];
        }

        $mappingProperty = $this->mappingPropertyRepository->getPropertyByUid($mappingId);
        if (empty($mappingProperty)) {
            throw new \Exception('no_usermapping', 1638195573540);
        }

        // If allowall map undef-users to fe_usernames, else deny
        $allowAll = (bool)$mappingProperty['allowall'];
        $sysfolder_id = (int)$mappingProperty['sysfolder_id'];
        $mapping_defaultmapping = $mappingProperty['mapping_defaultmapping'];

        $userMapping = $this->userMappingRepository->getUserMappingByMappingAndUser((int)$mappingId, (int)$feUser->user['uid']);

        if ((int)$feUser->user['pid'] !== $sysfolder_id) {
            throw new \Exception('no_usermapping', 1439646264);
        }

        if (!empty($userMapping['mapping_username'])) {
            return $userMapping['mapping_username'];
        }

        if (!$allowAll) {
            throw new \Exception('No mapping was found and allow all was denied!', 1439646541);
        }

        return $mapping_defaultmapping ?: $feUser->user['username'];
    }

    /**
     * @return \TYPO3\CMS\Core\Localization\LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }
}
