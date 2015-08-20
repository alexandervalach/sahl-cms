<?php

namespace App\Model;

class RoundsRepository extends Repository{
    public function getLatestRound() {
        $id = $this->getTable()->max('id');
        return $this->findById($id);
    }
    
    public function getLatestRoundFights() {
        $round = $this->getLatestRound();
        $rounds = $round->related('fights');
        return $rounds;
    }
}
