<?php

namespace App\Model;

class LinksRepository extends Repository {

    public function getSponsors() {
        $sponsors = $this->findByValue('sponsor', 1);
        if ($sponsors != null) {
            return $sponsors;
        }
        return null;
    }

}

?>