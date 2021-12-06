<?php

namespace Bitmotion\SingleSignon\Service;

use Bitmotion\SingleSignon\Domain\Repository\UserMappingRepository;
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

class ModuleHelper
{
    private const PAGES_TABLE_NAME = 'pages';
    private const FE_USERS_TABLE_NAME = 'fe_users';
    private const FOLDER_DOKTYPE = 254;

    public function getFoldersWithUsers(): ?array
    {
        $qb = $this->getQueryBuilder(self::PAGES_TABLE_NAME);

        return $qb->selectLiteral('p.uid, p.title, count(u.uid) as users')
            ->from(self::PAGES_TABLE_NAME, 'p')
            ->leftJoin('p', self::FE_USERS_TABLE_NAME, 'u', 'u.pid = p.uid')
            ->where(
                $qb->expr()->eq('p.doktype', $qb->createNamedParameter(self::FOLDER_DOKTYPE, \PDO::PARAM_INT))
            )
            ->groupBy('p.uid')
            ->execute()
            ->fetchAll();
    }

    public function getUsersByFolder(int $folderUid): ?array
    {
        $qb = $this->getQueryBuilder(self::FE_USERS_TABLE_NAME);

        return $qb->select('u.uid', 'u.username')
            ->from(self::FE_USERS_TABLE_NAME, 'u')
            ->where(
                $qb->expr()->eq('u.pid', $qb->createNamedParameter($folderUid, \PDO::PARAM_INT))
            )
            ->execute()
            ->fetchAll();
    }

    public function getUsersByFolderAndMappingId(int $folderUid, int $mappingId): ?array
    {
        $qb = $this->getQueryBuilder(self::FE_USERS_TABLE_NAME);

        return $qb->selectLiteral('u.uid, u.username, m.mapping_username')
            ->from(self::FE_USERS_TABLE_NAME, 'u')
            ->leftJoin('u', UserMappingRepository::USER_MAPPING_TABLE_NAME, 'm', 'u.uid = m.fe_uid and m.mapping_id = ' . $mappingId)
            ->where(
                $qb->expr()->eq('u.pid', $qb->createNamedParameter($folderUid, \PDO::PARAM_INT))
            )
            ->execute()
            ->fetchAll();
    }

    private function getQueryBuilder(string $tablename): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($tablename);
    }
}
