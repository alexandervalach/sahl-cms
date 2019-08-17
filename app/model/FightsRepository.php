<?php

declare(strict_types = 1);

namespace App\Model;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class FightsRepository extends Repository
{
  /**
   * @param ActiveRow $row
   * @param string $key
   * @return mixed
   */
  public function getTeam(ActiveRow $row, $key = 'team_id')
  {
    return $row->ref('teams', $key);
  }

  /**
   * @param int $id
   * @return Selection
   */
  public function getForRound(int $id): Selection
  {
    return $this->getAll()
        ->where('round_id', $id)
        ->where(self::IS_PRESENT, 1)
        ->order('id DESC');
  }
}
