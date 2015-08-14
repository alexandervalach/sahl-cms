<?php

namespace App\Presenters;


class StatsPresenter extends BasePresenter {

    /** @var Nette\Databasq\Table\Selection */
    private $playerSelection;

    public function actionDefault() {
        $this->playerSelection = $this->playersRepository->findAll()->order('goals DESC, lname DESC');
    }

    public function renderDefault() {
        $this->template->stats = $this->playerSelection;
    }

}
