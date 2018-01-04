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

    public function renderAll() {
        $this->template->punishments = $this->punishmentsRepository->findByValue('archive_id', null)
                                                                   ->order('id DESC');

        $this['breadCrumb']->addLink("Hráči", $this->link("Players:all"));
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
        $this->getComponent('editForm')->setDefaults($this->punishmentRow);
    }

    public function actionDelete($id) {
        $this->userIsLogged();
        $this->punishmentRow = $this->punishmentsRepository->findById($id);
    }

    public function renderDelete($id) {
        $this->template->punishment = $this->punishmentRow;
        $this->getComponent('deleteForm');
    }

    public function actionArchAll($id) {
        $this->archRow = $this->archivesRepository->findById($id);
    }

    public function renderArchAll($id) {
        $this['breadCrumb']->addLink("Archív", $this->link("Archives:all"));
        $this['breadCrumb']->addLink($this->archRow->title, $this->link("Archives:view", $this->archRow));
        $this['breadCrumb']->addLink("Tresty hráčov");

        $this->template->archive = $this->archRow;
        $this->template->punishments = $this->punishmentsRepository->findByValue('archive_id', $id);
    }

    protected function createComponentEditForm() {
        $form = new Form;
        $form->addText('text', 'Dôvod');
        $form->addText('round', 'Kolá');
        $form->addCheckbox('condition', ' Podmienka');
        $form->addSubmit('edit', 'Upraviť')
             ->setAttribute('class', 'btn btn-large btn-success');
        $form->onSuccess[] = [$this, 'submittedEditForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentAddForm() {
        $players = $this->playersRepository->getNonEmptyPlayers();

        $form = new Form;
        $form->addSelect('player_id', 'Hráč', $players);
        $form->addText('text', 'Dôvod');
        $form->addText('round', 'Kolá');
        $form->addCheckbox('condition', ' Podmienka');
        $form->addSubmit('add', 'Pridať');
        $form->onSuccess[] = [$this, 'submittedAddForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedEditForm(Form $form, $values) {
        $this->punishmentRow->update($values);
        $this->flashMessage('Trest bol pridaný', 'success');
        $this->redirect('all');
    }

    public function submittedAddForm(Form $form, $values) {
        $this->punishmentsRepository->insert($values);
        $this->flashMessage('Trest bol upravený', 'success');
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
