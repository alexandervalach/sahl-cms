<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;
use Nette\Forms\Controls\SubmitButton;

class GroupsPresenter extends BasePresenter {

  const TYPE_NOT_FOUND = 'Player type not found';

  /** @var ActiveRow */
  private $groupRow;

  public function actionAll() {
    $this->userIsLogged();
  }

  public function renderAll() {
    $this->template->groups = $this->groupsRepository->getAll();
  }

  public function actionEdit($id) {
    $this->userIsLogged();
    $this->groupRow = $this->groupsRepository->findById($id);

    if (!$this->groupRow || !$this->groupRow->is_present) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }

    $this->getComponent(self::EDIT_FORM)->setDefaults($this->groupRow);
  }

  public function renderEdit($id) {
    $this->template->group = $this->groupRow;
  }

  public function actionRemove($id) {
    $this->userIsLogged();
    $this->groupRow = $this->groupsRepository->findById($id);

    if (!$this->groupRow || !$this->groupRow->is_present) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }
  }

  public function renderRemove($id) {
    $this->template->group = $this->groupRow;
  }

  protected function createComponentAddForm() {
    $form = new Form;
    $form->addText('label', 'Názov')
          ->setAttribute('placeholder', 'Skupina A')
          ->addRule(Form::FILLED, 'Ešte vyplňte názov')
          ->addRule(Form::MAX_LENGTH, 'Názov môže mať len 50 znakov.', 50);
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
    $form->addText('label', 'Názov')
        ->setAttribute('placeholder', 'Skupina A')
        ->addRule(Form::FILLED, 'Ešte vyplňte názov')
        ->addRule(Form::MAX_LENGTH, 'Názov môže mať len 50 znakov.', 50);
    $form->addSubmit('save', 'Uložiť')
          ->setAttribute('class', self::BTN_SUCCESS)
          ->onClick[] = [$this, self::SUBMITTED_EDIT_FORM];
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->onClick[] = [$this, 'formCancelled'];
    FormHelper::setBootstrapFormRenderer($form);
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
    $this->groupsRepository->insert($values);
    $this->flashMessage('Skupina bol pridaná', self::SUCCESS);
    $this->redirect('all');
  }

  public function submittedEditForm(SubmitButton $button, $values) {
    $this->groupRow->update($values);
    $this->flashMessage('Skupina bola upravená', self::SUCCESS);
    $this->redirect('all');
  }

  public function submittedRemoveForm() {
    $this->groupsRepository->remove($this->groupRow);
    $this->flashMessage('Skupina bola odstránená', self::SUCCESS);
    $this->redirect('all');
  }

  public function formCancelled() {
    $this->redirect('all');
  }
}
