<?php

namespace App\Model;

class GoalsRepository extends Repository {

  const PLAYER_ID = 'player_id';

  public function getPlayerGoalsCount($playerId) {
    return $this->getAll()->where(self::PLAYER_ID, $playerId)->sum('goals');
  }

}
