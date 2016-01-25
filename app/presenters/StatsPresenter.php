<?php

namespace App\Presenters;

class StatsPresenter extends BasePresenter {

    /** @var Nette\Databasq\Table\Selection */
    private $playerSelection;

    public function actionAll() {
        $this->playerSelection = $this->playersRepository->findAll()->order('goals DESC, lname DESC');
    }

    public function renderAll() {
        $this->template->stats = $this->playerSelection;
    }

}
