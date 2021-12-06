<?php

namespace Bitmotion\SingleSignon\Domain\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/*
 * This file is part of the "Single Signon" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * Yassine Abid <yassine.abid@leuchtfeuer.com>, Leuchtfeuer Digital Marketing
 */

class MappingPropertyRepository
{
    private const MAPPING_PROPERTY_TABLE_NAME = 'tx_singlesignon_properties';
    private const ENABLE = 0;

    public function getAllEnabledProperties(): array
    {
        $qb = $this->getQueryBuilder();

        return $qb->select('*')
            ->from(self::MAPPING_PROPERTY_TABLE_NAME)
            ->where(
                $qb->expr()->eq('deleted', self::ENABLE)
            )
            ->execute()
            ->fetchAll() ?? [];
    }

    public function getPropertyByUid(int $uid): array
    {
        $qb = $this->getQueryBuilder();

        $result = $qb->select('*')
                ->from(self::MAPPING_PROPERTY_TABLE_NAME)
                ->where(
                    $qb->expr()->eq('uid', $qb->createNamedParameter($uid, \PDO::PARAM_INT))
                )
                ->execute()
                ->fetchAll();

        return empty($result) ? [] : reset($result);
    }

    public function addMappingProperty(
        int $folderUid,
        string $tablename,
        string $defaultMapping,
        bool $allow
    ): int {
        $values = [
            'tstamp'  => time(),
            'crdate'  => time(),
            'cruser_id'  => $GLOBALS['BE_USER']->user['uid'] ?? 0,
            'mapping_tablename' => $tablename,
            'mapping_defaultmapping' => $defaultMapping,
            'allowall' => (int)$allow,
            'sysfolder_id' => $folderUid
        ];

        $this->getQueryBuilder()
            ->insert(self::MAPPING_PROPERTY_TABLE_NAME)
            ->values($values)
            ->execute();

        $qb = $this->getQueryBuilder();
        return $qb->select('uid')
            ->from(self::MAPPING_PROPERTY_TABLE_NAME)
            ->where(
                $qb->expr()->eq('tstamp', $qb->createNamedParameter($values['tstamp'], \PDO::PARAM_INT))
            )
            ->andwhere(
                $qb->expr()->eq('crdate', $qb->createNamedParameter($values['crdate'], \PDO::PARAM_INT))
            )
            ->andwhere(
                $qb->expr()->eq('mapping_tablename', $qb->createNamedParameter($values['mapping_tablename'], \PDO::PARAM_STR))
            )
            ->andwhere(
                $qb->expr()->eq('mapping_defaultmapping', $qb->createNamedParameter($values['mapping_defaultmapping'], \PDO::PARAM_STR))
            )
            ->andwhere(
                $qb->expr()->eq('sysfolder_id', $qb->createNamedParameter($values['sysfolder_id'], \PDO::PARAM_INT))
            )
            ->orderBy('uid', 'DESC')
            ->execute()
            ->fetchColumn();
    }

    public function updateMappingProperty(
        int $uid,
        string $tablename,
        string $defaultMapping,
        bool $allow
    ): void {
        $qb = $this->getQueryBuilder();
        $qb->update(self::MAPPING_PROPERTY_TABLE_NAME)
            ->where(
                $qb->expr()->eq('uid', $qb->createNamedParameter($uid, \PDO::PARAM_INT))
            )
            ->set('tstamp', time())
            ->set('mapping_tablename', $tablename)
            ->set('mapping_defaultmapping', $defaultMapping)
            ->set('allowall', (int)$allow)
            ->execute();
    }

    public function deleteMappingProperty(int $property): void
    {
        $qb = $this->getQueryBuilder();
        $qb->delete(self::MAPPING_PROPERTY_TABLE_NAME)
            ->where(
                $qb->expr()->eq('uid', $property)
            )
            ->execute();
    }

    private function getQueryBuilder(): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::MAPPING_PROPERTY_TABLE_NAME);
    }
}
