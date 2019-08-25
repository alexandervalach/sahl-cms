<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\ResultSet;
use Nette\Database\Table\Selection;

class PunishmentsRepository extends Repository {

  /**
   * Finds punishments for specified player
   * @param int $id holds the player id
   * @return Selection
   */
  public function getForPlayer(int $id): Selection
  {
    return $this->getAll()->where('players_seasons_teams_id', $id)->order('id DESC');
  }

  public function getForSeasonGroup(int $seasonGroupId): ResultSet
  {
    $con = $this->getConnection();

    $query = 'SELECT pn.id AS id, pn.content, pn.round, pn.condition, 
       p.number AS player_number, p.name AS player_name, 
       t.name AS team_name, t.logo AS team_logo
      FROM punishments AS pn
      INNER JOIN players_seasons_groups_teams AS psgt
      ON pn.player_season_group_team_id = psgt.id
      INNER JOIN seasons_groups_teams AS sgt
      ON psgt.season_group_team_id = sgt.id
      INNER JOIN seasons_groups AS sg
      ON sgt.season_group_id = sg.id
      INNER JOIN players AS p
      ON psgt.player_id = p.id
      INNER JOIN teams AS t
      ON sgt.team_id = t.id
      WHERE sgt.season_group_id = ?';

      return $con->query($query, $seasonGroupId);
  }

}
