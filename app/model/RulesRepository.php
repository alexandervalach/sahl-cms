<?php 

namespace App\Model;

class RulesRepository extends Repository {

	public function archive( $arch_id ) {
		$check_rounds = $this->findByValue('archive_id', $arch_id);

		if ($check_rounds->count()) {
			return;
		}

    	$rounds = $this->findByValue('archive_id', null);
    	if (!$rounds->count()) {
    		foreach ($rounds as $round) {
				$round->update($arch_id);
			}
			$this->rulesRepository->insert( 
							array('rule' => 'Onedlho sa tu budú nachádzať pravidlá a smernice')
						);
    	}
    }
	
}