<?php

namespace Bitmotion\SingleSignon\Tests\Functional\Domain\Repository;

use Bitmotion\SingleSignon\Domain\Repository\MappingPropertyRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class MappingPropertyRepositoryTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/single_signon'
    ];

    /** @var MappingPropertyRepository */
    private $mappingRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/default.csv');

        $this->mappingRepository = GeneralUtility::makeInstance(ObjectManager::class)
            ->get(MappingPropertyRepository::class);
    }

    public function testAddMappingProperty(): void
    {
        $uid = $this->mappingRepository->addMappingProperty(55, 'someTablename', 'someDefaultMapping', true);
        self::assertSame(4, $uid);
    }

    public function testUpdateMappingProperty(): void
    {
        $this->mappingRepository->updateMappingProperty(
            1,
            't',
            'd',
            false
        );

        self::assertSame(0, $this->mappingRepository->getPropertyByUid(1)['allowall']);
    }
}
