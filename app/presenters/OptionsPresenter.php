<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Application\BadRequestException;

class OptionsPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $optionRow;

    /** @var string */
    private $error;

    public function actionAddToSidebar($type) {
        $this->userIsLogged();
        $this->optionRow = $this->optionsRepository->findByValue('option', $type)->fetch();
    }

    public function renderAddToSidebar($type) {
        if (!$this->optionRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->option = $this->optionRow;
        $this->getComponent('addToSidebarForm')->setDefaults($this->optionRow);
    }

    protected function createComponentAddToSidebarForm() {
        $form = new Form;
        $form->addCheckbox('visible', ' Zobraziť na bočnom paneli.');
        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = $this->submittedAddToSidebarForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddToSidebarForm(Form $form) {
        $values = $form->getValues();
        $this->optionRow->update($values);
        $this->redirect('Tables:all#nav');
    }

}
