<?php

namespace App\Model;

use Nette\Database\ResultSet;

class SeasonsTeamsRepository extends Repository {

  const SEASON_ID = 'season_id';

  protected $tableName = 'seasons_teams';

  /**
   * Get teams for selected season
   * @return ResultSet
   */
  public function getTeams($seasonId = null) {
    $teams = $this->findAll()->where(self::SEASON_ID, $seasonId);

    $con = $this->getConnection();
    return $con->query('SELECT t.id, t.name, t.logo FROM seasons_teams as st
      INNER JOIN teams as t ON st.team_id = t.id
      WHERE st.season_id = ? AND t.is_present = ?', $seasonId, 1);
  }

}
