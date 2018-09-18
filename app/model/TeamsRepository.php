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
        return $this->findByValue('archive_id', null)->order('name ASC')->fetchPairs('id', 'name');
    }

    public function getPlayersForTeam($team_id) {
        $team = $this->findById($team_id);
        return $team->related('players')->fetchPairs('id', 'name');
    }

}
