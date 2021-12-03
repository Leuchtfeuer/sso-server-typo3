<?php

namespace Bitmotion\SingleSignon\Tests\Functional\Sevice;

use Bitmotion\SingleSignon\Service\UserMapping;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class UserMappingTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/single_signon'
    ];

    /** @var UserMapping */
    private $userMapping;

    protected function setUp(): void
    {
        parent::setUp();
        Bootstrap::initializeLanguageObject();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/default.csv');

        $this->userMapping = new UserMapping();
    }

    public function testGetAvailableMappingItems(): void
    {
        $items = $this->userMapping->getAvailableMappingItems([]);
        self::assertCount(3, $items['items']);

        $item = $items['items'][0];
        self::assertSame('No Usermapping', $item[0]);
        self::assertEquals(0, $item[1]);

        $item = $items['items'][1];
        self::assertSame('mappingTable1', $item[0]);
        self::assertSame(1, $item[1]);
    }

    public function feUserDataProvider(): array
    {
        return [
            [1, 1, 'mappedUsername'],
            [1, 4, 'default'],
            [0, 4, 'TypoUsername'],
        ];
    }

    /**
     * @dataProvider feUserDataProvider
     */
    public function testFindUsernameForUserAndMapping(int $mappingId, int $uid, string $expected): void
    {
        $feUser = new FrontendUserAuthentication();
        $feUser->user = [
            'pid' => 45,
            'uid' => $uid,
            'username' => 'TypoUsername'
        ];

        self::assertSame($expected, $this->userMapping->findUsernameForUserAndMapping($feUser, $mappingId));
    }
}
