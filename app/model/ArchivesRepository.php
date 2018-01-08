<?php 

namespace App\Model;

class ArchivesRepository extends Repository {
	/**
     * Loop trought all archives and store them in array. 
     * Accessible index $team->id. 
     * Value is $team->name.
     * @return array
     */
    public function getArchives() {
        return $this->findAll()->fetchPairs('id', 'title');
    }
}