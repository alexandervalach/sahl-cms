<?php

namespace App\Presenters;

class StatsPresenter extends BasePresenter {

    public function actionAll() {
        
    }

    public function renderAll() {
        $this->template->stats = $this->playersRepository->findAll()->where('lname != ?', ' ')->order('goals DESC, lname DESC');
    }

}
