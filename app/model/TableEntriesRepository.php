<?php

namespace App\Model;

use InvalidArgumentException;
use Nette\Database\IRow;

class TableEntriesRepository extends Repository
{
  const TABLE_ID = 'table_id';
  const TEAM_ID = 'team_id';

  const WIN = 'win';
  const OVERTIME_WIN = 'overtime_win';
  const DRAW = 'tram';
  const OVERTIME_LOSS = 'overtime_loss';
  const LOSS = 'lost';

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
    $entryRow->update(array($column => $entry[$column] + $value));
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
    $entryRow->update(array(self::POINTS => $entry->points + $value));
  }

  /**
   * Applies or reverses one fight result in the standings.
   *
   * Scoring:
   * - regulation win: 3 points
   * - overtime win: 2 points
   * - overtime loss: 1 point
   * - regulation loss: 0 points
   * - draw: 1 point for each team (kept for backwards compatibility)
   *
   * @param int $tableId
   * @param int $team1Id
   * @param int $team2Id
   * @param int $score1
   * @param int $score2
   * @param bool $isOvertime
   * @param int $direction 1 to apply, -1 to reverse
   */
  public function applyFightResult(
      int $tableId,
      int $team1Id,
      int $team2Id,
      int $score1,
      int $score2,
      bool $isOvertime,
      int $direction = 1
  ): void
  {
    if ($direction !== 1 && $direction !== -1) {
      throw new InvalidArgumentException('Direction must be 1 or -1.');
    }

    if ($isOvertime && $score1 === $score2) {
      throw new InvalidArgumentException('Overtime result cannot end in a draw.');
    }

    // Number of played games and goals are independent of the type of result.
    $this->updateEntry($tableId, $team1Id, 'counter', $direction);
    $this->updateEntry($tableId, $team2Id, 'counter', $direction);
    $this->updateEntry($tableId, $team1Id, 'score1', $score1 * $direction);
    $this->updateEntry($tableId, $team1Id, 'score2', $score2 * $direction);
    $this->updateEntry($tableId, $team2Id, 'score1', $score2 * $direction);
    $this->updateEntry($tableId, $team2Id, 'score2', $score1 * $direction);

    if ($score1 === $score2) {
      $this->updateEntry($tableId, $team1Id, self::DRAW, $direction);
      $this->updateEntry($tableId, $team2Id, self::DRAW, $direction);
      $this->updatePoints($tableId, $team1Id, $direction);
      $this->updatePoints($tableId, $team2Id, $direction);
      return;
    }

    $winnerId = $score1 > $score2 ? $team1Id : $team2Id;
    $loserId = $score1 > $score2 ? $team2Id : $team1Id;

    if ($isOvertime) {
      $this->updateEntry($tableId, $winnerId, self::OVERTIME_WIN, $direction);
      $this->updateEntry($tableId, $loserId, self::OVERTIME_LOSS, $direction);
      $this->updatePoints($tableId, $winnerId, 2 * $direction);
      $this->updatePoints($tableId, $loserId, $direction);
      return;
    }

    $this->updateEntry($tableId, $winnerId, self::WIN, $direction);
    $this->updateEntry($tableId, $loserId, self::LOSS, $direction);
    $this->updatePoints($tableId, $winnerId, 3 * $direction);
  }

  public function insertData(int $teamId, int $tableId)
  {
    return $this->insert(array(self::TEAM_ID => $teamId, self::TABLE_ID => $tableId));
  }
}
