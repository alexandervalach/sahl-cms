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
        WHERE sgt.season_group_id = ? AND sgt.team_id = ? AND sgt.is_present = ? ORDER BY type_priority', $seasonGroupId, $teamId, 1);
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

    $query = 'SELECT t.name as team_name, t.logo as team_logo, t.id as team_id,
      pt.label as type_label, pt.abbr as type_abbr,
      p.id, p.name, p.number, psgt.goals, psgt.is_transfer, psgt.id as player_season_group_team_id
      FROM seasons_groups_teams as sgt
      INNER JOIN players_seasons_groups_teams as psgt
      ON psgt.season_group_team_id = sgt.id
      INNER JOIN players as p
      ON p.id = psgt.player_id
      INNER JOIN teams as t
      ON t.id = sgt.team_id
      INNER JOIN player_types as pt
      ON psgt.player_type_id = pt.id 
      WHERE p.name != ?
      AND p.name NOT LIKE ?
      AND p.is_present = ?
      AND t.is_present = ? 
      AND season_group_id = ? 
      ORDER BY name';

    return $db->query($query, ' ', 'voľné miesto%', 1, 1, $seasonGroupId);
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
    return $db->query('SELECT player_id AS id, 
       is_transfer, psgt.is_present, name, number, 
       label AS type_label, abbr AS type_abbr
      FROM players_seasons_groups_teams AS psgt
      INNER JOIN players AS p
      ON psgt.player_id = p.id
      INNER JOIN player_types AS pt
      ON pt.id = psgt.player_type_id
      WHERE psgt.player_id = ? AND psgt.is_present = ?
      ORDER BY psgt.id DESC 
      LIMIT ?', $playerId, 1, 1)->fetch();
  }

}
