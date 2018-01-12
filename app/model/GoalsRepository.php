<?php

namespace App\Model;

class GoalsRepository extends Repository {
    

	public function getPlayerGoalsCount($id) {
		return $this->findByValue('player_id = ?', $id)
			        ->sum('goals');
	}

}
