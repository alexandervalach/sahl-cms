<?php

namespace App\Model;

class PlayerTypesRepository extends Repository {

  const LABEL = 'label';

  /** @var string */
  protected $tableName = 'player_types';

  public function getTypes() {
    return $this->getAll()->fetchPairs(self::ID, self::LABEL);
  }

  public function getGoalie() {
    return $this->getAll()->where('label LIKE', '%brankár%')->fetch();
  }

}
