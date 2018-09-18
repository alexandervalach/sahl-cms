<?php

namespace App\Model;

class LinksRepository extends Repository {

    public function getSponsors() {
        return $this->findByValue('sponsor', 1);
    }

}
