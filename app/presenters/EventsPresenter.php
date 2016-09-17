<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use IPub\VisualPaginator\Components as VisualPaginator;

class EventsPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $eventRow;

    /** @var string */
    private $error = "Event not found!";

    public function actionAll() {
        
    }

    public function renderAll() {
        $eventSelection = $this->eventsRepository->findByValue('archive_id', null)->order('id DESC');

        $visualPaginator = $this->getComponent('visualPaginator');
        $paginator = $visualPaginator->getPaginator();
        $paginator->itemsPerPage = 5;
        $paginator->itemCount = $eventSelection->count();
        $eventSelection->limit($paginator->itemsPerPage, $paginator->offset);

        $this->template->events = $eventSelection;
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

    public function actionCreate() {
        $this->userIsLogged();
    }

    public function renderCreate() {
        $this->getComponent('addEventForm');
    }

    public function actionArchView($id) {
        
    }

    public function renderArchView($id) {
        $this->template->archive = $this->archiveRepository->findById($id);
        $events = $this->eventsRepository->findByValue('archive_id', $id)->order('id DESC');

        $visualPaginator = $this->getComponent('visualPaginator');
        $paginator = $visualPaginator->getPaginator();
        $paginator->itemsPerPage = 5;
        $paginator->itemCount = $events->count();
        $events->limit($paginator->itemsPerPage, $paginator->offset);

        $this->template->events = $events;
    }

    protected function createComponentAddEventForm() {
        $form = new Form;

        $form->addTextArea('event', 'Rozpis zápasov')
                ->setAttribute('id', 'ckeditor')
                ->setRequired("Rozpis zápasov je povinné pole.");

        $form->addSubmit('save', 'Uložiť');

        $form->onSuccess[] = $this->submittedAddEventForm;

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

    /**
     * Create items paginator
     *
     * @return VisualPaginator\Control
     */
    protected function createComponentVisualPaginator() {
        $control = new VisualPaginator\Control;
        $control->setTemplateFile('bootstrap.latte');
        $control->disableAjax();
        return $control;
    }

    public function submittedAddEventForm(Form $form) {
        $values = $form->getValues();
        $this->eventsRepository->insert($values);
        $this->redirect('all#nav');
    }

    public function submittedEditEventForm(Form $form) {
        $values = $form->getValues();
        $this->eventRow->update($values);
        $this->redirect('all#nav');
    }

    public function submittedDeleteForm() {
        $this->userIsLogged();
        $this->eventRow->delete();
        $this->flashMessage('Rozpis odstránený!', 'success');
        $this->redirect('all#nav');
    }

    public function formCancelled() {
        $this->redirect('all#nav');
    }

}
