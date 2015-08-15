<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;

class FightsRepository extends Repository {

    public function getTeamForFight(ActiveRow $row, $key) {
        return $row->ref('teams', $key);
    }
    
    public function getPlayersForTeam(ActiveRow $row, $key) {
       $players = $this->getTeamForFight($row, $key)->related('players'); 
       $list = array();
       foreach( $players as $player ) {
           $list[$player->id] = $player->lname;
       }
       return $list;
    }
}

?>