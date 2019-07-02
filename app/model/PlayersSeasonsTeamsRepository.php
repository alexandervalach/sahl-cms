<?php

namespace App\Model;

class PlayersSeasonsTeamsRepository extends Repository {

  protected $tableName = 'players_seasons_teams';

  public function getAll($seasonId = null) {
    return $this->findAll()->where('season_id', $seasonId);
  }

  public function getTeam($teamId) {
    return $this->findAll()->where('team_id', $team);
  }

}
