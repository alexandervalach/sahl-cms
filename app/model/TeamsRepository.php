<?php

namespace App\Model;

class TeamsRepository extends Repository {

  /**
   * Loop trough all teams and store them in array.
   * Accessible index $team->id.
   * Value is $team->name.
   * @return array
   */
  public function getTeams() {
    return $this->getArchived()->order('name ASC')->fetchPairs('id', 'name');
  }

  public function getPlayers($teamId) {
    $team = $this->findById($teamId);
    return $team->related('players')->fetchPairs('id', 'name');
  }

}
