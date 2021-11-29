<?php

namespace Bitmotion\SingleSignon\Tests\Functional\Domain\Repository;

use Bitmotion\SingleSignon\Domain\Model\Session;
use Bitmotion\SingleSignon\Domain\Repository\SessionRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class SessionRepositoryTest extends FunctionalTestCase
{
    private const SESSION_HASH_EXAMPLE = '0sasd49885d8s';
    private const NEW_SESSION_HASH = '22sd49885d8s';
    private const USER_ID_EXAMPLE = 1;
    private const APP_ID_EXAMPLE = '1';

    protected $testExtensionsToLoad = [
        'typo3conf/ext/single_signon'
    ];

    /** @var SessionRepository */
    private $sessionRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/default.csv');

        $this->sessionRepository = GeneralUtility::makeInstance(ObjectManager::class)
            ->get(SessionRepository::class);
    }

    public function testFindBySessionId(): void
    {
        $sessions = $this->sessionRepository->findBySessionId(self::SESSION_HASH_EXAMPLE);
        self::assertCount(1, $sessions);

        $session = reset($sessions);
        self::assertSame(self::SESSION_HASH_EXAMPLE, $session['session_hash']);
        self::assertSame(self::USER_ID_EXAMPLE, $session['user_id']);
        self::assertSame(self::APP_ID_EXAMPLE, $session['app_id']);
        self::assertSame('data', $session['data']);
        self::assertSame(15891, $session['timestamp']);
    }

    public function testFindBySessionIdWithNoValidSession(): void
    {
        self::assertNull($this->sessionRepository->findBySessionId('0'));
    }

    public function testAddOrUpdateSessionWithExistsSession(): void
    {
        $sessions = $this->sessionRepository->findBySessionId(self::SESSION_HASH_EXAMPLE);
        self::assertCount(1, $sessions);

        $session = reset($sessions);
        self::assertSame(self::SESSION_HASH_EXAMPLE, $session['session_hash']);
        self::assertSame(self::USER_ID_EXAMPLE, $session['user_id']);
        self::assertSame(self::APP_ID_EXAMPLE, $session['app_id']);
        self::assertSame('data', $session['data']);
        self::assertSame(15891, $session['timestamp']);

        $modifiedSession = new Session(
            self::SESSION_HASH_EXAMPLE,
            self::USER_ID_EXAMPLE,
            self::APP_ID_EXAMPLE,
            [
                'someModifiedData'
            ]
        );

        $this->sessionRepository->addOrUpdateSession($modifiedSession);

        $sessions = $this->sessionRepository->findBySessionId(self::SESSION_HASH_EXAMPLE);
        self::assertCount(1, $sessions);
        $session = reset($sessions);
        self::assertSame(self::SESSION_HASH_EXAMPLE, $session['session_hash']);
        self::assertSame(self::USER_ID_EXAMPLE, $session['user_id']);
        self::assertSame(self::APP_ID_EXAMPLE, $session['app_id']);
        self::assertSame('a:1:{i:0;s:16:"someModifiedData";}', $session['data']);
        self::assertNotEquals(15891, $session['timestamp']);
    }

    public function testAddOrUpdateSessionWithNewSession(): void
    {
        $sessions = $this->sessionRepository->findBySessionId(self::NEW_SESSION_HASH);
        self::assertEmpty($sessions);

        $newSession = new Session(
            self::NEW_SESSION_HASH,
            self::USER_ID_EXAMPLE,
            self::APP_ID_EXAMPLE,
            [
                'Data'
            ]
        );

        $this->sessionRepository->addOrUpdateSession($newSession);

        $sessions = $this->sessionRepository->findBySessionId(self::NEW_SESSION_HASH);
        self::assertCount(1, $sessions);
        $session = reset($sessions);
        self::assertSame(self::NEW_SESSION_HASH, $session['session_hash']);
        self::assertSame(self::USER_ID_EXAMPLE, $session['user_id']);
        self::assertSame(self::APP_ID_EXAMPLE, $session['app_id']);
        self::assertSame('a:1:{i:0;s:4:"Data";}', $session['data']);
    }

    public function testDeleteBySessionHashUserIdAppId(): void
    {
        $sessions = $this->sessionRepository->findBySessionId(self::SESSION_HASH_EXAMPLE);
        self::assertCount(1, $sessions);

        $sessions = $this->sessionRepository->deleteBySessionHashUserIdAppId(
            self::SESSION_HASH_EXAMPLE,
            self::USER_ID_EXAMPLE,
            self::APP_ID_EXAMPLE
        );

        $sessions = $this->sessionRepository->findBySessionId(self::SESSION_HASH_EXAMPLE);
        self::assertEmpty($sessions);
    }
}
