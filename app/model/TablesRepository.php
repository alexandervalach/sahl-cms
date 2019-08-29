<?php

declare(strict_types = 1);

namespace App\Model;

use Nette\Utils\ArrayHash;
use Nette\Database\IRow;
use Nette\Database\Table\Selection;

class TablesRepository extends Repository
{
  const TABLE_TYPE_ID = 'table_type_id';
  const SEASON_GROUP_ID = 'season_group_id';

  /**
   * @param int|null $seasonId
   * @return Selection
   */
  public function getForSeason($seasonId = null): Selection
  {
    return $this->getAll()
      ->where(self::SEASON_ID, $seasonId)
      ->where(self::IS_VISIBLE, 1);
  }

  /**
   * @param int $tableTypeId
   * @param int $seasonGroupId
   * @return IRow|null
   */
  public function getByType(int $tableTypeId, int $seasonGroupId)
  {
    return $this->findByValue('table_type_id', $tableTypeId)
        ->where('season_group_id', $seasonGroupId)->fetch();
  }

  /**
   * @param int $tableTypeId
   * @param int $seasonGroupId
   * @return IRow|null
   */
  public function getByTableTypeId(int $tableTypeId, int $seasonGroupId)
  {
    return $this->getAll()
      ->where(self::TABLE_TYPE_ID, $tableTypeId)
      ->where(self::SEASON_GROUP_ID, $seasonGroupId)
      ->select(self::ID)
      ->fetch();
  }
}
