<?php

declare(strict_types = 1);

namespace App\Model;

use Nette\Database\ResultSet;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Database\IRow;

class TeamsRepository extends Repository
{
  const NAME = 'name';

  /**
   * @param int|null $seasonId
   * @return ResultSet
   */
  protected function getForSeason($seasonId = null): ResultSet
  {
    $db = $this->getConnection();
    $query = "SELECT sgt.id as season_group_id, g.id AS group_id, t.id AS id, t.name, t.logo, t.photo, g.label as group_label
      FROM seasons_groups AS sg
      INNER JOIN seasons_groups_teams AS sgt
      ON sgt.season_group_id = sg.id
      INNER JOIN teams as t
      ON t.id = sgt.team_id
      INNER JOIN groups as g
      ON g.id = sg.group_id
      WHERE sgt.is_present = ? ";
    return $seasonId === null ? $db->query($query . "AND sg.season_id IS NULL", 1) :
        $db->query($query . "AND sg.season_id = ?", 1, $seasonId);
  }

  /**
   * @return array
   */
  public function fetchForSeason(): array
  {
    return ($this->getForSeason(null))->fetchPairs(self::ID, self::NAME);
  }

  /**
   * Loop trough all teams and store them in array.
   * Accessible index $team->id.
   * Value is $team->name.
   * @return array
   */
  public function getTeams(): array
  {
    return $this->findAll()->order(self::NAME)->fetchPairs(self::ID, self::NAME);
  }

  /**
   * @param int $team1Id
   * @param int $team2Id
   * @param array $seasonGroupTeamIds
   * @return ResultSet
   */
  public function getPlayersForTeams(int $team1Id, int $team2Id, array $seasonGroupTeamIds): ResultSet
  {
    $db = $this->getConnection();
    return $db->query('SELECT psgt.id, p.name, p.number
      FROM teams AS t
      INNER JOIN seasons_groups_teams AS sgt
      ON sgt.team_id = t.id
      INNER JOIN players_seasons_groups_teams AS psgt
      ON psgt.season_group_team_id = sgt.id
      INNER JOIN players AS p
      ON psgt.player_id = p.id
      WHERE p.name NOT LIKE ? AND
      (t.id = ? OR t.id = ?) AND
      psgt.season_group_team_id IN (?) AND
      psgt.is_present = ?', 'voľné miesto %', $team1Id, $team2Id, $seasonGroupTeamIds, 1);
  }

  /**
   * @param int $team1Id
   * @param int $team2Id
   * @param Selection $seasonGroups
   * @return array
   */
  public function fetchPlayersForTeams(int $team1Id, int $team2Id, Selection $seasonGroups): array
  {
    $db = $this->getConnection();

    $seasonGroupTeamIds = $db->query('SELECT id FROM seasons_groups_teams AS sgt
      WHERE sgt.season_group_id IN (?) AND
      (sgt.team_id = ? OR sgt.team_id = ?) AND
      sgt.is_present = 1', $seasonGroups, $team1Id, $team2Id)->fetchFields();

    return $this->getPlayersForTeams($team1Id, $team2Id, $seasonGroupTeamIds)->fetchPairs('id', 'name');
  }

  /**
   * Returns single row with given name
   * @param string $name
   * @return IRow|null
   */
  public function findByName(string $name)
  {
    return $this->findAll()->where(self::NAME, $name)->fetch();
  }

  /**
   * @param int $seasonGroupId
   * @return ResultSet
   */
  public function getForSeasonGroup(int $seasonGroupId): ResultSet
  {
    $db = $this->getConnection();
    $query = 'SELECT t.id AS id, photo, logo, name
      FROM seasons_groups_teams AS sgt
      INNER JOIN teams AS t
      ON sgt.team_id = t.id
      WHERE sgt.is_present = ?';

    return $db->query($query . ' AND sgt.season_group_id = ? ORDER BY name', 1, $seasonGroupId);
  }

  /**
   * Fetches a team for a group in the current season
   * @param int $seasonGroup
   * @return array
   */
  public function fetchForSeasonGroup (int $seasonGroup): array
  {
    return $this->getForSeasonGroup($seasonGroup)->fetchAll();
  }

  public function insertData (string $name)
  {
    return $this->insert( array(self::NAME => $name) );
  }

}
