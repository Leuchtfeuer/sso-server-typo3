<?php

namespace Bitmotion\SingleSignon\Controller;

use Bitmotion\SingleSignon\Domain\Repository\MappingPropertyRepository;
use Bitmotion\SingleSignon\Domain\Repository\UserMappingRepository;
use Bitmotion\SingleSignon\Service\ModuleHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/*
 * This file is part of the "Single Signon" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * Yassine Abid <yassine.abid@leuchtfeuer.com>, Leuchtfeuer Digital Marketing
 */

class MappingTableController extends BackendController
{
    /** @var MappingPropertyRepository */
    private $mappingRepository;

    /** @var UserMappingRepository */
    private $userMappingRepository;

    /** @var ModuleHelper */
    private $moduleHelper;

    public function __construct()
    {
        $this->mappingRepository = GeneralUtility::makeInstance(ObjectManager::class)
            ->get(MappingPropertyRepository::class);

        $this->userMappingRepository = GeneralUtility::makeInstance(ObjectManager::class)
            ->get(UserMappingRepository::class);

        $this->moduleHelper = new ModuleHelper();

        if (version_compare(TYPO3_version, '10.0.0', '<')) {
            parent::__construct();
        }
    }

    public function listAction(): void
    {
        $this->view->assignMultiple([
            'mappings' => $this->mappingRepository->getAllEnabledProperties(),
        ]);
    }

    public function selectFolderAction(): void
    {
        $this->addButton('menu.button.cancel', 'list', 'MappingTable', 'actions-close');

        $this->view->assignMultiple([
            'folders' => $this->moduleHelper->getFoldersWithUsers(),
        ]);
    }

    public function createFormAction(int $folderUid): void
    {
        $this->addButton('menu.button.cancel', 'selectFolder', 'MappingTable', 'actions-close');

        $this->view->assignMultiple([
            'folder' => $folderUid,
            'users' => $this->moduleHelper->getUsersByFolder($folderUid),
        ]);
    }

    public function newAction(array $property, array $users): void
    {
        $mappingPropertyUid = $this->mappingRepository->addMappingProperty(
            $property['sysfolder_id'],
            $property['mapping_tablename'],
            $property['mapping_defaultmapping'],
            !empty($property['allowall'])
        );

        foreach ($users as $feUid => $user) {
            $this->userMappingRepository->addUserMap(
                $mappingPropertyUid,
                $feUid,
                reset($user)
            );
        }

        $this->addFlashMessage($this->getTranslation('message.property.created.text'), $this->getTranslation('message.property.created.title'));
        $this->redirect('list');
    }

    public function editAction(int $mappingId): void
    {
        $this->addButton('menu.button.cancel', 'list', 'MappingTable', 'actions-close');

        $mapping = $this->mappingRepository->getPropertyByUid($mappingId);
        $this->view->assignMultiple([
            'folder' => $mapping['sysfolder_id'],
            'mapping' => $mapping,
            'users' => $this->moduleHelper->getUsersByFolderAndMappingId($mapping['sysfolder_id'], $mapping['uid']),
        ]);
    }

    public function updateAction(array $property, array $users): void
    {
        $this->mappingRepository->updateMappingProperty(
            (int)$property['uid'],
            $property['mapping_tablename'],
            $property['mapping_defaultmapping'],
            !empty($property['allowall'])
        );

        foreach ($users as $feUid => $user) {
            $this->userMappingRepository->updateUserMapping(
                (int)$property['uid'],
                $feUid,
                reset($user)
            );
        }

        $this->addFlashMessage($this->getTranslation('message.property.updated.text'), $this->getTranslation('message.property.updated.title'));
        $this->redirect('list');
    }

    public function deleteAction(int $property): void
    {
        $this->mappingRepository->deleteMappingProperty($property);
        $this->userMappingRepository->deleteUserMappingByMappingId($property);

        $this->addFlashMessage($this->getTranslation('message.property.deleted.text'), $this->getTranslation('message.property.deleted.title'));
        $this->redirect('list');
    }
}
