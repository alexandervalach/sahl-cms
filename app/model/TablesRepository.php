<?php

declare(strict_types = 1);

namespace App\Model;

use Nette\Database\Table\ActiveRow;
use Nette\Utils\ArrayHash;
use Nette\Database\IRow;
use Nette\Database\Table\Selection;

/**
 * Class TablesRepository
 * @package App\Model
 */
class TablesRepository extends Repository
{
  /*
  /**
   * @param int|null $seasonId
   * @return Selection
   */
  /*
  public function getForSeason($seasonId = null): Selection
  {
    return $this->getAll()
      ->where(self::SEASON_ID, $seasonId)
      ->where(self::IS_VISIBLE, 1);
  }
  */

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

  /**
   * @param int $tableTypeId
   * @param int $seasonGroupId
   * @return bool|int|ActiveRow
   */
  public function insertData(int $tableTypeId, int $seasonGroupId)
  {
    return $this->insert(array(
      self::TABLE_TYPE_ID => $tableTypeId,
      self::SEASON_GROUP_ID => $seasonGroupId)
    );
  }
}
