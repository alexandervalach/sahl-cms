<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;

class FightsRepository extends Repository {

    public function getForFight(ActiveRow $row, $key, $table = 'teams') {
        return $row->ref($table, $key);
    }

    public function getPlayersForFight(ActiveRow $row, $key, $table = 'teams') {
        return $this->getForFight($row, $key, $table)->related('players');
    }

    public function getPlayersForTeam(ActiveRow $row, $key, $table = 'teams') {
        $players = $this->getPlayetsForFight($row, $table, $key);
        $list = array();
        foreach ($players as $player) {
            $list[$player->id] = $player->lname;
        }
        return $list;
    }

    public function getPlayersForSelect(ActiveRow $row, $key1, $key2, $table = 'teams') {
        $teamOnePlayers = $this->getPlayersForFight($row, $key1, $table);
        $teamTwoPlayers = $this->getPlayersForFight($row, $key2, $table);

        foreach ($teamOnePlayers as $player) {
            $teamOneIdList[] = $player->id;
            $teamOneNameList[] = $player->lname;
        }

        foreach ($teamTwoPlayers as $player) {
            $teamTwoIdList[] = $player->id;
            $teamTwoNameList[] = $player->lname;
        }

        $idList = array_merge($teamOneIdList, $teamTwoIdList);
        $nameList = array_merge($teamOneNameList, $teamTwoNameList);
        $list = array_combine($idList, $nameList);
        return $list;
    }

}
