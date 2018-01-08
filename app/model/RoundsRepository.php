<?php

namespace App\Model;

class RoundsRepository extends Repository {

    public function getLatestRound() {
        return $this->findByValue('archive_id', null)->order('id DESC')->fetch();
    }
    
    public function getLatestFights() {
        if ($round = $this->getLatestRound()) {
            return $round->related('fights');
        }
        return null;
    }

    public function archive( $arch_id ) {
    	$rounds = $this->findByValue('archive_id', null);
    	if (!$rounds->count()) {
    		foreach ($rounds as $round) {
				$round->update($arch_id);
			}
    	}
    }
    
}
