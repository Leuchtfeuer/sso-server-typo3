<?php

declare(strict_types=1);

namespace Bitmotion\SingleSignon\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;

class FrontendUserGroupRepository extends \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository
{
    public function initializeObject(): void
    {
        $defaultQuerySettings = $this->objectManager->get(Typo3QuerySettings::class);
        $defaultQuerySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($defaultQuerySettings);
    }
}
