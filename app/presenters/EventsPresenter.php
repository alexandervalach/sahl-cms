<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;

class EventsPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $eventRow;

    /** @var ActiveRow */
    private $archRow; 

    /** @var string */
    private $error = "Event not found!";

    public function actionAll() {
        
    }

    public function renderAll() {
        $this->redrawControl('main');
        $this->template->events = $this->eventsRepository->findByValue('archive_id', null)->order('id DESC');
        $this['breadCrumb']->addLink("Zápasy");

        if ($this->user->isLoggedIn()) {
            $this->getComponent("addForm");
        }
    }

    public function actionEdit($id) {
        $this->userIsLogged();
        $this->eventRow = $this->eventsRepository->findById($id);
    }

    public function renderEdit($id) {
        if (!$this->eventRow) {
            throw new BadRequestException($this->error);
        }
        $this->getComponent('editEventForm')->setDefaults($this->eventRow);
    }

    public function actionDelete($id) {
        $this->userIsLogged();
        $this->eventRow = $this->eventsRepository->findById($id);
    }

    public function renderDelete($id) {
        if (!$this->eventRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->event = $this->eventRow;
        $this->getComponent('deleteForm');
    }

    public function actionArchView($id) {
        $this->archRow = $this->archiveRepository->findById($id);
    }

    public function renderArchView($id) {
        $this->template->archive = $this->archRow;
        $this->template->events = $this->eventsRepository->findByValue('archive_id', $id)->order('id DESC');
        
        $this['breadCrumb']->addLink("Archívy", $this->link("Archive:all"));
        $this['breadCrumb']->addLink($this->archRow->title, $this->link("Archive:view", $this->archRow));
        $this['breadCrumb']->addLink("Zápasy");
    }

    protected function createComponentAddForm() {
        $form = new Form;

        $form->addTextArea('event', 'Rozpis zápasov')
                ->setAttribute('id', 'ckeditor')
                ->setRequired("Rozpis zápasov je povinné pole.");
        $form->addSubmit('save', 'Uložiť');

        $form->onSuccess[] = $this->submittedAddForm;

        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditEventForm() {
        $form = new Form;

        $form->addTextArea('event', 'Rozpis zápasov')
                ->setAttribute('id', 'ckeditor')
                ->setRequired('Rozpis zápasov je povinné pole.');
        $form->addSubmit('save', 'Uložiť');

        $form->onSuccess[] = $this->submittedEditEventForm;

        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddForm(Form $form) {
        $values = $form->getValues();
        $this->eventsRepository->insert($values);
        $this->redirect('all');
    }

    public function submittedEditEventForm(Form $form) {
        $values = $form->getValues();
        $this->eventRow->update($values);
        $this->redirect('all');
    }

    public function submittedDeleteForm() {
        $this->eventRow->delete();
        $this->flashMessage('Rozpis odstránený!', 'success');
        $this->redirect('all');
    }

    public function formCancelled() {
        $this->redirect('all');
    }

}
