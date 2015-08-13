<?php

namespace App\Model;

class TablesRepository extends Repository{
    public function getTableStats() {
        return $this->findAll()->order('points ASC');
    }
}

?>