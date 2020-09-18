<?php

namespace App\Model;

use Nette\Database\ResultSet;

class AssistancesRepository extends Repository {

  const PLAYER_ID = 'player_id';

  /*
  public function getPlayerGoalsCount($playerId) {
    return $this->getAll()->where(self::PLAYER_ID, $playerId)->sum('goals');
  }
  */

  /**
   * @param int $fightId
   * @return ResultSet
   */
  public function getForFight(int $fightId): ResultSet
  {
    $db = $this->getConnection();
    return $db->query('SELECT a.id, p.name AS player_name,
      p.number AS player_number, a.number AS assistances, is_home_player
      FROM assistances AS a
      INNER JOIN players_seasons_groups_teams AS psgt
      ON a.player_season_group_team_id = psgt.id
      INNER JOIN players AS p
      ON psgt.player_id = p.id
      WHERE fight_id = ? AND a.is_present = ?
      ORDER BY is_home_player DESC', $fightId, 1);
  }

  /**
   * @param int $fightId
   * @return array
   */
  public function fetchForFight(int $fightId): array
  {
    return $this->getForFight($fightId)->fetchAll();
  }

}
