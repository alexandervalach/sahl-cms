<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;

class FightsRepository extends Repository {

  public function getTeam(ActiveRow $row, $key) {
    return $row->ref('teams', $key);
  }

}
