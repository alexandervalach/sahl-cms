<?php

namespace App\Presenters;

use Nette\Application\UI\Form;
use Nette\Database\Connection;
use App\FormHelper;

class StatsPresenter extends BasePresenter {

    public function actionAll() {
        
    }

    public function renderAll() {
        $this->template->stats = $this->playersRepository->findByValue('archive_id', null)
                                      ->where('lname != ?', ' ')
                                      ->order('goals DESC, lname DESC');
        $this->template->i = 0;
        $this->template->j = 0;
        $this->template->current = 0;
        $this->template->previous = 0;
        
        if ($this->user->isLoggedIn()) {
            $this->getComponent('resetForm');
        }
    }

    public function actionArchView($id) {

    }

    public function renderArchView($id) {
    	$this->template->stats = $this->playersRepository->findByValue('archive_id', $id)
                                      ->where('lname != ?', ' ')
                                      ->order('goals DESC, lname DESC');
    	$this->template->archive = $this->archiveRepository->findById($id);
    }

    protected function createComponentResetForm() {
        $form = new Form;
        
        $form->addSubmit('reset', 'Vynulovať')
             ->setAttribute('class', 'btn btn-large btn-danger')
             ->onClick[] = $this->submittedResetForm;

        $form->addSubmit('cancel', 'Zrušiť')
             ->setAttribute('class', 'btn btn-large btn-warning')
             ->setAttribute('data-dismiss', 'modal');

        $form->addProtection();

        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedResetForm() {
        $players = $this->playersRepository
                        ->findByValue('archive_id', null)
                        ->where('goals != ?', 0);
        $values = array('goals' => 0);
        foreach ($players as $player) {
            $player->update($values);
        }
        $this->redirect("all");
    }

    public function formCancelled() {
        $this->redirect("all");
    }
}
