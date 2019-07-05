<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Utils\ArrayHash;

/**
 * Description of Repository
 *
 * @author
 */
abstract class Repository {

  const IS_PRESENT = 'is_present';
  const IS_VISIBLE = 'is_visible';
  const SEASON_ID = 'season_id';
  const ID = 'id';
  const LABEL = 'label';

  /** @var Context */
  protected $database;

  /** @var string */
  protected $tableName;

  public function __construct(Context $database)
  {
    $this->database = $database;
  }

  /**
   * Vrací objekt reprezentující databázovou tabulku.
   * @return Selection
   */
  protected function getTable(): Selection
  {
    if (isset($this->tableName)) {
      return $this->database->table($this->tableName);
    } else {
      // název tabulky odvodíme z názvu třídy
      preg_match('#(\w+)Repository$#', get_class($this), $m);
      return $this->database->table(lcfirst($m[1]));
    }
  }

  /**
   * @return Context
   */
  public function getConnection(): Context
  {
    return $this->database;
  }

  /**
   * @return Selection
   */
  public function getAll(): Selection
  {
    return $this->findAll()->where(self::IS_PRESENT, 1);
  }

  /**
   * @return Selection
   */
  public function getArchived($seasonId = null): Selection
  {
    return $this->getAll()->where(self::SEASON_ID, $seasonId);
  }

  /**
   * Returns rows from the table
   * @return Selection
   */
  public function findAll(): Selection
  {
    return $this->getTable();
  }

  /**
   * Vrací řádky podle filtru, např. array('name' => 'Jon').
   * @return Selection
   */
  public function findBy(array $by): Selection
  {
    return $this->getTable()->where($by);
  }

  /**
   * Returns selection
   * @param string $columnName
   * @param type $value
   * @return Selection
   */
  public function findByValue(string $columnName, $value): Selection
  {
    return $this->findBy( array($columnName => $value) );
  }

  /**
   * Returns row identified by ID
   * @param int $id primary key
   * @return ActiveRow
   */
  public function findById(int $id): ActiveRow
  {
    return $this->getTable()->get($id);
  }

  /**
   * Add data to table
   * @param int $id data to be inserted
   * @param array $data data to be inserted
   * @return ActiveRow
   */
  public function update(int $id, array $data): ActiveRow
  {
    $this->getTable()->wherePrimary($id)->update($data);
  }

  /**
   * Add data to table
   * @param array|ArrayHash $data data to be inserted
   * @return ActiveRow
   */
  public function insert($data) {
    return $this->getTable()->insert($data);
  }

  /**
   * @param int $id
   * @return void
   */
  public function remove(int $id): void
  {
    $this->getTable()->wherePrimary($id)->update(
      array(self::IS_PRESENT => 0)
    );
  }

  /**
   * @param type $data
   * @return Selection
   */
  public function select($data): Selection
  {
    return $this->getAll()->select($data);
  }

  /*
  public function getAsArray($id)
  {
    $checkRows = $this->findByValue('season_id', $id);

    if ($checkRows->count()) {
      return null;
    }

    $rows = $this->findByValue('season_id', null);
    $data = array();
    if (!$rows->count()) {
      return null;
    } else {
      $i = 0;
      foreach ($rows as $row) {
        $data[$i] = $row;
        $i++;
      }
    }
    return $data;
  }
  */

}
