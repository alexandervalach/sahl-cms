<?php

namespace App\Model;

class PlayerTypesRepository extends Repository{
    public function getTypes() {
        $types = $this->findByValue('id NOT', 1);
        $list = array();
        
        foreach ($types as $type) {
            $list[$type->id] = $type->type;
        }
        
        return $list;
    }
}
