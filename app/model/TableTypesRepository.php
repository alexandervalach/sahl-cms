<?php

declare(strict_types = 1);

namespace App\Model;

use Nette\Database\IRow;
use Nette\Database\ResultSet;
use Nette\Database\Table\ActiveRow;

/**
 * Class TableTypesRepository
 * @package App\Model
 */
class TableTypesRepository extends Repository
{
  /**
   * @var string
   */
  protected $tableName = 'table_types';

  /**
   * @return array
   */
  public function getTableTypes(): array
  {
    return $this->getAll()->fetchPairs(self::ID, self::LABEL);
  }

  /**
   * @param null $seasonId
   * @return ResultSet
   */
  public function getForSeason($seasonId = null): ResultSet
  {
    $db = $this->getConnection();
    $query = 'SELECT t.id as id, table_type_id, group_id, label, is_visible FROM seasons_groups AS sg
      INNER JOIN tables AS t
      ON t.season_group_id = sg.id
      INNER JOIN table_types AS tt
      ON t.table_type_id = tt.id
      WHERE t.is_present = ? ';
    return $seasonId === null ? $db->query($query . 'AND sg.season_id IS NULL', 1) :
        $db->query($query . 'AND sg.season_id = ?', 1, $seasonId);
  }

  /**
   * @return array
   */
  public function fetchForSeason(): array
  {
    return ($this->getForSeason(null))->fetchPairs('table_type_id', self::LABEL);
  }

  /**
   * @param string $label
   * @return IRow|null
   */
  public function findByLabel(string $label)
  {
    return $this->getAll()->where(self::LABEL, $label)->fetch();
  }

  /**
   * @param string $label
   * @return bool|int|ActiveRow
   */
  public function insertData(string $label)
  {
    return $this->insert( array(self::LABEL => $label) );
  }

}
