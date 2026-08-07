<?php

declare(strict_types = 1);

namespace App\Model;

use Nette\Database\ResultSet;

class AttendancesRepository extends Repository
{
  protected $tableName = 'attendances';

  /**
   * @param int $fightId
   * @return array
   */
  public function fetchSelectedForFight(int $fightId): array
  {
    return $this->getAll()
      ->where('fight_id', $fightId)
      ->fetchPairs('player_season_group_team_id', 'player_season_group_team_id');
  }

  /**
   * @param int $fightId
   * @return int
   */
  public function countForFight(int $fightId): int
  {
    return $this->getAll()
      ->where('fight_id', $fightId)
      ->count('*');
  }

  /**
   * Returns players that belong to the given team in the given season/group.
   * Players previously saved in this fight are included even if their roster
   * assignment has since been marked as not present, so historical attendance
   * is not accidentally lost when editing an older result.
   *
   * @param int $teamId
   * @param int $seasonGroupId
   * @param int $fightId
   * @return ResultSet
   */
  public function getPlayersForFightTeam(int $teamId, int $seasonGroupId, int $fightId): ResultSet
  {
    $db = $this->getConnection();

    return $db->query('SELECT psgt.id, p.name, p.number
      FROM seasons_groups_teams AS sgt
      INNER JOIN players_seasons_groups_teams AS psgt
        ON psgt.season_group_team_id = sgt.id
      INNER JOIN players AS p
        ON p.id = psgt.player_id
      WHERE sgt.season_group_id = ?
        AND sgt.team_id = ?
        AND p.name NOT LIKE ?
        AND (
          (sgt.is_present = ? AND psgt.is_present = ?)
          OR EXISTS (
            SELECT 1
            FROM attendances AS a
            WHERE a.fight_id = ?
              AND a.player_season_group_team_id = psgt.id
              AND a.is_present = ?
          )
        )
      ORDER BY p.name, p.number', $seasonGroupId, $teamId, 'voľné miesto%', 1, 1, $fightId, 1);
  }

  /**
   * @param int $teamId
   * @param int $seasonGroupId
   * @param int $fightId
   * @return array
   */
  public function fetchPlayersForFightTeam(int $teamId, int $seasonGroupId, int $fightId): array
  {
    $rows = $this->getPlayersForFightTeam($teamId, $seasonGroupId, $fightId)->fetchAll();
    $players = [];

    foreach ($rows as $row) {
      $players[(int) $row->id] = $row->name . ' (' . $row->number . ')';
    }

    return $players;
  }

  /**
   * Replaces attendance for one fight. Empty array intentionally means that
   * nobody was marked as present.
   *
   * @param int $fightId
   * @param array $playerSeasonGroupTeamIds
   */
  public function saveForFight(int $fightId, array $playerSeasonGroupTeamIds): void
  {
    $db = $this->getConnection();

    $db->transaction(function () use ($db, $fightId, $playerSeasonGroupTeamIds): void {
      $db->query('UPDATE attendances SET is_present = ? WHERE fight_id = ?', 0, $fightId);

      foreach ($playerSeasonGroupTeamIds as $playerSeasonGroupTeamId) {
        $db->query('INSERT INTO attendances
          (fight_id, player_season_group_team_id, is_present)
          VALUES (?, ?, ?)
          ON CONFLICT (fight_id, player_season_group_team_id)
          DO UPDATE SET is_present = EXCLUDED.is_present',
          $fightId, (int) $playerSeasonGroupTeamId, 1);
      }

      $db->query('UPDATE fights SET attendance_recorded = ? WHERE id = ?', 1, $fightId);
    });
  }
}
