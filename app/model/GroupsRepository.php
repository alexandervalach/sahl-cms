<?php

namespace App\Model;

class GroupsRepository extends Repository {

  const LABEL = 'label';

  public function getGroups() {
    return $this->getAll()->fetchPairs(self::ID, self::LABEL);
  }

}
