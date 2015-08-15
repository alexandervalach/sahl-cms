<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;

class FightsRepository extends Repository {

    public function getTeam1(ActiveRow $row) {
        return $row->ref('teams', 'team1_id');
    }

    public function getTeam2(ActiveRow $row) {
        return $row->ref('teams', 'team2_id');
    }

}

?>