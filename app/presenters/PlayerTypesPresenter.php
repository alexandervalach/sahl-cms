<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;

class PlayerTypesPresenter extends BasePresenter {

  const TYPE_NOT_FOUND = 'Player type not found';

  /** @var ActiveRow */
  private $playerTypeRow;

  public function actionAll() {
    $this->userIsLogged();
  }

  public function renderAll() {
    $this->template->types = $this->playerTypesRepository->getAll();
  }

  public function actionEdit($id) {
    $this->userIsLogged();
    $this->playerTypeRow = $this->playerTypesRepository->findById($id);

    if (!$this->playerTypeRow || !$this->playerTypeRow->is_present) {
      throw new BadRequestException(self::TYPE_NOT_FOUND);
    }
  }

  public function renderEdit($id) {
    $this->getComponent(self::EDIT_FORM)->setDefaults($this->playerTypeRow);
    $this->template->type = $this->playerTypeRow;
  }

  public function actionRemove($id) {
    $this->userIsLogged();
    $this->playerTypeRow = $this->playerTypesRepository->findById($id);

    if (!$this->playerTypeRow || !$this->playerTypeRow->is_present) {
      throw new BadRequestException(self::TYPE_NOT_FOUND);
    }
  }

  public function renderRemove($id) {
    $this->template->type = $this->playerTypeRow;
  }

  protected function createComponentAddForm() {
    $form = new Form;
    $form->addText('label', 'Typ hráča')
          ->setAttribute('placeholder', 'Kapitán')
          ->addRule(Form::FILLED, 'Ešte vyplňte názov')
          ->addRule(Form::MAX_LENGTH, 'Názov môže mať len 50 znakov.', 50);
    $form->addText('abbr', 'Skratka')
          ->setAttribute('placeholder', 'C');
    $form->addSubmit('save', 'Uložiť');
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    FormHelper::setBootstrapFormRenderer($form);
    $form->onSuccess[] = [$this, self::SUBMITTED_ADD_FORM];
    return $form;
  }

  protected function createComponentEditForm() {
    $form = new Form;
    $form->addText('label', 'Typ hráča')
        ->setAttribute('placeholder', 'Kapitán')
        ->addRule(Form::FILLED, 'Ešte vyplňte názov')
        ->addRule(Form::MAX_LENGTH, 'Názov môže mať len 50 znakov.', 50);
    $form->addText('abbr', 'Skratka')
          ->setAttribute('placeholder', 'C');
    $form->addSubmit('save', 'Upraviť')
          ->setAttribute('class', self::BTN_SUCCESS);
    FormHelper::setBootstrapFormRenderer($form);
    $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
    return $form;
  }

  protected function createComponentRemoveForm() {
    $form = new Form;
    $form->addSubmit('save', 'Odstrániť')
          ->setAttribute('class', self::BTN_DANGER)
          ->onClick[] = [$this, self::SUBMITTED_REMOVE_FORM];
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->onClick[] = [$this, 'formCancelled'];
    return $form;
  }

  public function submittedAddForm(Form $form, $values) {
    $this->playerTypesRepository->insert($values);
    $this->flashMessage('Typ hráča bol pridaný', self::SUCCESS);
    $this->redirect('all');
  }

  public function submittedEditForm(Form $form, $values) {
    $this->playerTypeRow->update($values);
    $this->flashMessage('Typ hráča bol upravený', self::SUCCESS);
    $this->redirect('all');
  }

  public function submittedRemoveForm() {
    $this->playerTypesRepository->remove($this->playerTypeRow);
    $this->flashMessage('Typ hráča bol odstránený', self::SUCCESS);
    $this->redirect('all');
  }

  public function formCancelled() {
    $this->redirect('all');
  }

}
