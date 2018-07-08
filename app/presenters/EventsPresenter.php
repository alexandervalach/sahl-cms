<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;

class EventsPresenter extends BasePresenter {

    const EVENT_NOT_FOUND = 'Event not found';

    /** @var ActiveRow */
    private $eventRow;

    /** @var ActiveRow */
    private $archRow; 


    public function renderAll() {
        $this->template->events = $this->eventsRepository->findByValue('archive_id', null)
                                                         ->order('id DESC');
        if ($this->user->isLoggedIn()) {
            $this->getComponent(self::ADD_FORM);
        }
    }

    public function actionEdit($id) {
        $this->userIsLogged();
        $this->eventRow = $this->eventsRepository->findById($id);
    }

    public function renderEdit($id) {
        if (!$this->eventRow) {
            throw new BadRequestException(self::EVENT_NOT_FOUND);
        }
        if ($this->user->isLoggedIn()) {
            $this->getComponent(self::EDIT_FORM)->setDefaults($this->eventRow);
        }
    }

    public function actionRemove($id) {
        $this->userIsLogged();
        $this->eventRow = $this->eventsRepository->findById($id);
    }

    public function renderRemove($id) {
        if (!$this->eventRow) {
            throw new BadRequestException(self::EVENT_NOT_FOUND);
        }
        $this->template->event = $this->eventRow;
        $this->getComponent(self::REMOVE_FORM);
    }

    public function actionArchAll($id) {
        $this->archRow = $this->archivesRepository->findById($id);
    }

    public function renderArchAll($id) {
        $this->template->archive = $this->archRow;
        $this->template->events = $this->eventsRepository->findByValue('archive_id', $id)
                                                         ->order('id DESC');
    }

    protected function createComponentAddForm() {
        $form = new Form;
        $form->addTextArea('event', 'Rozpis zápasov')
             ->setAttribute('id', 'ckeditor');
        $form->addSubmit('add', 'Pridať');
        $form->onSuccess[] = [$this, self::ADD_FORM];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditForm() {
        $form = new Form;
        $form->addTextArea('event', 'Rozpis zápasov')
             ->setAttribute('id', 'ckeditor');
        $form->addSubmit('edit', 'Upraviť')
             ->setAttribute('class', self::BTN_SUCCESS);
        $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentRemoveForm() {
        $form = new Form;
        $form->addSubmit('cancel', 'Zrušiť')
             ->setAttribute('class', self::BTN_WARNING)
             ->onClick[] = [$this, 'formCancelled'];
        $form->addSubmit('delete', 'Odstrániť')
             ->setAttribute('class', self::BTN_DANGER)
             ->onClick[] = [$this, self::SUBMITTED_REMOVE_FORM];
        $form->addProtection();
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddForm(Form $form, $values) {
        $this->eventsRepository->insert($values);
        $this->flashMessage('Rozpis bol pridaný', self::SUCCESS);
        $this->redirect('all');
    }

    public function submittedEditForm(Form $form, $values) {
        $this->eventRow->update($values);
        $this->flashMessage('Rozpis bol upravený', self::SUCCESS);
        $this->redirect('all');
    }

    public function submittedRemoveForm() {
        $this->eventRow->delete();
        $this->flashMessage('Rozpis bol odstránený', self::SUCCESS);
        $this->redirect('all');
    }

    public function formCancelled() {
        $this->redirect('all');
    }

}
