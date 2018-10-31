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

        if ($this->user->isLoggedIn()) {
            $this->getComponent(self::ADD_FORM);
        }
    }

    public function actionEdit($id) {
        $this->userIsLogged();
        $this->punishmentRow = $this->punishmentsRepository->findById($id);
    }

    public function renderEdit($id) {
        $this->template->player = $this->punishmentRow->ref('players', 'player_id');
        $this->getComponent(self::EDIT_FORM)->setDefaults($this->punishmentRow);
    }

    public function actionRemove($id) {
        $this->userIsLogged();
        $this->punishmentRow = $this->punishmentsRepository->findById($id);
    }

    public function renderRemove($id) {
        $this->template->punishment = $this->punishmentRow;
        $this->getComponent(self::REMOVE_FORM);
    }

    public function actionArchAll($id) {
        $this->archRow = $this->archivesRepository->findById($id);
    }

    public function renderArchAll($id) {
        $this->template->archive = $this->archRow;
        $this->template->punishments = $this->punishmentsRepository->findByValue('archive_id', $id);
    }

    /**
     * @return Nette\Application\UI\Form;
     */
    protected function createComponentEditForm() {
        $form = new Form;
        $form->addText('text', 'Dôvod')
             ->setAttribute('placeholder', 'Nešportové správanie');
        $form->addText('round', 'Kolá')
             ->setAttribute('placeholder', '3. kolo');
        $form->addCheckbox('condition', ' Podmienka');
        $form->addSubmit('save', 'Uložiť');
        $form->addSubmit('cancel', 'Zrušiť')
             ->setAttribute('class', self::BTN_WARNING)
             ->setAttribute('data-dismiss', 'modal');
        $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentAddForm() {
        $players = $this->playersRepository->getNonEmptyPlayers();
        $form = new Form;
        $form->addSelect('player_id', 'Hráč', $players);
        $form->addText('text', 'Dôvod')
             ->setAttribute('placeholder', 'Nešportové správanie');
        $form->addText('round', 'Stop na kolo')
             ->setAttribute('placeholder', '3. kolo');
        $form->addCheckbox('condition', ' Podmienka');
        $form->addSubmit('save', 'Uložiť');
        $form->addSubmit('cancel', 'Zrušiť')
             ->setAttribute('class', self::BTN_WARNING)
             ->setAttribute('data-dismiss', 'modal');
        $form->onSuccess[] = [$this, self::SUBMITTED_ADD_FORM];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedEditForm(Form $form, $values) {
        $this->punishmentRow->update($values);
        $this->flashMessage('Trest bol upravený', self::SUCCESS);
        $this->redirect('all');
    }

    public function submittedAddForm(Form $form, $values) {
        $this->punishmentsRepository->insert($values);
        $this->flashMessage('Trest bol pridaný', self::SUCCESS);
        $this->redirect('all');
    }

    public function submittedRemoveForm() {
        $this->punishmentRow->delete();
        $this->flashMessage('Trest bol odstránený', self::SUCCESS);
        $this->redirect('all');
    }

    public function formCancelled() {
        $this->redirect('all');
    }

}
