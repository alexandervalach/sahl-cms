<?php

namespace App\Model;

class RoundsRepository extends Repository{
    public function getLatestRound() {
        $id = $this->findByValue('archive_id', null)->max('id');
        if ($id) {
          return $this->findById($id);
        }
        return null;
    }
    
    public function getLatestRoundFights() {
        $round = $this->getLatestRound();
        if($round) {
            $rounds = $round->related('fights');
            return $rounds;
        }
        return null;
    }
}
