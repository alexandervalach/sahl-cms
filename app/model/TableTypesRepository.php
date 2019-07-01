<?php

namespace App\Model;

class TableTypesRepository extends Repository {

  protected $tableName = 'table_types';

  public function getTableTypes() {
    return $this->getAll()->fetchPairs('id', 'label');
  }

}
