<?php

namespace App\Model;

class PlayerTypesRepository extends Repository {
    
    public function getTypes() {
        $types = $this->findAll();
        
        foreach ($types as $type) {
            $list[$type->id] = $type->type;
        }
        
        return $list;
    }
}
