<?php

namespace Bitmotion\SingleSignon\Tests\Functional\Domain\Repository;

use Bitmotion\SingleSignon\Domain\Repository\UserMappingRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class UserMappingRepositoryTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/single_signon'
    ];

    /** @var UserMappingRepository */
    private $userMappingRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/default.csv');

        $this->userMappingRepository = GeneralUtility::makeInstance(ObjectManager::class)
            ->get(UserMappingRepository::class);
    }

    public function testAddUserMap(): void
    {
        $this->userMappingRepository->addUserMap(55, 1, 'someMapping');
        self::assertCount(2, $this->userMappingRepository->getUserMappings());
    }
}
