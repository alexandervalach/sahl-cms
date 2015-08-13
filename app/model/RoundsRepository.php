<?php

namespace App\Model;

class RoundsRepository extends Repository{
    public function getLatestRound() {
        return $this->findAll()->order('id DESC')->limit(1);
    }
    
    public function getLatestRoundFights() {
        $rounds = $this->getLatestRound();
        foreach( $rounds as $round ) {
            return $round->related('fights');
        }
    }
}
