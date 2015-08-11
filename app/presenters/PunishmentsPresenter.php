<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;

class PunishmentsPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $punishmentRow;

    public function actionAll() {
        
    }

    public function renderAll() {
        $this->template->punishments = $this->punishmentsRepository->findAll();
    }

    public function actionAdd() {
        $this->userIsLogged();
    }

    public function rednerAdd() {
        $this->getComponent('addPunishmentForm');
    }

    public function actionEdit($id) {
        $this->userIsLogged();
        $this->punishmentRow = $this->punishmentsRepository->findById($id);
    }

    public function renderEdit($id) {
        $this->getComponent('editPunishmentForm')->setDefaults($this->punishmentRow);
    }

    public function actionDelete($id) {
        $this->userIsLogged();
        $this->punishmentRow = $this->punishmentsRepository->findById($id);
    }

    public function renderDelete($id) {
        $this->template->punishment = $this->punishmentRow;
        $this->getComponent('deleteForm');
    }

    protected function createComponentEditPunishmentForm() {
        $form = new Form;
        $form->addText('text', 'Trest');
        $form->addText('round', 'Kolá');
        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = $this->submittedEditPunishmentForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentAddPunishmentForm() {
        $form = new Form;
        $form->addText('text', 'Trest');
        $form->addText('round', 'Kolá');
        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = $this->submittedAddPunishmentForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedEditPunishmentForm(Form $form) {
        $this->userIsLogged();
        $values = $form->getValues();
        $this->punishmentRow->update($values);
        $this->redirect('all');
    }

    public function submittedAddPunishmentForm(Form $form) {
        $this->userIsLogged();
        $values = $form->getValues();
        $this->punishmentsRepository->insert($values);
        $this->redirect('all');
    }

    public function submittedDeleteForm() {
        $this->userIsLogged();
        $this->punishmentRow->delete();
        $this->flashMessage('Trest bol odstránený', 'success');
        $this->redirect('all');
    }

    public function formCancelled() {
        $this->redirect('all');
    }

}
