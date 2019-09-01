<?php

namespace App\Model;

use Nette\Database\ResultSet;

class GoalsRepository extends Repository {

  const PLAYER_ID = 'player_id';

  public function getPlayerGoalsCount($playerId) {
    return $this->getAll()->where(self::PLAYER_ID, $playerId)->sum('goals');
  }

  public function getForFight(int $fightId): ResultSet
  {
    $db = $this->getConnection();
    return $db->query('SELECT g.id, p.name AS player_name, 
      p.number AS player_number, g.number AS goals, is_home_player 
      FROM goals AS g
      INNER JOIN players_seasons_groups_teams AS psgt
      ON g.player_season_group_team_id = psgt.id
      INNER JOIN players AS p
      ON psgt.player_id = p.id
      WHERE fight_id = ? AND g.is_present = ?
      ORDER BY is_home_player DESC', $fightId, 1);
  }

}
