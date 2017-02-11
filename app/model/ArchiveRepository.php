<?php 

namespace App\Model;

class ArchiveRepository extends Repository {
	/**
     * Loop trought all archives and store them in array. 
     * Accessible index $team->id. 
     * Value is $team->name.
     * @return array
     */
    public function getArchives() {
        $archiveSelection = $this->findAll();
        if (!$archiveSelection) {
        	return NULL;
        }
        $arhives = array();
        foreach ($archiveSelection as $arch) {
            $archives[$arch->id] = $arch->title;
        }
        return $archives;
    }
}