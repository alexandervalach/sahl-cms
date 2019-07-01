<?php

namespace App\Model;

class PunishmentsRepository extends Repository {

  /**
   * Finds punishments for specified player
   * @param $id holds the player id
   */
  public function getForPlayer($id) {
    return $this->findByValue('team_id', $id);
  }

}
