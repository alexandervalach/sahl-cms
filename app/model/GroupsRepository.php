<?php

declare(strict_types=1);

namespace App\Model;

class GroupsRepository extends Repository {

  const LABEL = 'label';

  public function getAsArray()
  {
    return $this->getAll()->fetchPairs(self::ID, self::LABEL);
  }

}
