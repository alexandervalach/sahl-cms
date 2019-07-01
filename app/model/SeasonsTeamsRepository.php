<?php

namespace App\Model;

use Nette\Database\ResultSet;

class SeasonsTeamsRepository extends Repository {

  const SEASON_ID = 'season_id';

  protected $tableName = 'seasons_teams';

}
