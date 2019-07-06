<?php

declare(strict_types = 1);

namespace App\Model;

use Nette\Database\Table\Selection;

class TableTypesRepository extends Repository
{
  protected $tableName = 'table_types';

  public function getTableTypes(): Selection
  {
    return $this->getAll()->fetchPairs(self::ID, self::LABEL);
  }

}
