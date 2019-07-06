<?php

declare(strict_types = 1);

namespace App\Model;

class RoundsRepository extends Repository {

  const LABEL = 'label';

  public function getRounds() {
    return $this->getArchived()->fetch(self::ID, self::LABEL);
  }

  public function getLatestRound() {
    return $this->getArchived()->order(self::ID . ' DESC')->fetch();
  }

  public function getLatestFights() {
    if ($round = $this->getLatestRound()) {
      return $round->related('fights');
    }
    return null;
  }

  public function archive($arch_id) {
    $rounds = $this->getArchived();
    if (!$rounds->count()) {
      foreach ($rounds as $round) {
        $round->update($arch_id);
      }
    }
  }

}
