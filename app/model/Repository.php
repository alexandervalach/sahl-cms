<?php

namespace App\Model;

use Nette\Database\Table\Selection;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;

/**
 * Description of Repository
 *
 * @author
 */
abstract class Repository {

  const IS_PRESENT = 'is_present';
  const SEASON_ID = 'season_id';
  const ID = 'id';

  /** @var Context */
  protected $database;

  /** @var string */
  protected $tableName;

  public function __construct(Context $database) {
    $this->database = $database;
  }

  /**
   * Vrací objekt reprezentující databázovou tabulku.
   * @return Selection
   */
  protected function getTable() {
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
  public function getConnection() {
    return $this->database;
  }

  /**
   * @return Selection
   */
  public function getAll() {
    return $this->findAll()->where(self::IS_PRESENT, 1);
  }

  /**
   * @return Selection
   */
  public function getArchived($seasonId = null) {
    return $this->getAll()->where(self::SEASON_ID, $seasonId);
  }

  /**
   * Returns rows from the table
   * @return Selection
   */
  public function findAll() {
    return $this->getTable();
  }

  /**
   * Vrací řádky podle filtru, např. array('name' => 'Jon').
   * @return Selection
   */
  public function findBy(array $by) {
    return $this->getTable()->where($by);
  }

  /**
   * Returns selection
   * @param type $columnName
   * @param type $value
   * @return Selection
   */
  public function findByValue($columnName, $value) {
    $condition = array($columnName => $value);
    return $this->findBy($condition);
  }

  /**
   * Returns row identified by ID
   * @param type $id primary key
   * @return ActiveRow
   */
  public function findById($id) {
    return $this->getTable()->get($id);
  }

  /**
   * Add data to table
   * @param $id data to be inserted
   * @param $data data to be inserted
   * @return ActiveRow
   */
  public function update($id, $data) {
    $this->getTable()->wherePrimary($id)->update($data);
  }

  /**
   * Add data to table
   * @param $data data to be inserted
   * @return ActiveRow
   */
  public function insert($data) {
    return $this->getTable()->insert($data);
  }

  /**
   * @return void
   */
  public function remove($id) {
    $this->getTable()->get($id)->update(
      array('is_present' => 0)
    );
  }

  /**
   * @return Selection
   */
  public function select($data) {
    return $this->getAll()->select($data);
  }

  public function getAsArray($id) {
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

}
