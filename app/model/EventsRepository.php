<?php

namespace App\Model;

class EventsRepository extends Repository {

    public function archive($arch_id) {

        $events = $this->findByValue('archive_id', null);

        $this->database->beginTransaction();

        if (!$events->count()) {
            foreach ($events as $event) {
                $event->update($arch_id);
            }
        }

        $this->database->commit();
    }

}
