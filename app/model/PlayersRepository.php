<?php

namespace App\Model;

class PlayersRepository extends Repository {

  public function getNonEmptyPlayers() {
    return $this->select('id, name, num')->where('num != ?', 0)
      ->order('name')
      ->fetchPairs('id', 'name');
  }

  public function getForTeam($teamId, $seasonId = null) {
    $con = $this->getConnection();
    if ($seasonId === null) {
      return $con->query('SELECT t.name as team_name, t.logo as team_logo, t.id as team_id,
        pt.label as type_label, pt.abbr as type_abbr,
        p.id, p.name, p.num, p.goals, p.trans
        FROM seasons_teams AS st
        INNER JOIN teams AS t
        ON t.id = st.team_id
        INNER JOIN players_seasons_teams AS pst
        ON pst.seasons_teams_id = st.id
        INNER JOIN players AS p
        ON p.id = pst.player_id
        INNER JOIN player_types AS pt
        ON pt.id = p.type_id
        WHERE st.season_id IS NULL AND st.team_id = ?
        AND t.is_present = ?
        AND p.is_present = ?', $teamId, 1, 1);
    }

    return $con->query('SELECT t.name as team_name, t.logo as team_logo, t.id as team_id,
      pt.label as type_label, pt.abbr as type_abbr,
      p.id, p.name, p.num, p.goals, p.trans
      FROM seasons_teams AS st
      INNER JOIN teams AS t
      ON t.id = st.team_id
      INNER JOIN players_seasons_teams AS pst
      ON pst.seasons_teams_id = st.id
      INNER JOIN players AS p
      ON p.id = pst.player_id
      INNER JOIN player_types AS pt
      ON pt.id = p.type_id
      WHERE st.season_id = ? AND st.team_id = ?
      AND t.is_present = ?
      AND p.is_present = ?', $seasonId, $teamId, 1, 1);
  }

  public function getForSeason($seasonId = null) {
    $con = $this->getConnection();

    if ($seasonId === null) {
      return $con->query('SELECT t.name as team_name, t.logo as team_logo, t.id as team_id,
        pt.label as type_label, pt.abbr as type_abbr,
        p.id, p.name, p.num, p.goals, p.trans
        FROM seasons_teams as st
        INNER JOIN players_seasons_teams as pst
        ON pst.seasons_teams_id = st.id
        INNER JOIN players as p
        ON p.id = pst.player_id
        INNER JOIN teams as t
        ON t.id = st.team_id
        INNER JOIN player_types as pt
        ON p.type_id = pt.id
        WHERE season_id IS NULL
        AND p.name != ?
        AND p.name NOT LIKE ?
        AND p.is_present = ?
        AND t.is_present = ?', ' ', '%voľné miesto%', 1, 1);
    }

    return $con->query('SELECT t.name as team_name, t.logo as team_logo,
      pt.label as type_label, pt.abbr as type_abbr,
      p.name, p.num, p.goals, p.trans
      FROM seasons_teams as st
      INNER JOIN players_seasons_teams as pst
      ON pst.seasons_teams_id = st.id
      INNER JOIN players as p
      ON p.id = pst.player_id
      INNER JOIN teams as t
      ON t.id = st.team_id
      INNER JOIN player_types as pt
      ON p.type_id = pt.id
      WHERE season_id = ?
      AND p.name != ?
      AND p.name NOT LIKE ?
      AND p.is_present = ?
      AND t.is_present = ?', $seasonId, ' ', '%voľné miesto%', 1, 1);
  }

}
