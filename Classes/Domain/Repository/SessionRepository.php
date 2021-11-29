<?php

namespace Bitmotion\SingleSignon\Domain\Repository;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Helmut Hummel <helmut.hummel@typo3.org>
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

use Bitmotion\SingleSignon\Domain\Model\Session;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class SessionRepository
 */
class SessionRepository
{
    private const SESSION_TABLE_NAME = 'tx_singlesignon_sessions';

    public function addOrUpdateSession(Session $session): void
    {
        $values = [];
        foreach ($session->getValues() as $name => $value) {
            $values[$name] = is_scalar($value) ? $value : serialize($value);
        }

        $this->sessionExists($values)
            ? $this->updateSession($values)
            : $this->addSession($values);
    }

    public function findBySessionId(string $sessionId): ?array
    {
        $qb = $this->getQueryBuilder();

        $result = $qb->select('*')
            ->from(self::SESSION_TABLE_NAME, 's')
            ->where(
                $qb->expr()->eq(
                    's.session_hash',
                    $qb->createNamedParameter($sessionId, \PDO::PARAM_STR)
                )
            )
            ->execute()
            ->fetchAll();

        return empty($result) ? null : $result;
    }

    public function deleteBySessionHashUserIdAppId(
        string $sessionHash,
        int $userId,
        string $appId
    ): void {
        $qb = $this->getQueryBuilder();

        $qb->delete(self::SESSION_TABLE_NAME)
            ->where(
                $qb->expr()->eq(
                    'session_hash',
                    $qb->createNamedParameter($sessionHash, \PDO::PARAM_STR)
                )
            )
            ->andWhere(
                $qb->expr()->eq(
                    'user_id',
                    $qb->createNamedParameter($userId, \PDO::PARAM_INT)
                )
            )
            ->andWhere(
                $qb->expr()->eq(
                    'app_id',
                    $qb->createNamedParameter($appId, \PDO::PARAM_STR)
                )
            )
            ->execute();
    }

    /**
     * Removes the identifiers and adds ON DUPLICATE KEY statement for data values
     *
     * @param Session $session
     * @return string
     */
    protected function getOnDuplicateKeyStatement(Session $session)
    {
        $updateValues = [];
        foreach (array_slice($session->getValues(), 3) as $name => $value) {
            $updateValues[] = "$name=VALUES($name)";
        }
        return ' ON DUPLICATE KEY UPDATE ' . implode(', ', $updateValues);
    }

    private function getQueryBuilder(): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::SESSION_TABLE_NAME);
    }

    private function updateSession(array $values): void
    {
        $qb = $this->getQueryBuilder();

        $qb->update(self::SESSION_TABLE_NAME)
            ->where(
                $qb->expr()->eq(
                    'session_hash',
                    $qb->createNamedParameter($values['session_hash'], \PDO::PARAM_STR)
                )
            )
            ->andWhere(
                $qb->expr()->eq(
                    'user_id',
                    $qb->createNamedParameter($values['user_id'], \PDO::PARAM_INT)
                )
            )
            ->andWhere(
                $qb->expr()->eq(
                    'app_id',
                    $qb->createNamedParameter($values['app_id'], \PDO::PARAM_STR)
                )
            )
            ->set('data', $values['data'])
            ->set('timestamp', $values['timestamp'], false)
            ->execute();
    }

    private function addSession(array $values): void
    {
        $this->getQueryBuilder()
            ->insert(self::SESSION_TABLE_NAME)
            ->values($values)
            ->execute();
    }

    private function sessionExists(array $values): bool
    {
        $qb = $this->getQueryBuilder();

        $result = $qb->select('*')
            ->from(self::SESSION_TABLE_NAME)
            ->where(
                $qb->expr()->eq(
                    'session_hash',
                    $qb->createNamedParameter($values['session_hash'], \PDO::PARAM_STR)
                )
            )
            ->andWhere(
                $qb->expr()->eq(
                    'user_id',
                    $qb->createNamedParameter($values['user_id'], \PDO::PARAM_INT)
                )
            )
            ->andWhere(
                $qb->expr()->eq(
                    'app_id',
                    $qb->createNamedParameter($values['app_id'], \PDO::PARAM_STR)
                )
            )
            ->setMaxResults(1)
            ->execute()
            ->fetchAll();

        return !empty($result);
    }
}
