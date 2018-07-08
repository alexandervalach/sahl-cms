<?php

namespace App\Model;

class TableTypesRepository extends Repository {

    public function getTypes() {
        return $this->findAll()->fetchPairs('id', 'name');
    }

}
