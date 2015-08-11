<?php

namespace App\Model;

class PlayersRepository extends Repository{
    public function getPlayers() {
        $players = $this->findAll();
        $list = array();
        
        foreach($players as $player) {
           $list[$player->id] = $player->lname; 
        }
        
        return $list;
    }
}

?>