<?php

namespace App\Model;

class PlayerTypesRepository extends Repository {
    
    public function getTypes() {
        $types = $this->findAll()->fetchPairs('id', 'type');
    }
}
