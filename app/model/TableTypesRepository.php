<?php

declare(strict_types = 1);

namespace App\Model;

class TableTypesRepository extends Repository
{
  protected $tableName = 'table_types';

  public function getTableTypes(): array
  {
    return $this->getAll()->fetchPairs(self::ID, self::LABEL);
  }

}
