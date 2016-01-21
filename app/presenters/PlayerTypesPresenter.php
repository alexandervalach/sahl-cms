<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Application\BadRequestException;

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
        $form->addText('type', 'Typ hráča')
                ->addRule(Form::MAX_LENGTH, 'Názov môže mať len 50 znakov.', 50)
                ->setRequired();
        $form->addSubmit('save', 'Uložiť');

        FormHelper::setBootstrapFormRenderer($form);
        $form->onSuccess[] = $this->submittedAddForm;
        return $form;
    }

    protected function createComponentEditForm() {
        $form = new Form;
        $form->addText('type', 'Typ hráča')
                ->addRule(Form::MAX_LENGTH, 'Názov môže mať len 50 znakov.', 50)
                ->setRequired();
        $form->addSubmit('save', 'Uložiť');

        FormHelper::setBootstrapFormRenderer($form);
        $form->onSuccess[] = $this->submittedEditForm;
        return $form;
    }
    
    protected function createComponentRemoveForm() {
        $form = new Form;
        $form->addSubmit('save', 'Zmaž')
                ->setAttribute('class', 'btn btn-danger')
                ->onClick[] = $this->submittedRemoveForm;
        $form->addSubmit('cancel', 'Zrušiť')
                ->setAttribute('class', 'btn btn-warning')
                ->onClick[] = $this->formCancelled;
        return $form;
    }

    public function submittedAddForm(Form $form) {
        $values = $form->getValues();
        $this->playerTypesRepository->insert($values);
        $this->redirect('all#nav');
    }

    public function submittedEditForm(Form $form) {
        $values = $form->getValues();
        $this->playerTypeRow->update($values);
        $this->redirect('all#nav');
    }
    
    public function submittedRemoveForm() {
        $this->userIsLogged();
        $this->playerTypeRow->delete();
        $this->redirect('all#nav');
    }
    
    public function formCancelled() {
        $this->redirect('all#primary');
    }

}
