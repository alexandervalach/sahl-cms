<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;

class PlayerTypesPresenter extends BasePresenter {
    public function actionAll() {
        $this->userIsLogged();
    }
    
    public function renderAll() {
        $this->template->types = $this->playerTypesRepository->findAll();
    }
    
    public function actionAdd() {
        $this->userIsLogged();
    }
    
    public function renderAdd() {
        $this->getComponent('addForm');
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
    
    public function submittedAddForm(Form $form) {
        $values = $form->getValues();
        $this->playerTypesRepository->insert($values);
        $this->redirect('all#primary');
    }
}
