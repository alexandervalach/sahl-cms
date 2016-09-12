<?php

namespace App\Presenters;

class StatsPresenter extends BasePresenter {

    public function actionAll() {
        
    }

    public function renderAll() {
        $this->template->stats = $this->playersRepository->findByValue('archive_id', null)->where('lname != ?', ' ')->order('goals DESC, lname DESC');
    }

    public function actionArchive($id) {

    }

    public function renderArchive($id) {
    	$this->template->stats = $this->playersRepository->findByValue('archive_id', $id)->where('lname != ?', ' ')->order('goals DESC, lname DESC');
    	$this->template->archive = $this->archiveRepository->findById($id);
    }

}
