<?php

namespace App\Model;

class PlayersRepository extends Repository {

  public function getNonEmptyPlayers() {
    return $this->select('id, name, num')->where('num != ?', 0)
      ->order('name')
      ->fetchPairs('id', 'name');
  }

  public function getForTeam($teamId) {
    $con = $this->getConnection();
    return $con->query('SELECT py.label as type_label, py.abbr as type_abbr, r.id, r.name, r.num, r.goals, r.trans
    FROM player_types as py
    INNER JOIN (
      SELECT pt.team_id, p.id, p.type_id, p.name, p.num, p.goals, p.trans
      FROM players_teams as pt
      INNER JOIN players as p ON pt.player_id = p.id
      WHERE pt.team_id = ? AND p.is_present = ?
      ) as r
    ON r.type_id = py.id
    ', $teamId, 1);
  }

  public function getForSeason($seasonId = null) {
    $con = $this->getConnection();

    if ($seasonId === null) {
      return $con->query('SELECT t.name as team_name, t.logo as team_logo, r.id, r.team_id, r.name, r.num, r.goals, r.trans, py.label as player_type
        FROM teams as t
        INNER JOIN
        (SELECT pt.team_id, p.id, p.type_id, p.name, p.num, p.goals, p.trans
          FROM players_teams as pt
          INNER JOIN players as p ON pt.player_id = p.id
          WHERE p.name != ? AND p.name NOT LIKE ? AND
          pt.team_id IN
            (SELECT team_id FROM seasons_teams as st
              INNER JOIN teams as t ON st.team_id = t.id
              WHERE st.season_id IS NULL AND t.is_present = ?)
          ORDER BY p.goals DESC, name DESC) as r
        ON r.team_id = t.id
        INNER JOIN player_types as py
        ON py.id = r.type_id', ' ', '%voľné miesto%', 1);
    } else {
      return $con->query('SELECT pt.team_id, p.id, p.type_id, p.name, p.num, p.goals, p.trans
        FROM players_teams as pt
        INNER JOIN players as p ON pt.player_id = p.id
        WHERE p.name != ? AND p.name NOT LIKE ? AND
        pt.team_id IN (SELECT team_id FROM seasons_teams as st
        INNER JOIN teams as t ON st.team_id = t.id
        WHERE st.season_id = ? AND t.is_present = ?)
        ORDER BY p.goals DESC, name DESC', ' ', '%voľné miesto%', $seasonId, 1);
      }
  }

}
