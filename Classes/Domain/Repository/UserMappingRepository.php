<?php

namespace Bitmotion\SingleSignon\Domain\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class UserMappingRepository
{
    public const USER_MAPPING_TABLE_NAME = 'tx_singlesignon_usermap';

    public function getUserMappingByMappingAndUser(int $mappingUid, int $userUid): array
    {
        $qb = $this->getQueryBuilder();

        $result = $qb->select('*')
            ->from('tx_singlesignon_usermap')
            ->where(
                $qb->expr()->eq('mapping_id', $qb->createNamedParameter($mappingUid, \PDO::PARAM_INT))
            )
            ->andWhere(
                $qb->expr()->eq('fe_uid', $qb->createNamedParameter($userUid, \PDO::PARAM_INT))
            )
            ->setMaxResults(1)
            ->execute()
            ->fetchAll();

        return empty($result) ? [] : reset($result);
    }

    public function getUserMappings(): ?array
    {
        $qb = $this->getQueryBuilder();

        return $qb->select('*')
            ->from('tx_singlesignon_usermap')
            ->execute()
            ->fetchAll() ?? null;
    }

    public function addUserMap(
        int $mappingId,
        string $feUid,
        string $mappingUsername
    ): void {
        $values = [
            'tstamp' => time(),
            'crdate' => time(),
            'cruser_id' => $GLOBALS['BE_USER']->user['uid'] ?? 0,
            'mapping_id' => $mappingId,
            'fe_uid' => $feUid,
            'mapping_username' => $mappingUsername,
        ];

        $this->getQueryBuilder()
            ->insert(self::USER_MAPPING_TABLE_NAME)
            ->values($values)
            ->execute();
    }

    public function updateUserMapping(
        int $mappingId,
        string $feUserId,
        string $mappingUsername
    ): void {
        $qb = $this->getQueryBuilder();
        $qb->update(self::USER_MAPPING_TABLE_NAME)
            ->where(
                $qb->expr()->eq('fe_uid', $qb->createNamedParameter($feUserId, \PDO::PARAM_INT))
            )
            ->andWhere(
                $qb->expr()->eq('mapping_id', $qb->createNamedParameter($mappingId, \PDO::PARAM_INT))
            )
            ->set('tstamp', time())
            ->set('mapping_username', $mappingUsername)
            ->execute();
    }

    public function deleteUserMappingByMappingId(int $uid): void
    {
        $qb = $this->getQueryBuilder();
        $qb->delete(self::USER_MAPPING_TABLE_NAME)
            ->where(
                $qb->expr()->eq('mapping_id', $uid)
            )
            ->execute();
    }

    private function getQueryBuilder(): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::USER_MAPPING_TABLE_NAME);
    }
}
