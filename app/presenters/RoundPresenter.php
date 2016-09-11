<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;

class RoundPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $roundRow;

    /** @var string */
    private $error = "Round not found!";

    public function actionAll() {
        
    }

    public function renderAll() {
        $this->template->rounds = $this->roundsRepository->findByValue('archive_id', null);
    }

    public function actionAdd() {
        $this->userIsLogged();
    }

    public function renderAdd() {
        $this->getComponent('addRoundForm');
    }

    public function actionEdit($id) {
        $this->userIsLogged();
        $this->roundRow = $this->roundsRepository->findById($id);
    }

    public function renderEdit($id) {
        if (!$this->roundRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->round = $this->roundRow;
        $this->getComponent('editRoundForm')->setDefaults($this->roundRow);
    }

    public function actionDelete($id) {
        $this->userIsLogged();
        $this->roundRow = $this->roundsRepository->findById($id);
    }

    public function renderDelete($id) {
        if (!$this->roundRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->round = $this->roundRow;
        $this->getComponent('deleteForm');
    }

    public function actionArchive($id) {

    }

    public function renderArchive($id) {
        $this->template->rounds = $this->roundsRepository->findByValue('archive_id', $id);
    }

    protected function createComponentAddRoundForm() {
        $form = new Form;
        $form->addText('name', 'Názov')
                ->addRule(Form::MAX_LENGTH, "Dĺžka názvu môže byť len 50 znakov", 50)
                ->setRequired("Názov je povinné pole");
        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = $this->submittedAddRoundForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditRoundForm() {
        $form = new Form;
        $form->addText('name', 'Názov')
                ->addRule(Form::MAX_LENGTH, "Dĺžka názvu môže byť len 50 znakov", 50)
                ->setRequired("Názov je povinné pole");
        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = $this->submittedEditRoundForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddRoundForm(Form $form) {
        $this->userIsLogged();
        $values = $form->getValues();
        $this->roundsRepository->insert($values);
        $this->redirect('all#nav');
    }

    public function submittedEditRoundForm(Form $form) {
        $this->userIsLogged();
        $values = $form->getValues();
        $this->roundRow->update($values);
        $this->redirect('all#nav');
    }

    public function submittedDeleteForm() {
        $this->userIsLogged();
        $fights = $this->roundRow->related('fights');
        /* Odstráni všetky zápasy daného kola */
        foreach ($fights as $fight) {
            $fight->delete();
        }
        $this->roundRow->delete();
        $this->flashMessage('Kolo bolo odstránené.', 'success');
        $this->redirect('all#nav');
    }

    public function formCancelled() {
        $this->redirect('all#nav');
    }

}
