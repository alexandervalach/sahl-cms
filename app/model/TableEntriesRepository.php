<?php

namespace App\Model;

use Nette\Database\IRow;
use Nette\Utils\ArrayHash;

class TableEntriesRepository extends Repository
{
  const TABLE_ID = 'table_id';
  const TEAM_ID = 'team_id';

  protected $tableName = 'table_entries';

  /**
   * @param int $tableId
   * @param int $teamId
   * @return IRow|null
   */
  public function getByTableAndTeam(int $teamId, int $tableId)
  {
    return $this->getAll()
      ->where(self::TABLE_ID, $tableId)
      ->where(self::TEAM_ID, $teamId)
      ->order('id DESC')
      ->limit(1)
      ->fetch();
  }

  /**
   * @param int $tableId
   * @param int $teamId
   * @param string $column
   * @param int $value
   */
  public function updateEntry(int $tableId, int $teamId, string $column, int $value = 1): void
  {
    $entry = $this->getByTableAndTeam($teamId, $tableId);
    $entryRow = $this->findById($entry->id);
    $entryRow->update( array($column => $entry[$column] + $value) );
  }

  /**
   * @param int $tableId
   * @param int $teamId
   * @param int $value
   */
  public function updatePoints(int $tableId, int $teamId, int $value = 1): void
  {
    $entry = $this->getByTableAndTeam($teamId, $tableId);
    $entryRow = $this->findById($entry->id);
    $entryRow->update( array(self::POINTS => $entry->points + $value) );
  }

  public function insertData(int $teamId, int $tableId)
  {
    return $this->insert( array(self::TEAM_ID => $teamId, self::TABLE_ID => $tableId) );
  }
}
