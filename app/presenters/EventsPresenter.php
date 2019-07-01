<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\ArrayHash;

class EventsPresenter extends BasePresenter {

    /* String constant fro debugging purpose */
    const EVENT_NOT_FOUND = 'Event not found';

    /** @var ActiveRow */
    private $eventRow;

    /** @var ActiveRow */
    private $seasonRow;

    /**
     * Renders data for all view
     */
    public function renderAll() {
      $this->template->events = $this->eventsRepository->getArchived()->order('id DESC');
    }

    /**
     * Authenticates user and loads data from repository
     * @param $id
     */
    public function actionEdit($id) {
      $this->userIsLogged();
      $this->eventRow = $this->eventsRepository->findById($id);

      if (!$this->eventRow || !$this->eventRow->is_present) {
        throw new BadRequestException(self::EVENT_NOT_FOUND);
      }

      if ($this->user->isLoggedIn()) {
        $this->getComponent(self::EDIT_FORM)->setDefaults($this->eventRow);
      }
    }

    /**
     * Passes data to template
     * @param $id
     */
    public function renderEdit($id) {
      $this->template->event = $this->eventRow;
    }

    /**
     * Removes a record from database
     */
    public function actionRemove($id) {
      $this->userIsLogged();
      $this->eventRow = $this->eventsRepository->findById($id);
      if (!$this->eventRow) {
        throw new BadRequestException(self::EVENT_NOT_FOUND);
      }
    }

    /**
     * Checks whether event exists and passes data to template
     */
    public function renderRemove($id) {
      $this->template->event = $this->eventRow;
    }

    /**
     * Get data for event arch page
     */
    public function actionArchAll($id) {
      $this->seasonRow = $this->seasonsRepository->findById($id);
    }

    /**
     * Renders arch view page
     */
    public function renderArchAll($id) {
      $this->template->archive = $this->seasonRow;
      $this->template->events = $this->eventsRepository->getAll($id)->order('id DESC');
    }

    /**
     * Creates add form components
     *
     * @return Nette\Application\UI\Form
     */
    protected function createComponentAddForm(): Form
    {
      $form = new Form;
      $form->addTextArea('content', 'Obsah')
          ->setAttribute('id', 'ckeditor');
      $form->addSubmit('save', 'Uložiť');
      $form->onSuccess[] = [$this, self::SUBMITTED_ADD_FORM];
      FormHelper::setBootstrapFormRenderer($form);
      return $form;
    }

    /**
     * Creates edit form component
     *
     * @return Nette\Application\UI\Form
     */
    protected function createComponentEditForm(): Form
    {
      $form = new Form;
      $form->addTextArea('content', 'Obsah')
          ->setAttribute('id', 'ckeditor');
      $form->addSubmit('save', 'Uložiť');
      $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
      FormHelper::setBootstrapFormRenderer($form);
      return $form;
    }

    /**
     * Renders remove form component
     *
     * @return Nette\Application\UI\Form
     */
    protected function createComponentRemoveForm(): Form
    {
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

    /**
     * Sends form data
     *
     * @param Nette\Application\UI\Form $form
     * @param Nette\Utils\ArrayHash $values
     */
    public function submittedAddForm(Form $form, ArrayHash $values) {
        $this->eventsRepository->insert($values);
        $this->flashMessage('Rozpis bol pridaný', self::SUCCESS);
        $this->redirect('all');
    }

    /**
     * Sends edit form data
     *
     * @param Nette\Application\UI\Form
     * @param array $values
     */
    public function submittedEditForm(Form $form, array $values) {
        $this->eventRow->update($values);
        $this->flashMessage('Rozpis bol upravený', self::SUCCESS);
        $this->redirect('all');
    }

    /**
     * Sends remove form data
     */
    public function submittedRemoveForm() {
        $this->eventRow->delete();
        $this->flashMessage('Rozpis bol odstránený', self::SUCCESS);
        $this->redirect('all');
    }

    /**
     * Redirects user to all page
     */
    public function formCancelled() {
        $this->redirect('all');
    }

}
