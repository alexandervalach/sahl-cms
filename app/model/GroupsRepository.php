<?php

declare(strict_types = 1);

namespace App\Model;

use Nette\Database\IRow;

class GroupsRepository extends Repository {

  const LABEL = 'label';

  /**
   * @return array
   */
  public function getGroups(): array
  {
    return $this->findAll()->fetchPairs(self::ID, self::LABEL);
  }

  /**
   * @param string $label
   * @return IRow|null
   */
  public function getByLabel(string $label)
  {
    return $this->findAll()->where(self::LABEL, $label)->fetch();
  }

  public function insertData (string $label)
  {
    return $this->insert( array(self::LABEL => $label) );
  }

}
