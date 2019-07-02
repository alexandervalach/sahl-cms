<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;
use Nette\Forms\Controls\SubmitButton;

class PlayerTypesPresenter extends BasePresenter {

  const TYPE_NOT_FOUND = 'Player type not found';

  /** @var ActiveRow */
  private $playerTypeRow;

  public function actionAll(): void
  {
    $this->userIsLogged();
  }

  public function renderAll(): void
  {
    $this->template->types = $this->playerTypesRepository->getAll();
  }

  public function actionEdit($id): void
  {
    $this->userIsLogged();
    $this->playerTypeRow = $this->playerTypesRepository->findById($id);

    if (!$this->playerTypeRow || !$this->playerTypeRow->is_present) {
      throw new BadRequestException(self::TYPE_NOT_FOUND);
    }

    $this->getComponent(self::EDIT_FORM)->setDefaults($this->playerTypeRow);
  }

  public function renderEdit($id): void
  {
    $this->template->type = $this->playerTypeRow;
  }

  public function actionRemove($id): void
  {
    $this->userIsLogged();
    $this->playerTypeRow = $this->playerTypesRepository->findById($id);

    if (!$this->playerTypeRow || !$this->playerTypeRow->is_present) {
      throw new BadRequestException(self::TYPE_NOT_FOUND);
    }
  }

  public function renderRemove($id): void
  {
    $this->template->type = $this->playerTypeRow;
  }

  /**
   * @return Nette\Application\UI\Form
   */
  protected function createComponentAddForm(): Form
  {
    $form = new Form;
    $form->addText('label', 'Typ hráča')
          ->addRule(Form::FILLED, 'Ešte vyplňte názov')
          ->addRule(Form::MAX_LENGTH, 'Názov môže mať len 50 znakov.', 50);
    $form->addText('abbr', 'Skratka');
    $form->addSubmit('save', 'Uložiť');
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    FormHelper::setBootstrapFormRenderer($form);
    $form->onSuccess[] = [$this, self::SUBMITTED_ADD_FORM];
    return $form;
  }

  /**
   * @return Nette\Application\UI\Form
   */
  protected function createComponentEditForm(): Form
  {
    $form = new Form;
    $form->addText('label', 'Typ hráča')
        ->addRule(Form::FILLED, 'Ešte vyplňte názov')
        ->addRule(Form::MAX_LENGTH, 'Názov môže mať len 50 znakov.', 50);
    $form->addText('abbr', 'Skratka');
    $form->addSubmit('save', 'Uložiť')
          ->setAttribute('class', self::BTN_SUCCESS)
          ->onClick[] = [$this, self::SUBMITTED_EDIT_FORM];
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->onClick[] = [$this, 'formCancelled'];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  /**
   * @return Nette\Application\UI\Form
   */
  protected function createComponentRemoveForm(): Form
  {
    $form = new Form;
    $form->addSubmit('save', 'Odstrániť')
          ->setAttribute('class', self::BTN_DANGER)
          ->onClick[] = [$this, self::SUBMITTED_REMOVE_FORM];
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->onClick[] = [$this, 'formCancelled'];
    return $form;
  }

  /**
   * @param Nette\Application\UI\Form $form
   * @param $values
   */
  public function submittedAddForm(Form $form, $values): void
  {
    $this->playerTypesRepository->insert($values);
    $this->flashMessage('Typ hráča bol pridaný', self::SUCCESS);
    $this->redirect('all');
  }

  /**
   * @param Nette\Forms\Controls\SubmitButton $button
   * @param $values
   */
  public function submittedEditForm(SubmitButton $button, $values): void
  {
    $this->playerTypeRow->update($values);
    $this->flashMessage('Typ hráča bol upravený', self::SUCCESS);
    $this->redirect('all');
  }

  /**
   *
   */
  public function submittedRemoveForm(): void
  {
    $this->playerTypesRepository->remove($this->playerTypeRow);
    $this->flashMessage('Typ hráča bol odstránený', self::SUCCESS);
    $this->redirect('all');
  }

  /**
   *
   */
  public function formCancelled(): void
  {
    $this->redirect('all');
  }
}
