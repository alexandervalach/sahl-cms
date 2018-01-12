<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;

class TableTypesPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $tableTypeRow;

    /** @var string */
    private $error;

    public function renderAll() {
        $this->userIsLogged();
        $this->template->types = $this->tableTypesRepository->findAll();
        $this->getComponent('addForm');
    }

    public function actionShow($id) {
        $this->userIsLogged();
        $this->tableTypeRow = $this->tableTypesRepository->findById($id);
        $this->submittedShowTable();
    }

    public function submittedShowTable() {
        $this->tableTypeRow->update( array('visible' => 1) );
        $this->flashMessage('Tabuľka je viditeľná', 'success');
        $this->redirect('all');
    }

    public function actionHide($id) {
        $this->userIsLogged();
        $this->tableTypeRow = $this->tableTypesRepository->findById($id);
        $this->submittedHideTable();
    }

    public function submittedHideTable() {
        $this->tableTypeRow->update( array('visible' => 0) );
        $this->flashMessage('Tabuľka je skrytá pre verejnosť', 'success');
        $this->redirect('all');
    }

    protected function createComponentAddForm() {
        $form = new Form;
        $form->addText('name', 'Názov')
             ->setRequired()
             ->setAttribute('placeholder', 'Play Off');
        $form->addCheckbox('visible', ' Chcem, aby sa tabuľka zobrazila na stránke');
        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = [$this, 'submittedAddForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddForm(Form $form, $values) {
        $this->tableTypesRepository->insert($values);
        $this->flashMessage('Typ tabuľky bol pridaný', 'success');
        $this->redirect('all');
    }

}
