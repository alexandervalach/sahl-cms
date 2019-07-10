<?php

declare(strict_types = 1);

namespace App\Model;

use Nette\Database\IRow;

class TableTypesRepository extends Repository
{
  protected $tableName = 'table_types';

  /**
   * @return array
   */
  public function getTableTypes(): array
  {
    return $this->getAll()->fetchPairs(self::ID, self::LABEL);
  }

  /**
   * @param string $label
   * @return IRow|null
   */
  public function findByLabel(string $label)
  {
    return $this->getAll()->where(self::LABEL, $label)->fetch();
  }

}
