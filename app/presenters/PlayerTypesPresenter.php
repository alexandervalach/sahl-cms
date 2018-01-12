<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;

class PlayerTypesPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $playerTypeRow;

    /** @var string */
    private $error = "Player type not found";

    public function actionAll() {
        $this->userIsLogged();
    }

    public function renderAll() {
        $this->template->types = $this->playerTypesRepository->findAll();
        $this->getComponent('addForm');
    }

    public function actionEdit($id) {
        $this->userIsLogged();
        $this->playerTypeRow = $this->playerTypesRepository->findById($id);
    }

    public function renderEdit($id) {
        if (!$this->playerTypeRow) {
            throw new BadRequestException($this->error);
        }
        $this->getComponent('editForm')->setDefaults($this->playerTypeRow);
        $this->template->type = $this->playerTypeRow;
    }

    public function actionAdd() {
        $this->userIsLogged();
    }

    public function renderAdd() {
        $this->getComponent('addForm');
    }

    public function actionDelete($id) {
        $this->userIsLogged();
        $this->playerTypeRow = $this->playerTypesRepository->findById($id);
    }

    public function renderDelete($id) {
        if (!$this->playerTypeRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->type = $this->playerTypeRow;
        $this->getComponent('removeForm');
    }

    protected function createComponentAddForm() {
        $form = new Form;
        $form->addText('name', 'Typ hráča')
             ->addRule(Form::MAX_LENGTH, 'Názov môže mať len 50 znakov.', 50)
             ->setRequired();
        $form->addText('abbr', 'Skratka');
        $form->addSubmit('save', 'Uložiť');
        FormHelper::setBootstrapFormRenderer($form);
        $form->onSuccess[] = [$this, 'submittedAddForm'];
        return $form;
    }

    protected function createComponentEditForm() {
        $form = new Form;
        $form->addText('name', 'Typ hráča')
             ->addRule(Form::MAX_LENGTH, 'Názov môže mať len 50 znakov.', 50)
             ->setRequired();
        $form->addText('abbr', 'Skratka');
        $form->addSubmit('save', 'Uložiť');
        FormHelper::setBootstrapFormRenderer($form);
        $form->onSuccess[] = [$this, 'submittedEditForm'];
        return $form;
    }

    protected function createComponentRemoveForm() {
        $form = new Form;
        $form->addSubmit('save', 'Odstrániť')
             ->setAttribute('class', 'btn btn-danger')
             ->onClick[] = $this->submittedRemoveForm;
        $form->addSubmit('cancel', 'Zrušiť')
             ->setAttribute('class', 'btn btn-warning')
             ->onClick[] = $this->formCancelled;
        return $form;
    }

    public function submittedAddForm(Form $form, $values) {
        $this->playerTypesRepository->insert($values);
        $this->flashMessage('Typ hráča bol pridaný', 'success');
        $this->redirect('all');
    }

    public function submittedEditForm(Form $form, $values) {
        $this->playerTypeRow->update($values);
        $this->flashMessage('Typ hráča bol upravený', 'success');
        $this->redirect('all');
    }

    public function submittedRemoveForm() {
        $players = $this->playerTypeRow->related('player');
        $data = array('type_id' => 1);
        
        foreach($players as $player) {
            $player->update($data);
        }
        
        $this->playerTypeRow->delete();
        $this->flashMessage('Typ hráča bol odstránený', 'success');
        $this->redirect('all');
    }

    public function formCancelled() {
        $this->redirect('all');
    }

}
