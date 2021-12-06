<?php

namespace Bitmotion\SingleSignon\Tests\Functional\Sevice;

use Bitmotion\SingleSignon\Service\ModuleHelper;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class ModuleHelperTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/single_signon'
    ];

    /** @var ModuleHelper */
    private $moduleHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/default.csv');

        $this->moduleHelper = new ModuleHelper();
    }

    public function testGetFoldersWithUsers(): void
    {
        $folders = $this->moduleHelper->getFoldersWithUsers();
        self::assertCount(1, $folders);
        self::assertEquals(2, reset($folders)['users']);
    }

    public function testGetUsersByFolder(): void
    {
        $users = $this->moduleHelper->getUsersByFolder(2);
        self::assertCount(2, $users);
    }

    public function testGetUsersByFolderAndMappingId(): void
    {
        $users = $this->moduleHelper->getUsersByFolderAndMappingId(2, 1);
        self::assertCount(2, $users);
        self::assertEquals('1', reset($users)['uid']);
        self::assertSame('user 1', reset($users)['username']);
        self::assertSame('mappedUsername', reset($users)['mapping_username']);
    }

    public function testGetUsersByFolderAndMappingIdWithoutUserMapping(): void
    {
        $users = $this->moduleHelper->getUsersByFolderAndMappingId(2, 5);
        self::assertCount(2, $users);
        self::assertEquals('1', reset($users)['uid']);
        self::assertSame('user 1', reset($users)['username']);
        self::assertEmpty(reset($users)['mapping_username']);
    }

    public function testGetUsersByFolderWithoutUsers(): void
    {
        self::assertEmpty($this->moduleHelper->getUsersByFolder(5));
    }
}
