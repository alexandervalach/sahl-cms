<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Table\Selection;

class PlayersSeasonsGroupsTeamsRepository extends Repository {

  protected $tableName = 'players_seasons_groups_teams';

  public function getAll(): Selection
  {
    return $this->findAll();
  }

  public function getForSeason($id = null): Selection
  {
    return $this->findAll()->where(self::SEASON_ID, $id);
  }

  public function getTeam(int $id): Selection
  {
    return $this->findAll()->where('team_id', $id);
  }

  public function getPlayers(int $id): Selection
  {
    return $this->getAll()->where('seasons_teams_id', $id);
  }

}
