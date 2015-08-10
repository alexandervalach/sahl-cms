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

    public function actionEdit($id) {
        $this->userIsLogged();
        $this->punishmentRow = $this->punishmentsRepository->findById($id);
    }

    public function renderEdit($id) {
        $this->getComponent('editPunishmentForm')->setDefaults($this->punishmentRow);
    }

    public function actionDelete($id) {
        
    }

    public function renderDelete($id) {
        
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

    public function submittedEditPunishmentForm(Form $form) {
        $this->userIsLogged();
        $values = $form->getValues();
        $this->punishmentRow->update($values);
        $this->redirect('all');
    }
    
    public function submittedDeleteForm() {
    }
    
    public function formCancelled() {
        $this->redirect('all');
    }
}
