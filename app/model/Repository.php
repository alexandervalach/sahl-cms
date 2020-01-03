<?php

declare(strict_types = 1);

namespace App\Model;

use Nette\SmartObject;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Utils\ArrayHash;

/**
 * Description of Repository
 *
 * @author
 */
abstract class Repository
{
  use SmartObject;

  const IS_PRESENT = 'is_present';
  const ID = 'id';
  const LABEL = 'label';
  const TEAM_ID = 'team_id';
  const SEASON_ID = 'season_id';
  const GROUP_ID = 'group_id';
  const SEASON_GROUP_ID = 'season_group_id';
  const TABLE_TYPE_ID = 'table_type_id';
  const POINTS = 'points';


  /** @var Context */
  protected $database;

  /** @var string */
  protected $tableName;

  /**
   * @param Context $database
   */
  public function __construct(Context $database)
  {
    $this->database = $database;
  }

  /**
   * Returns object that represents table
   * @return Selection
   */
  protected function getTable(): Selection
  {
    if (isset($this->tableName)) {
      return $this->database->table($this->tableName);
    } else {
      // table name is derived from repository class name
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
   * @param null|int $seasonId
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
    return $this->getTable()->where(self::IS_PRESENT, 1);
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
   * @return ActiveRow|mixed
   */
  public function findById(int $id)
  {
    return $this->findAll()->get($id);
  }

  /**
   * Add data to table
   * @param int $id data to be inserted
   * @param array $data data to be inserted
   * @return int
   */
  public function update(int $id, array $data): int
  {
    $this->findAll()->wherePrimary($id)->update($data);
  }

  /**
   * Add data to table
   * @param array|ArrayHash $data data to be inserted
   * @return ActiveRow|int|bool
   */
  public function insert($data)
  {
    return $this->getTable()->insert($data);
  }

  /**
   * @param int $id
   * @return void
   */
  public function remove(int $id): void
  {
    $this->findAll()->wherePrimary($id)->update( array(self::IS_PRESENT => 0) );
  }

  /**
   * Mark item as not present
   * @param int $id
   * @return void
   */
  public function softDelete(int $id): void
  {
    $this->findAll()->wherePrimary($id)->update( array(self::IS_PRESENT => 0) );
  }

  /**
   * @param type $data
   * @return Selection
   */
  public function select($data): Selection
  {
    return $this->getAll()->select($data);
  }

}
