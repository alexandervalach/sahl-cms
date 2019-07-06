<?php

declare(strict_types = 1);

namespace App\Model;

use Nette\Database\ResultSet;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Row;

class TeamsRepository extends Repository
{
  const NAME = 'name';

  /**
   * Loop trough all teams and store them in array.
   * Accessible index $team->id.
   * Value is $team->name.
   * @return array
   */
  public function getTeams(): array
  {
    return $this->getAll()->order(self::NAME)->fetchPairs(self::ID, self::NAME);
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
   * Get teams for selected season
   * @param int|null $seasonId
   * @return ResultSet
   */
  public function getForSeason($seasonId = null): ResultSet
  {
    $con = $this->getConnection();

    $query = 'SELECT t.id, t.name, t.logo FROM seasons_teams as st
      INNER JOIN teams as t ON st.team_id = t.id ';

    if ($seasonId === null) {
      return $con->query($query .
        'WHERE st.season_id IS NULL AND t.is_present = ? ORDER BY name', 1);
    } else {
      return $con->query($query .
        'WHERE st.season_id = ? AND t.is_present = ? ORDER BY name', $seasonId, 1);
    }
  }

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
