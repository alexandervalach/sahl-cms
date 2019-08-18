<?php

namespace App\Model;

class RulesRepository extends Repository {

  public function archive($seasonId) {
    $checkRounds = $this->getNonArchived();

    if ($checkRounds->count()) {
      return;
    }

    $rounds = $this->findByValue('season_id', null);
    if (!$rounds->count()) {
      foreach ($rounds as $round) {
        $round->update($seasonId);
      }
      $this->rulesRepository->insert(
        array('rule' => 'Onedlho sa tu budú nachádzať pravidlá a smernice')
      );
    }
  }

  /**
   * @return IRow|null
   */
  public function getLatest()
  {
    return $this->getArchived()->order('id DESC')->fetch();
  }

}
