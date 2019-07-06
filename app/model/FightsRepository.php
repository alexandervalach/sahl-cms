<?php

declare(strict_types = 1);

namespace App\Model;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class FightsRepository extends Repository
{
  public function getTeam(ActiveRow $row, $key = 'team_id')
  {
    return $row->ref('teams', $key);
  }

  public function getForRound(int $id): Selection
  {
    return $this->getAll()->where('round_id', $id);
  }
}
