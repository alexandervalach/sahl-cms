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

  /**
   * @param int $playerId
   * @return mixed
   */
  public function findByPlayer(int $playerId)
  {
    return $this->findAll()->where('player_id', $playerId)->order('id DESC')->limit(1)->fetch();
  }

  /**
   * @param int $seasonGroupTeamId
   * @return Selection
   */
  public function findBySeasonGroupTeam(int $seasonGroupTeamId): Selection
  {
    return $this->getAll()->where('season_group_team_id', $seasonGroupTeamId);
  }

}
