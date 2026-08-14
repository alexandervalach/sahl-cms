<?php

declare(strict_types = 1);

namespace App\Model;

use InvalidArgumentException;
use Nette\Database\Context;
use RuntimeException;

/**
 * Archives the current season and initializes a fresh current season.
 *
 * In this application the current season is represented by season_id = NULL.
 * Rows moved to a record in seasons become archived; new current rows are then
 * created again with season_id = NULL.
 */
class SeasonArchiveService
{
  private const BASE_TABLE_LABEL = 'Základná časť';
  private const DEFAULT_PLAYER_TYPE_LABEL = 'Hráč';
  private const EMPTY_PLACE_PATTERN = 'voľné miesto%';
  private const NEW_ROSTER_SIZE = 30;

  /** @var Context */
  private $database;

  public function __construct(Context $database)
  {
    $this->database = $database;
  }

  /**
   * Returns current teams together with their division and the number of
   * existing placeholder players. Every selected team will start the new
   * season with exactly NEW_ROSTER_SIZE empty roster places.
   *
   * @return array<int, object>
   */
  public function getCurrentTeams(): array
  {
    return $this->database->query(
      'SELECT
          sgt.id AS season_group_team_id,
          sgt.team_id,
          t.name AS team_name,
          sg.group_id,
          g.label AS group_label,
          COUNT(psgt.id) FILTER (
            WHERE p.name ILIKE ?
              AND psgt.is_present = true
              AND p.is_present = true
          )::int AS empty_places
       FROM seasons_groups AS sg
       INNER JOIN groups AS g
         ON g.id = sg.group_id
        AND g.is_present = true
       INNER JOIN seasons_groups_teams AS sgt
         ON sgt.season_group_id = sg.id
        AND sgt.is_present = true
       INNER JOIN teams AS t
         ON t.id = sgt.team_id
        AND t.is_present = true
       LEFT JOIN players_seasons_groups_teams AS psgt
         ON psgt.season_group_team_id = sgt.id
       LEFT JOIN players AS p
         ON p.id = psgt.player_id
       WHERE sg.season_id IS NULL
         AND sg.is_present = true
       GROUP BY sgt.id, sgt.team_id, t.name, sg.group_id, g.label
       ORDER BY g.label, t.name',
      self::EMPTY_PLACE_PATTERN
    )->fetchAll();
  }

