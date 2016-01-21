<?php

namespace App\Presenters;

class PlayerTypesPresenter extends BasePresenter {
    public function actionAll() {
        $this->userIsLogged();
    }
    
    public function renderAll() {
        $this->template->types = $this->playerTypesRepository->findAll();
    }
}
