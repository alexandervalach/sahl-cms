<?php

namespace App\Model;

class TablesRepository extends Repository {

  public function getTableStats($type) {
    $rows = $this->getNonArchived()->where('type', $type)
      ->order('points DESC')->order('score1 - score2 DESC');
    if (!$rows) {
      return null;
    }
    $result = array();
    foreach ($rows as $row) {
      $result[] = $row;
    }
    return $result;
  }

  public function incTabVal($teamId, $type, $columnName, $value) {
    $conn = $this->getConnection();
    $tableRow = $conn->query("UPDATE `tables` SET `$columnName` = `$columnName` + ?
        WHERE (`team_id` = ?) AND (`archive_id` IS NULL) AND (`type` = ?)", $value, $teamId, $type);
    return $tableRow;
  }

  public function updateFights($teamId, $type) {
    $conn = $this->getConnection();
    $tableRow = $conn->query("UPDATE `tables` SET `counter` = `win` + `tram` + `lost` WHERE (`team_id` = ?) AND (`archive_id` IS NULL) AND (`type` = ?)", $teamId, $type);
    return $tableRow;
  }

}
