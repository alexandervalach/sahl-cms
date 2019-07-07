<?php

namespace App\Model;

use Nette\Database\Table\Selection;

class SeasonsTeamsRepository extends Repository {

  const SEASON_ID = 'season_id';
  const TEAM_ID = 'team_id';

  protected $tableName = 'seasons_teams';

  public function getTeam($teamId, $seasonId = null)
  {
    return $this->getForSeason($seasonId)->where(self::TEAM_ID, $teamId)->fetch();
  }

  /**
   * @param $seasonId
   * @return Selection
   */
  public function getForSeason($seasonId = null): Selection
  {
    return $this->getAll()->where('season_id', $seasonId);
  }

}
