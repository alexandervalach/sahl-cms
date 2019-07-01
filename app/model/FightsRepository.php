<?php

namespace App\Model;

use Nette\Database\Table\ActiveRow;

class FightsRepository extends Repository {

  public function getTeam(ActiveRow $row, $key = 'team_id') {
    return $row->ref('teams', $key);
  }

  public function getForRound($roundId) {
    return $this->getAll()->where('round_id', $roundId);
  }

}
