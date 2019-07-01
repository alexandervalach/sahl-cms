<?php

namespace App\Model;

class EventsRepository extends Repository {

  public function archive($seasonId) {
    $events = $this->getNonArchived();

    if (!$events->count()) {
      foreach ($events as $event) {
        $event->update($seasonId);
      }
    }
  }

}