  /**
   * Archive every season-scoped current record and initialize a new current
   * season with the selected teams. Every selected team starts with exactly
   * NEW_ROSTER_SIZE placeholders, regardless of the previous roster size.
   *
   * No real player is carried to the new season. The historical rows remain
   * linked to the newly created archive season.
   *
   * @param string $label Label of the season being archived, e.g. 2025/2026.
   * @param array<int|string> $selectedSeasonGroupTeamIds Current SGT IDs to carry over.
   * @return int ID of the created archive season.
   */
  public function archiveCurrentSeason(string $label, array $selectedSeasonGroupTeamIds): int
  {
    $label = trim($label);
    if ($label === '') {
      throw new InvalidArgumentException('Názov archivovanej sezóny nesmie byť prázdny.');
    }

    $selectedIds = array_values(array_unique(array_filter(
      array_map('intval', $selectedSeasonGroupTeamIds),
      static function (int $id): bool {
        return $id > 0;
      }
    )));

    return $this->database->transaction(function () use ($label, $selectedIds): int {
      $currentSeasonGroups = [];
      $currentGroupIds = [];

      foreach ($this->database->table('seasons_groups')
        ->where('season_id', null)
        ->where('is_present', true) as $seasonGroup) {
        $groupId = (int) $seasonGroup->group_id;

        if (isset($currentGroupIds[$groupId])) {
          throw new RuntimeException('Aktuálna sezóna obsahuje duplicitnú divíziu.');
        }

        $currentSeasonGroups[(int) $seasonGroup->id] = [
          'id' => (int) $seasonGroup->id,
          'group_id' => $groupId,
        ];
        $currentGroupIds[$groupId] = true;
      }

      $currentSeasonGroupIds = array_keys($currentSeasonGroups);
      $currentTeams = [];

      if ($currentSeasonGroupIds) {
        foreach ($this->database->table('seasons_groups_teams')
          ->where('season_group_id', $currentSeasonGroupIds)
          ->where('is_present', true) as $seasonGroupTeam) {
          $seasonGroupTeamId = (int) $seasonGroupTeam->id;
          $currentTeams[$seasonGroupTeamId] = [
            'id' => $seasonGroupTeamId,
            'season_group_id' => (int) $seasonGroupTeam->season_group_id,
            'team_id' => (int) $seasonGroupTeam->team_id,
          ];
        }
      }

      foreach ($selectedIds as $selectedId) {
        if (!isset($currentTeams[$selectedId])) {
          throw new InvalidArgumentException('Bol vybraný tím, ktorý nepatrí do aktuálnej sezóny.');
        }
      }

      $baseTableType = $this->database->table('table_types')
        ->where('label', self::BASE_TABLE_LABEL)
        ->where('is_present', true)
        ->order('id DESC')
        ->fetch();

      if (!$baseTableType) {
        throw new RuntimeException('Chýba typ tabuľky "' . self::BASE_TABLE_LABEL . '".');
      }

      $defaultPlayerType = $this->database->table('player_types')
        ->where('label', self::DEFAULT_PLAYER_TYPE_LABEL)
        ->where('is_present', true)
        ->order('id ASC')
        ->fetch();

      if (!$defaultPlayerType) {
        throw new RuntimeException('Chýba typ hráča "' . self::DEFAULT_PLAYER_TYPE_LABEL . '".');
      }

      $archiveSeason = $this->database->table('seasons')->insert([
        'label' => $label,
        'is_present' => true,
      ]);

      if (!$archiveSeason) {
        throw new RuntimeException('Nepodarilo sa vytvoriť záznam archivovanej sezóny.');
      }

      $archiveSeasonId = (int) $archiveSeason->id;

      // Move all current season-scoped rows to the archive. We deliberately
      // include soft-deleted rows too, otherwise old NULL rows would remain
      // attached to the newly initialized current season.
      $this->database->table('seasons_groups')
        ->where('season_id', null)
        ->update(['season_id' => $archiveSeasonId]);

      $this->database->table('rounds')
        ->where('season_id', null)
        ->update(['season_id' => $archiveSeasonId]);

      $this->database->table('events')
        ->where('season_id', null)
        ->update(['season_id' => $archiveSeasonId]);

      $this->database->table('rules')
        ->where('season_id', null)
        ->update(['season_id' => $archiveSeasonId]);

      // Re-create the active divisions and a fresh base table for each one.
      $newSeasonGroupByOldId = [];
      $newBaseTableByOldSeasonGroupId = [];

      foreach ($currentSeasonGroups as $oldSeasonGroupId => $currentSeasonGroup) {
        $newSeasonGroup = $this->database->table('seasons_groups')->insert([
          'season_id' => null,
          'group_id' => $currentSeasonGroup['group_id'],
          'is_present' => true,
        ]);

        if (!$newSeasonGroup) {
          throw new RuntimeException('Nepodarilo sa vytvoriť divíziu pre novú sezónu.');
        }

        $newSeasonGroupId = (int) $newSeasonGroup->id;
        $newSeasonGroupByOldId[$oldSeasonGroupId] = $newSeasonGroupId;

        $newTable = $this->database->table('tables')->insert([
          'table_type_id' => (int) $baseTableType->id,
          'season_group_id' => $newSeasonGroupId,
          'is_present' => true,
          'is_visible' => true,
        ]);

        if (!$newTable) {
          throw new RuntimeException('Nepodarilo sa vytvoriť tabuľku pre novú sezónu.');
        }

        $newBaseTableByOldSeasonGroupId[$oldSeasonGroupId] = (int) $newTable->id;
      }

      // Carry only the explicitly selected teams into the fresh current season.
      // Their master team records are reused; only the season association is new.
      foreach ($selectedIds as $oldSeasonGroupTeamId) {
        $oldTeam = $currentTeams[$oldSeasonGroupTeamId];
        $oldSeasonGroupId = $oldTeam['season_group_id'];

        if (!isset($newSeasonGroupByOldId[$oldSeasonGroupId])) {
          throw new RuntimeException('Pre vybraný tím neexistuje nová divízia.');
        }

        $newSeasonGroupTeam = $this->database->table('seasons_groups_teams')->insert([
          'season_group_id' => $newSeasonGroupByOldId[$oldSeasonGroupId],
          'team_id' => $oldTeam['team_id'],
          'is_present' => true,
        ]);

        if (!$newSeasonGroupTeam) {
          throw new RuntimeException('Nepodarilo sa preniesť tím do novej sezóny.');
        }

        $newSeasonGroupTeamId = (int) $newSeasonGroupTeam->id;

        $this->database->table('table_entries')->insert([
          'team_id' => $oldTeam['team_id'],
          'table_id' => $newBaseTableByOldSeasonGroupId[$oldSeasonGroupId],
          'counter' => 0,
          'win' => 0,
          'overtime_win' => 0,
          'tram' => 0,
          'overtime_loss' => 0,
          'lost' => 0,
          'score1' => 0,
          'score2' => 0,
          'points' => 0,
          'is_present' => true,
        ]);

        // The new season always starts with exactly 30 empty roster places,
        // regardless of how many players/places the team had in the archived
        // season. We intentionally create independent player rows so filling a
        // placeholder later cannot modify an archived player or another team.
        for ($slot = 1; $slot <= self::NEW_ROSTER_SIZE; $slot++) {
          $placeholder = $this->database->table('players')->insert([
            'name' => 'voľné miesto ' . $slot,
            'number' => 0,
            'born' => null,
            'is_present' => true,
          ]);

          if (!$placeholder) {
            throw new RuntimeException('Nepodarilo sa vytvoriť voľné miesto na súpiske.');
          }

          $this->database->table('players_seasons_groups_teams')->insert([
            'season_group_team_id' => $newSeasonGroupTeamId,
            'player_id' => (int) $placeholder->id,
            'player_type_id' => (int) $defaultPlayerType->id,
            'goals' => 0,
            'assistances' => 0,
            'is_transfer' => false,
            'is_present' => true,
          ]);
        }
      }

      return $archiveSeasonId;
    });
  }
}
