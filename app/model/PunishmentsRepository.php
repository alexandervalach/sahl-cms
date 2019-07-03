<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\ResultSet;
use Nette\Database\Table\Selection;

class PunishmentsRepository extends Repository {

  /**
   * Finds punishments for specified player
   * @param $id holds the player id
   */
  public function getForPlayer(int $id): Selection
  {
    return $this->getAll()->where('players_seasons_teams_id', $id)->order('id DESC');
  }

  public function getForSeason($id = null): ResultSet
  {
    $con = $this->getConnection();

    if ($id === null) {
      return $con->query('SELECT t.name as team_name, t.logo as team_logo,
        p.id as player_id, p.name as player_name, p.num as player_num,
        pn.content, pn.condition, pn.id, pn.round
        FROM seasons_teams as st
        INNER JOIN players_seasons_teams as pst ON st.id = pst.seasons_teams_id
        INNER JOIN players as p ON p.id = pst.player_id
        INNER JOIN punishments as pn ON pst.id = pn.players_seasons_teams_id
        INNER JOIN teams as t ON t.id = st.team_id
        WHERE st.season_id IS NULL AND p.is_present = ? AND pn.is_present = ?
        ORDER BY pn.id DESC', 1, 1);
    }
  }

}
