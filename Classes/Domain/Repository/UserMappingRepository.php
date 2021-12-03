<?php

namespace Bitmotion\SingleSignon\Domain\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class UserMappingRepository
{
    private const USER_MAPPING_TABLE_NAME = 'tx_singlesignon_usermap';

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

    private function getQueryBuilder(): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::USER_MAPPING_TABLE_NAME);
    }
}
