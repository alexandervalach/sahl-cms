<?php

namespace App\Model;

use Nette\Database\Table\Selection;

class SeasonsTeamsRepository extends Repository {

  const SEASON_ID = 'season_id';

  protected $tableName = 'seasons_teams';

  /**
   * @return Selection
   */
  public function getAll(): Selection
  {
    return $this->findAll();
  }

  /**
   * @return Selection
   */
  public function getForSeason($id = null): Selection
  {
    return $this->findAll()->where('season_id', $id);
  }

}
