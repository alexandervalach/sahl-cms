<?php

namespace App\Model;

use Nette\Database\ResultSet;

class SeasonsTeamsRepository extends Repository {

  const SEASON_ID = 'season_id';

  protected $tableName = 'seasons_teams';

  /***
   * @return
   */
  public function getForSeason($seasonId = null) {
    return $this->findAll()->where('season_id', $seasonId);
  }

}
