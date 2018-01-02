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

    public function renderAll() {
        if ($this->user->loggedIn) {
            $this->getComponent("addForm");
        }
        $this->template->events = $this->eventsRepository->findByValue('archive_id', null)->order('id DESC');
        $this['breadCrumb']->addLink("Zápasy");
    }

    public function actionEdit($id) {
        $this->userIsLogged();
        $this->eventRow = $this->eventsRepository->findById($id);
    }

    public function renderEdit($id) {
        if (!$this->eventRow) {
            throw new BadRequestException($this->error);
        }
        $this->getComponent('editForm')->setDefaults($this->eventRow);
    }

    public function actionRemove($id) {
        $this->userIsLogged();
        $this->eventRow = $this->eventsRepository->findById($id);
    }

    public function renderRemove($id) {
        if (!$this->eventRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->event = $this->eventRow;
        $this->getComponent('removeForm');
    }

    public function actionArchView($id) {
        $this->archRow = $this->archivesRepository->findById($id);
    }

    public function renderArchView($id) {
        $this->template->archive = $this->archRow;
        $this->template->events = $this->eventsRepository->findByValue('archive_id', $id)->order('id DESC');
        
        $this['breadCrumb']->addLink("Archívy", $this->link("Archives:all"));
        $this['breadCrumb']->addLink($this->archRow->title, $this->link("Archives:view", $this->archRow));
        $this['breadCrumb']->addLink("Zápasy");
    }

    protected function createComponentAddForm() {
        $form = new Form;
        $form->addTextArea('event', 'Rozpis zápasov')
             ->setAttribute('id', 'ckeditor')
             ->setRequired("Rozpis zápasov je povinné pole.");
        $form->addSubmit('add', 'Pridať');
        $form->onSuccess[] = [$this, 'submittedAddForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditForm() {
        $form = new Form;
        $form->addTextArea('event', 'Rozpis zápasov')
             ->setAttribute('id', 'ckeditor')
             ->setRequired('Rozpis zápasov je povinné pole.');
        $form->addSubmit('edit', 'Upraviť')
             ->setAttribute('class', 'btn btn-large btn-success');
        $form->onSuccess[] = [$this, 'submittedEditForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentRemoveForm() {
        $form = new Form;
        $form->addSubmit('cancel', 'Zrušiť')
             ->setAttribute('class', 'btn btn-large btn-warning')
             ->onClick[] = $this->formCancelled;
        $form->addSubmit('delete', 'Odstrániť')
             ->setAttribute('class', 'btn btn-large btn-danger')
             ->onClick[] = $this->submittedRemoveForm;
        $form->addProtection();
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddForm(Form $form, $values) {
        $this->eventsRepository->insert($values);
        $this->flashMessage('Rozpis bol pridaný', 'success');
        $this->redirect('all');
    }

    public function submittedEditForm(Form $form, $values) {
        $this->eventRow->update($values);
        $this->flashMessage('Rozpis bol upravený', 'success');
        $this->redirect('all');
    }

    public function submittedRemoveForm() {
        $this->eventRow->delete();
        $this->flashMessage('Rozpis bol odstránený', 'success');
        $this->redirect('all');
    }

    public function formCancelled() {
        $this->redirect('all');
    }

}
