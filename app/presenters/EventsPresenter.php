<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\ArrayHash;
use Nette\Forms\Controls\SubmitButton;

class EventsPresenter extends BasePresenter
{
  /* String constant for debugging purpose */
  const EVENT_NOT_FOUND = 'Event not found';

  /** @var ActiveRow */
  private $eventRow;

  /** @var ActiveRow */
  private $seasonRow;

  /**
   * Renders data for all view
   */
  public function renderAll(): void
  {
    $this->template->events = $this->eventsRepository->getArchived()->order('id DESC');
  }

  /**
   * Authenticates user and loads data from repository
   * @param int $id
   */
  public function actionEdit(int $id): void
  {
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
   * @param int $id
   */
  public function renderEdit(int $id): void
  {
    $this->template->event = $this->eventRow;
  }

  /**
   * Remove action handler
   * @param int $id
   */
  public function actionRemove(int $id): void
  {
    $this->userIsLogged();
    $this->eventRow = $this->eventsRepository->findById($id);

    if (!$this->eventRow || !$this->eventRow->is_present) {
      throw new BadRequestException(self::EVENT_NOT_FOUND);
    }
  }

  /**
   * Passes data to template
   * @param int $id
   */
  public function renderRemove(int $id): void
  {
    $this->template->event = $this->eventRow;
  }

  /**
   * Get data for event arch page
   * @param int $id
   */
  public function actionArchAll(int $id): void
  {
    $this->seasonRow = $this->seasonsRepository->findById($id);
  }

  /**
   * Renders arch view page
   * @param int $id
   */
  public function renderArchAll(int $id): void
  {
    $this->template->archive = $this->seasonRow;
    $this->template->events = $this->eventsRepository->getArchived($id)->order('id DESC');
  }

  /**
   * Creates add form component
   * @return Nette\Application\UI\Form
   */
  protected function createComponentAddForm(): Form
  {
    $form = new Form;
    $form->addTextArea('content', 'Obsah')
        ->setAttribute('id', 'ckeditor');
    $form->addSubmit('save', 'Uložiť');
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, self::SUBMITTED_ADD_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  /**
   * Creates edit form component
   * @return Nette\Application\UI\Form
   */
  protected function createComponentEditForm(): Form
  {
    $form = new Form;
    $form->addTextArea('content', 'Obsah')
        ->setAttribute('id', 'ckeditor');
    $form->addSubmit('save', 'Uložiť')
          ->setAttribute('class', self::BTN_SUCCESS)
          ->onClick[] = [$this, self::SUBMITTED_EDIT_FORM];
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->onClick[] = [$this, 'formCancelled'];
    $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  /**
   * Renders remove form component
   * @return Nette\Application\UI\Form
   */
  protected function createComponentRemoveForm(): Form
  {
    $form = new Form;
    $form->addSubmit('remove', 'Odstrániť')
          ->setAttribute('class', self::BTN_DANGER)
          ->onClick[] = [$this, self::SUBMITTED_REMOVE_FORM];
    $form->addSubmit('cancel', 'Zrušiť')
        ->setAttribute('class', self::BTN_WARNING)
        ->onClick[] = [$this, self::FORM_CANCELLED];
    $form->addProtection(self::CSRF_TOKEN_EXPIRED);
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  /**
   * Insert data to database
   * @param Nette\Application\UI\Form $form
   * @param Nette\Utils\ArrayHash $values
   */
  public function submittedAddForm(Form $form, ArrayHash $values): void
  {
    $this->userIsLogged();
    $this->eventsRepository->insert($values);
    $this->flashMessage('Rozpis bol pridaný', self::SUCCESS);
    $this->redirect('all');
  }

  /**
   * Edits selected row
   * @param SubmitButton $button
   * @param ArrayHash $values
   */
  public function submittedEditForm(SubmitButton $button, ArrayHash $values): void
  {
    $this->userIsLogged();
    $this->eventRow->update($values);
    $this->flashMessage('Rozpis bol upravený', self::SUCCESS);
    $this->redirect('all');
  }

  /**
   * Removes selected row
   */
  public function submittedRemoveForm(): void
  {
    $this->userIsLogged();
    $this->eventsRepository->remove($this->eventRow);
    $this->flashMessage('Rozpis bol odstránený', self::SUCCESS);
    $this->redirect('all');
  }
}
