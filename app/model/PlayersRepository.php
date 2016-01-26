<?php

namespace App\Model;

class PlayersRepository extends Repository {

    public function getPlayers() {
        $players = $this->findAll();
        $list = array();

        foreach ($players as $player) {
            $list[$player->id] = $player->lname;
        }

        return $list;
    }

    public function getPlayersByValue($key, $value) {
        $players = $this->findByValue($key, $value)->order('lname ASC');
        if ($players == null) {
            return null;
        }

        $list = array();

        foreach ($players as $player) {
            if ($player->num != 0) {
                $list[$player->id] = $player->lname . ' - ' . $player->num;
            }
        }
        return $list;
    }

}
