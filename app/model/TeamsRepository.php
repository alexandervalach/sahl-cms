<?php

namespace App\Model;

class TeamsRepository extends Repository {

  /**
   * Loop trough all teams and store them in array.
   * Accessible index $team->id.
   * Value is $team->name.
   * @return array
   */
  public function getTeams() {
    return $this->getAll()->order('name')->fetchPairs('id', 'name');
  }

  public function getPlayers($teamId) {
    $team = $this->findById($teamId);
    return $team->related('players')->fetchPairs('id', 'name');
  }

  /**
   * Get teams for selected season
   * @return ResultSet
   */
  public function getForSeason($seasonId = null) {
    $con = $this->getConnection();
    if ($seasonId === null) {
      return $con->query('SELECT t.id, t.name, t.logo FROM seasons_teams as st
        INNER JOIN teams as t ON st.team_id = t.id
        WHERE st.season_id IS NULL AND t.is_present = ?', 1);
    } else {
      return $con->query('SELECT t.id, t.name, t.logo FROM seasons_teams as st
        INNER JOIN teams as t ON st.team_id = t.id
        WHERE st.season_id = ? AND t.is_present = ?', $seasonId, 1);
    }
  }

  /**
   * Get teams for selected season
   * @return ActiveRow
   */
  public function getForPlayer($playerRow) {
    $seasonTeam = $playerRow->ref('players_seasons_teams', 'players_seasons_teams_id');
    return $this->table($tableName)->get($seasonTeam->team_id);
  }

}
