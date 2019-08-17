<?php

namespace App\Model;

use Nette\Database\Table\Selection;
use Nette\Database\IRow;

class SeasonsGroupsTeamsRepository extends Repository
{
  const SEASON_ID = 'season_id';
  const TEAM_ID = 'team_id';

  protected $tableName = 'seasons_groups_teams';

  /**
   * @param int $teamId
   * @param int|null $seasonId
   */
  public function getTeam(int $teamId, $seasonId = null)
  {
    return $this->getForSeason($seasonId)->where(self::TEAM_ID, $teamId)->fetch();
  }

  /**
   * @param int $teamId
   * @param int|null $seasonId
   * @return IRow|null
   */
  public function getSeasonTeam(int $teamId, $seasonId = null)
  {
    return $this->getForSeason($seasonId)
      ->where(self::TEAM_ID, $teamId)
      ->select(self::ID)->fetch();
  }

  /**
   * @param int|null $seasonId
   * @return Selection
   */
  public function getForSeason($seasonId = null): Selection
  {
    return $this->getAll()->where('season_group_id', $seasonId);
  }

}
