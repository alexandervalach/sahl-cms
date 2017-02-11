<?php

namespace App\Model;

class TeamsRepository extends Repository {
    /**
     * Loop trought all teams and store them in array. 
     * Accessible index $team->id. 
     * Value is $team->name.
     * @return array
     */
    public function getTeams() {
        $teamSelection = $this->findByValue('archive_id', null)->order('name ASC');
        $teams = array();
        foreach ($teamSelection as $team) {
            $teams[$team->id] = $team->name;
        }
        return $teams;
    }
}
