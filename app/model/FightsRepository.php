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
        foreach ($players as $player) {
            $list[$player->id] = $player->lname;
        }
        return $list;
    }
    
    public function getPlayersForFight(ActiveRow $row, $key) {
       return $this->getTeamForFight($row, $key)->related('players');
    }

    public function getPlayersForSelect(ActiveRow $row, $key1, $key2) {
        $teamOnePlayers = $this->getPlayersForFight($row, $key1);
        $teamTwoPlayers = $this->getPlayersForFight($row, $key2);
     
        foreach ($teamOnePlayers as $player) {
            $teamOneIdList[] = $player->id;
            $teamOneNameList[] = $player->lname;
        }
        
        foreach ($teamTwoPlayers as $player) {
            $teamTwoIdList[] = $player->id;
            $teamTwoNameList[] = $player->lname;
        }
        
        $idList = array_merge( $teamOneIdList, $teamTwoIdList);
        $nameList = array_merge( $teamOneNameList, $teamTwoNameList );
        $list = array_combine($idList, $nameList);
        return $list;
    }

}
