<?php

namespace App\Model;

use Nette\Database\Table\Selection;
use Nette\Database\IRow;

class SeasonsGroupsRepository extends Repository
{
  const SEASON_ID = 'season_id';
  const GROUP_ID = 'group_id';

  protected $tableName = 'seasons_groups';

  /**
   * @param int $groupId
   * @param int|null $seasonId
   * @return IRow|null
   */
  public function getGroup(int $groupId, $seasonId = null)
  {
    return $this->getForSeason($seasonId)->where(self::GROUP_ID, $groupId)->fetch();
  }

  /**
   * @param int $groupId
   * @param int|null $seasonId
   * @return IRow|null
   */
  public function getSeasonGroup(int $groupId, $seasonId = null)
  {
    return $this->getForSeason($seasonId)
      ->where(self::GROUP_ID, $groupId)
      ->select(self::ID)->fetch();
  }

  /**
   * @param int|null $seasonId
   * @return Selection
   */
  public function getForSeason($seasonId = null): Selection
  {
    return $this->findAll()->where(self::SEASON_ID, $seasonId);
  }

}
