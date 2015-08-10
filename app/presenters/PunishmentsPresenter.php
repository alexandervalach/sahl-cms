<?php

namespace App\Presenters;

class PunishmentsPresenter extends BasePresenter {

    public function actionAll() {
        
    }

    public function renderAll() {
        $this->template->punishments = $this->punishmentsRepository->findAll();
    }

}
