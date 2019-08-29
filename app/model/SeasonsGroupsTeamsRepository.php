<?php

namespace App\Model;

use Nette\Database\Table\Selection;
use Nette\Database\IRow;
use Nette\Database\ResultSet;

class SeasonsGroupsTeamsRepository extends Repository
{
  const SEASON_ID = 'season_id';
  const TEAM_ID = 'team_id';
  const NAME = 'name';

  protected $tableName = 'seasons_groups_teams';

  /**
   * @param int $teamId
   * @param int $seasonGroupId
   * @return IRow|null
   */
  public function getByTeam(int $teamId, int $seasonGroupId)
  {
    return $this->getForSeason($seasonGroupId)->where(self::TEAM_ID, $teamId)->fetch();
  }

  /**
   * @param int $teamId
   * @param int $seasonGroupId
   * @return IRow|null
   */
  public function getTeam(int $teamId, int $seasonGroupId)
  {
    return $this->getForSeason($seasonGroupId)
      ->where(self::TEAM_ID, $teamId)
      ->select(self::ID)->fetch();
  }

  /**
   * @param int $teamId
   * @return IRow|null
   */
  public function getSeasonGroupForTeam(int $teamId)
  {
    return $this->findAll()
        ->where(self::TEAM_ID, $teamId)
        ->order('id DESC')
        ->limit(1)
        ->fetch();
  }

  /**
   * @param int $seasonGroupId
   * @return Selection
   */
  public function getForSeason(int $seasonGroupId): Selection
  {
    return $this->findAll()->where('season_group_id', $seasonGroupId);
  }

}
