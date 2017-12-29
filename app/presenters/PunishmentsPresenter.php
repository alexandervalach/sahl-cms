<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;

class PunishmentsPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $punishmentRow;

    /** @var ActiveRow */
    private $archRow;

    public function actionAll() {
        
    }

    public function renderAll() {
        $this->template->punishments =
            $this->punishmentsRepository->findByValue('archive_id', null)->order('id DESC');

        $this['breadCrumb']->addLink("Hráči", $this->link("Stats:all"));
        $this['breadCrumb']->addLink("Tresty hráčov");

        if ($this->user->isLoggedIn()) {
            $this->getComponent("addForm");
        }
    }

    public function actionEdit($id) {
        $this->userIsLogged();
        $this->punishmentRow = $this->punishmentsRepository->findById($id);
    }

    public function renderEdit($id) {
        $this->template->player = $this->punishmentRow->ref('players', 'player_id');
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

    public function actionArchView($id) {
        $this->archRow = $this->archiveRepository->findById($id);
    }

    public function renderArchView($id) {
        $this['breadCrumb']->addLink("Archív", $this->link("Archive:all"));
        $this['breadCrumb']->addLink($this->archRow->title, $this->link("Archive:view", $this->archRow));
        $this['breadCrumb']->addLink("Tresty hráčov");

        $this->template->archive = $this->archRow;
        $this->template->punishments = $this->punishmentsRepository->findByValue('archive_id', $id);
    }

    protected function createComponentEditPunishmentForm() {
        $form = new Form;
        $form->addText('text', 'Dôvod');
        $form->addText('round', 'Kolá');
        $form->addCheckbox('condition', ' Podmienka');
        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = $this->submittedEditPunishmentForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentAddForm() {
        $players = $this->playersRepository->getPlayersByValue('num !=', 0);
        $form = new Form;
        $form->addSelect('player_id', 'Hráč', $players)
                ->setRequired();
        $form->addText('text', 'Dôvod');
        $form->addText('round', 'Kolá');
        $form->addCheckbox('condition', ' Podmienka');
        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = $this->submittedAddForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedEditPunishmentForm(Form $form) {
        $values = $form->getValues();
        $this->punishmentRow->update($values);
        $this->redirect('all');
    }

    public function submittedAddForm(Form $form) {
        $values = $form->getValues();
        $this->punishmentsRepository->insert($values);
        $this->redirect('all');
    }

    public function submittedDeleteForm() {
        $this->punishmentRow->delete();
        $this->flashMessage('Trest bol odstránený', 'success');
        $this->redirect('all');
    }

    public function formCancelled() {
        $this->redirect('all');
    }

}
