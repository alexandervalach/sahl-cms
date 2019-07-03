<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;

class TableTypesPresenter extends BasePresenter {

  const TYPE_NOT_FOUND = 'Type not found';

  /** @var ActiveRow */
  private $tableTypeRow;

  public function actionAll() {
    $this->userIsLogged();
  }

  public function renderAll() {
    $this->template->types = $this->tableTypesRepository->getAll();
  }

  public function actionEdit($id) {
    $this->userIsLogged();
    $this->tableTypeRow = $this->tableTypesRepository->findById($id);

    if (!$this->tableTypeRow || !$this->tableTypeRow->is_present) {
      throw new BadRequestException(self::TYPE_NOT_FOUND);
    }

    $this->getComponent(self::EDIT_FORM)->setDefaults($this->tableTypeRow);
  }

  public function renderEdit($id) {
    $this->template->type = $this->tableTypeRow;
  }

  public function actionRemove($id) {
    $this->userIsLogged();
    $this->tableTypeRow = $this->tableTypesRepository->findById($id);

    if (!$this->tableTypeRow || !$this->tableTypeRow->is_present) {
      throw new BadRequestException(self::TYPE_NOT_FOUND);
    }
  }

  public function renderRemove($id) {
    $this->template->type = $this->tableTypeRow;
  }

  /**
   * Creates add table types form
   * @return Nette\Application\UI\Form
   */
  protected function createComponentAddForm() {
    $form = new Form;
    $form->addText('label', 'Názov')
          ->addRule(Form::FILLED, 'Ešte treba vyplniť názov')
          ->setAttribute('placeholder', 'Play Off');
    $form->addSubmit('save', 'Uložiť');
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, self::SUBMITTED_ADD_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }


  /**
   * Creates edit table types form
   * @return Nette\Application\UI\Form
   */
  protected function createComponentEditForm() {
    $form = new Form;
    $form->addText('label', 'Názov')
          ->addRule(Form::FILLED, 'Ešte treba vyplniť názov')
          ->setAttribute('placeholder', 'Play Off');
    $form->addSubmit('save', 'Uložiť')
          ->setAttribute('class', self::BTN_SUCCESS);
    $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  /**
   * Submitting data from add form
   * @param Form $form
   * @param array $values
   */
  public function submittedAddForm(Form $form, $values) {
    $this->tableTypesRepository->insert($values);
    $this->flashMessage('Typ tabuľky bol pridaný', self::SUCCESS);
    $this->redirect('all');
  }

  /**
   * Submitting data from add form
   * @param Form $form
   * @param array $values
   */
  public function submittedEditForm(Form $form, $values) {
    $this->tableTypeRow->update($values);
    $this->flashMessage('Záznam bol upravený', self::SUCCESS);
    $this->redirect('all');
  }

  /**
   * Submits remove table type form
   */
  public function submittedRemoveForm() {
    $this->tableTypesRepository->remove($this->tableTypeRow);
    $this->flashMessage('Typ hráča bol odstránený', self::SUCCESS);
    $this->redirect('all');
  }

  public function formCancelled() {
    $this->redirect('all');
  }

  /**
   * @param integer $id
   */
  public function actionShow($id) {
    $this->userIsLogged();
    $this->tableTypeRow = $this->tableTypesRepository->findById($id);

    if (!$this->tableTypeRow || !$this->tableTypeRow->is_pesent) {
      throw new BadRequestException(self::TYPE_NOT_FOUND);
    }

    $this->submittedShowTable();
  }

  public function submittedShowTable() {
    /*
    $this->tableTypeRow->update(array('visible' => 1));
    $this->flashMessage('Tabuľka je viditeľná', self::SUCCESS);
    */
    $this->redirect('all');
  }

  /**
   * @param integer $id
   */
  public function actionHide($id) {
    $this->userIsLogged();
    $this->tableTypeRow = $this->tableTypesRepository->findById($id);

    if (!$this->tableTypeRow || !$this->tableTypeRow->is_pesent) {
      throw new BadRequestException(self::TYPE_NOT_FOUND);
    }

    $this->submittedHideTable();
  }

  public function submittedHideTable() {
    /*
    $this->tableTypeRow->update(array('visible' => 0));
    $this->flashMessage('Tabuľka je skrytá pre verejnosť', self::SUCCESS);
    */
    $this->redirect('all');
  }
}
