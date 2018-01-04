<?php

namespace App\Model;

class PlayersRepository extends Repository {

    public function getNonEmptyPlayers() {
        $players = $this->select('id, name, num')
                        ->where('archive_id', null)
                        ->where('num != ?', 0)
                        ->order('name');
        $list = NULL;

        foreach ($players as $player) {
            $list[$player->id] = $player->name . ' - ' . $player->num;
        }
        return $list;
    }
}
