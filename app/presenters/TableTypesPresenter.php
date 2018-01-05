<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;

class TableTypesPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $optionRow;

    /** @var string */
    private $error;

    public function actionAddToSidebar($type) {
        $this->userIsLogged();
    }

    public function renderAddToSidebar($type) {
        $this->getComponent('addToSidebarForm');
    }

    protected function createComponentAddToSidebarForm() {
        $form = new Form;
        $form->addCheckbox('visible', ' Zobraziť na bočnom paneli.');
        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = $this->submittedAddToSidebarForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddToSidebarForm(Form $form, $values) {
        $this->optionRow->update($values);
        $this->redirect('Tables:all');
    }

}
