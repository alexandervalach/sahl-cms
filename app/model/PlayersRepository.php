<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\ResultSet;
use Nette\Database\IRow;

class PlayersRepository extends Repository
{
  const NUMBER = 'number';
  const NAME = 'name';

  public function getNonEmptyPlayers(): array
  {
    return $this->select('id, name, number')->where('number != ?', 0)
      ->order('name')
      ->fetchPairs('id', 'name');
  }

  /**
   * @param int $teamId
   * @param int $seasonGroupId
   * @return ResultSet
   */
  public function getForTeam(int $teamId, int $seasonGroupId): ResultSet
  {
    $db = $this->getConnection();
    return $db->query('SELECT t.name as team_name, t.logo as team_logo, t.id as team_id,
        pt.label as type_label, pt.abbr as type_abbr, pt.priority as type_priority,
        p.id, p.name, p.number,
        psgt.goals, psgt.is_transfer FROM seasons_groups_teams AS sgt
          INNER JOIN teams AS t ON t.id = sgt.team_id
          INNER JOIN players_seasons_groups_teams AS psgt ON psgt.season_group_team_id = sgt.id
          INNER JOIN players AS p ON p.id = psgt.player_id
          INNER JOIN player_types AS pt ON pt.id = psgt.player_type_id
        WHERE sgt.season_group_id = ?
          AND sgt.team_id = ?
          AND sgt.is_present = ?
          AND psgt.is_present = ?
        ORDER BY type_priority', $seasonGroupId, $teamId, 1, 1);
  }

  /**
   * @param int $teamId
   * @param int $seasonGroupId
   * @return array
   */
  public function fetchForTeam(int $teamId, int $seasonGroupId): array
  {
    return $this->getForTeam($teamId, $seasonGroupId)->fetchAll();
  }

  /**
   * @param int $seasonGroupId
   * @return ResultSet
   */
  public function getForSeasonGroup(int $seasonGroupId): ResultSet
  {
    $db = $this->getConnection();

    $query = "SELECT t.name as team_name, t.logo as team_logo, t.id as team_id,
      pt.label as type_label, pt.abbr as type_abbr,
      p.id, p.name, p.number, psgt.goals, psgt.assistances, psgt.is_transfer,
      psgt.id as player_season_group_team_id,
      COALESCE(attendance_stats.games_played, 0)::int AS games_played,
      COALESCE(attendance_stats.regular_season_games_played, 0)::int AS regular_season_games_played,
      COALESCE(team_stats.regular_season_games, 0)::int AS regular_season_games,
      COALESCE(team_stats.recorded_regular_season_games, 0)::int AS recorded_regular_season_games,
      CASE
        WHEN COALESCE(team_stats.regular_season_games, 0) > 0
          THEN ROUND(COALESCE(attendance_stats.regular_season_games_played, 0) * 100.0
            / team_stats.regular_season_games, 1)
        ELSE 0.0
      END AS regular_season_attendance_percent,
      CASE
        WHEN COALESCE(team_stats.regular_season_games, 0) > 0
          THEN COALESCE(attendance_stats.regular_season_games_played, 0) * 4
            >= team_stats.regular_season_games
        ELSE false
      END AS has_25_percent,
      COALESCE(team_stats.recorded_regular_season_games, 0)
        = COALESCE(team_stats.regular_season_games, 0) AS attendance_complete
      FROM seasons_groups_teams as sgt
      INNER JOIN players_seasons_groups_teams as psgt
        ON psgt.season_group_team_id = sgt.id
      INNER JOIN players as p
        ON p.id = psgt.player_id
      INNER JOIN teams as t
        ON t.id = sgt.team_id
      INNER JOIN player_types as pt
        ON psgt.player_type_id = pt.id
      LEFT JOIN LATERAL (
        SELECT
          COUNT(DISTINCT a.fight_id) AS games_played,
          COUNT(DISTINCT a.fight_id) FILTER (WHERE tt.label = 'Základná časť')
            AS regular_season_games_played
        FROM attendances AS a
        INNER JOIN fights AS f
          ON f.id = a.fight_id AND f.is_present = true
        INNER JOIN tables AS tb
          ON tb.id = f.table_id AND tb.is_present = true
        INNER JOIN table_types AS tt
          ON tt.id = tb.table_type_id AND tt.is_present = true
        WHERE a.player_season_group_team_id = psgt.id
          AND a.is_present = true
      ) AS attendance_stats ON true
      LEFT JOIN LATERAL (
        SELECT
          COUNT(*) AS regular_season_games,
          COUNT(*) FILTER (WHERE f.attendance_recorded = true)
            AS recorded_regular_season_games
        FROM fights AS f
        INNER JOIN tables AS tb
          ON tb.id = f.table_id AND tb.is_present = true
        INNER JOIN table_types AS tt
          ON tt.id = tb.table_type_id AND tt.is_present = true
        WHERE tb.season_group_id = sgt.season_group_id
          AND tt.label = 'Základná časť'
          AND f.is_present = true
          AND (f.team1_id = sgt.team_id OR f.team2_id = sgt.team_id)
      ) AS team_stats ON true
      WHERE p.name != ?
      AND p.name NOT LIKE ?
      AND psgt.is_present = ?
      AND sgt.is_present = ?
      AND sgt.season_group_id = ?
      ORDER BY (psgt.goals + psgt.assistances) DESC,
        psgt.goals DESC, psgt.assistances DESC, psgt.id DESC";

    return $db->query($query, ' ', 'voľné miesto%', 1, 1, $seasonGroupId);
  }

  /**
   * Player statistics for a complete archived season across all divisions.
   *
   * @param int $seasonId
   * @return ResultSet
   */
  public function getForArchivedSeason(int $seasonId): ResultSet
  {
    $db = $this->getConnection();

    return $db->query("SELECT
        g.label AS group_label,
        t.id AS team_id,
        t.name AS team_name,
        t.logo AS team_logo,
        p.id AS id,
        p.name,
        p.number,
        pt.label AS type_label,
        pt.abbr AS type_abbr,
        psgt.goals,
        psgt.assistances,
        psgt.is_transfer,
        COALESCE(attendance_stats.games_played, 0)::int AS games_played
      FROM seasons_groups AS sg
      INNER JOIN groups AS g
        ON g.id = sg.group_id
      INNER JOIN seasons_groups_teams AS sgt
        ON sgt.season_group_id = sg.id
       AND sgt.is_present = true
      INNER JOIN teams AS t
        ON t.id = sgt.team_id
      INNER JOIN players_seasons_groups_teams AS psgt
        ON psgt.season_group_team_id = sgt.id
       AND psgt.is_present = true
      INNER JOIN players AS p
        ON p.id = psgt.player_id
      INNER JOIN player_types AS pt
        ON pt.id = psgt.player_type_id
      LEFT JOIN LATERAL (
        SELECT COUNT(DISTINCT a.fight_id) AS games_played
        FROM attendances AS a
        INNER JOIN fights AS f
          ON f.id = a.fight_id
         AND f.is_present = true
        WHERE a.player_season_group_team_id = psgt.id
          AND a.is_present = true
      ) AS attendance_stats ON true
      WHERE sg.season_id = ?
        AND sg.is_present = true
        AND p.name != ?
        AND p.name NOT LIKE ?
      ORDER BY (psgt.goals + psgt.assistances) DESC,
        psgt.goals DESC,
        psgt.assistances DESC,
        p.name", $seasonId, ' ', 'voľné miesto%');
  }

  /**
   * @param int $seasonGroupId
   * @return array
   */
  public function fetchForSeasonGroup (int $seasonGroupId): array
  {
    return $this->getForSeasonGroup($seasonGroupId)->fetchPairs('player_season_group_team_id', self::NAME);
  }

  /**
   * @param string $name
   * @param int $number
   * @return IRow|null
   */
  public function getPlayer(string $name, int $number)
  {
    return $this->findByValue(self::NAME, $name)
      ->where(self::NUMBER, $number)
      ->select(self::ID)->fetch();
  }

  /**
   * @param int $playerId
   * @return IRow|null
   */
  public function getTeam(int $playerId)
  {
    $con = $this->getConnection();
    return $con->query('SELECT st.team_id as team_id, t.name as team_name, t.logo as team_logo,
      pt.label as type_label, pt.abbr as type_abbr,
      pst.goals, pst.is_transfer, p.photo,
      g.label as group_label
      FROM seasons_teams AS st
      INNER JOIN teams AS t ON st.team_id = t.id
      INNER JOIN players_seasons_teams AS pst ON pst.seasons_teams_id = st.id
      INNER JOIN player_types AS pt ON pst.player_type_id = pt.id
      INNER JOIN players AS p ON pst.player_id = p.id
      INNER JOIN groups AS g ON st.group_id = g.id
      WHERE st.season_id IS NULL AND pst.player_id = ?', $playerId)->fetch();
  }

  /**
   * @param int $playerId
   * @return IRow|null
   */
  public function getPlayerInfo(int $playerId)
  {
    $db = $this->getConnection();
    return $db->query("SELECT player_id AS id,
       psgt.is_transfer, psgt.is_present, p.name, p.number,
       psgt.player_type_id, psgt.goals, psgt.assistances,
       pt.label AS type_label, pt.abbr AS type_abbr,
       COALESCE(attendance_stats.games_played, 0)::int AS games_played,
       COALESCE(attendance_stats.regular_season_games_played, 0)::int AS regular_season_games_played,
       COALESCE(team_stats.regular_season_games, 0)::int AS regular_season_games,
       COALESCE(team_stats.recorded_regular_season_games, 0)::int AS recorded_regular_season_games,
       CASE
         WHEN COALESCE(team_stats.regular_season_games, 0) > 0
           THEN ROUND(COALESCE(attendance_stats.regular_season_games_played, 0) * 100.0
             / team_stats.regular_season_games, 1)
         ELSE 0.0
       END AS regular_season_attendance_percent,
       CASE
         WHEN COALESCE(team_stats.regular_season_games, 0) > 0
           THEN COALESCE(attendance_stats.regular_season_games_played, 0) * 4
             >= team_stats.regular_season_games
         ELSE false
       END AS has_25_percent,
       COALESCE(team_stats.recorded_regular_season_games, 0)
         = COALESCE(team_stats.regular_season_games, 0) AS attendance_complete
      FROM players_seasons_groups_teams AS psgt
      INNER JOIN players AS p
        ON psgt.player_id = p.id
      INNER JOIN player_types AS pt
        ON pt.id = psgt.player_type_id
      INNER JOIN seasons_groups_teams AS sgt
        ON sgt.id = psgt.season_group_team_id
      LEFT JOIN LATERAL (
        SELECT
          COUNT(DISTINCT a.fight_id) AS games_played,
          COUNT(DISTINCT a.fight_id) FILTER (WHERE tt.label = 'Základná časť')
            AS regular_season_games_played
        FROM attendances AS a
        INNER JOIN fights AS f
          ON f.id = a.fight_id AND f.is_present = true
        INNER JOIN tables AS tb
          ON tb.id = f.table_id AND tb.is_present = true
        INNER JOIN table_types AS tt
          ON tt.id = tb.table_type_id AND tt.is_present = true
        WHERE a.player_season_group_team_id = psgt.id
          AND a.is_present = true
      ) AS attendance_stats ON true
      LEFT JOIN LATERAL (
        SELECT
          COUNT(*) AS regular_season_games,
          COUNT(*) FILTER (WHERE f.attendance_recorded = true)
            AS recorded_regular_season_games
        FROM fights AS f
        INNER JOIN tables AS tb
          ON tb.id = f.table_id AND tb.is_present = true
        INNER JOIN table_types AS tt
          ON tt.id = tb.table_type_id AND tt.is_present = true
        WHERE tb.season_group_id = sgt.season_group_id
          AND tt.label = 'Základná časť'
          AND f.is_present = true
          AND (f.team1_id = sgt.team_id OR f.team2_id = sgt.team_id)
      ) AS team_stats ON true
      WHERE psgt.player_id = ? AND psgt.is_present = ?
      ORDER BY psgt.id DESC
      LIMIT ?", $playerId, 1, 1)->fetch();
  }

}
