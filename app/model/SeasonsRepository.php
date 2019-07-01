<?php

namespace App\Model;

class SeasonsRepository extends Repository {

  /**
   * Fetch all seasons and store them as an associative array
   * @return array
   */
  public function getSeasons() {
    return $this->getAll()->fetchPairs('id', 'label');
  }

}
