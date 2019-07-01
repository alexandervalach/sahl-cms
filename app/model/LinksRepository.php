<?php

namespace App\Model;

class LinksRepository extends Repository {

  const LABEL = 'label';

  public function getSponsors() {
    return $this->getAll()->where('sponsor', 1);
  }

  public function getLinks() {
    return $this->getAll()->where('sponsor', 0)->order(self::LABEL);
  }

}
