<?php

namespace Bitmotion\SingleSignon\Domain\Repository;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2021 Leuchtfeuer Digital Marketing GmbH  <team-yd@typo3.org>
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

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class SessionRepository
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

    private function getQueryBuilder(): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::MAPPING_PROPERTY_TABLE_NAME);
    }
}
