<?php

declare(strict_types = 1);

namespace App\Model;

use Nette\Database\Table\Selection;

class TablesRepository extends Repository
{
  public function getForSeason($seasonId = null): Selection
  {
    return $this->getAll()->where(self::SEASON_ID, $seasonId)->where(self::IS_VISIBLE, 1);
  }

  public function getTableStats($type)
  {
    $rows = $this->getArchived()->where('type', $type)->order('points DESC')->order('score1 - score2 DESC');

    if (!$rows) {
      return null;
    }

    $result = array();

    foreach ($rows as $row) {
      $result[] = $row;
    }

    return $result;
  }

  public function incTabVal($teamId, $type, $columnName, $value)
  {
    $conn = $this->getConnection();
    $tableRow = $conn->query("UPDATE tables SET ? = ? + ?
      WHERE (team_id = ?) AND (season_id IS NULL) AND (type = ?)",
      $columnName, $columnName, $value, $teamId, $type);
    return $tableRow;
  }

  public function updateFights($teamId, $type)
  {
    $conn = $this->getConnection();
    $tableRow = $conn->query("UPDATE `tables` SET `counter` = `win` + `tram` + `lost` WHERE (`team_id` = ?) AND (`archive_id` IS NULL) AND (`type` = ?)", $teamId, $type);
    return $tableRow;
  }

}
