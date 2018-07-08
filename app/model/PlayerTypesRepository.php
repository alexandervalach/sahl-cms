<?php

namespace App\Model;

class PlayerTypesRepository extends Repository {
    
    public function getTypes() {
        return $this->findAll()->fetchPairs('id', 'type');
    }
}
