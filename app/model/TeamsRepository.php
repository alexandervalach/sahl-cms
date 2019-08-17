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
   * @param int $id
   * @return array
   */
  public function getPlayers(int $id): array
  {
    return ($this->findById($id))->related('players')->fetchPairs(self::ID, self::NAME);
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

  /*
  public function getForSeason($seasonId = null): ResultSet
  {
    $con = $this->getConnection();

    $query = 'SELECT t.id, t.name, t.logo FROM seasons_teams as st
      INNER JOIN teams as t ON st.team_id = t.id ';

    return $seasonId === null ? $con->query($query .
        'WHERE st.season_id IS NULL AND t.is_present = ? ORDER BY name', 1) : $con->query($query .
        'WHERE st.season_id = ? AND t.is_present = ? ORDER BY name', $seasonId, 1);
  }
  */

  /**
   * @param int $playerId
   * @return IRow|null
   */
  public function getForPlayer(int $playerId)
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
}
