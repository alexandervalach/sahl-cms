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
  public function getEntry(int $tableId, int $teamId)
  {
    return $this->getAll()
      ->where(self::TABLE_ID, $tableId)
      ->where(self::TEAM_ID, $teamId)
      ->select(self::ID)
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
    $entry = $this->getEntry();
    $entryRow = $this->findById($entry->id);
    $entryRow->update( array($column => $entry[$column] + $value) );
  }

  /**
   * @param int $tableId
   * @param int $teamId
   * @param int $value
   */
  public function updateEntryPoints(int $tableId, int $teamId, int $value = 1): void
  {
    $entry = $this->getEntry();
    $entryRow = $this->findById($entry->id);
    $entryRow->update( array($column => $entry[$column] + $value) );
  }
}
