<?php

namespace App\Model;

class PlayersRepository extends Repository {

  public function getNonEmptyPlayers() {
    return $this->select('id, name, num')->where('num != ?', 0)->order('name')->fetchPairs('id', 'name');
  }

}
