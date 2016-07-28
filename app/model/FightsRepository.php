<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;

class FightsRepository extends Repository {

    public function getForFight(ActiveRow $row, $key, $table = 'teams') {
        return $row->ref($table, $key);
    }

}
