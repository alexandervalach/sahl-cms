<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;

class RulesPresenter extends BasePresenter {

  /** @var ActiveRow */
  private $ruleRow;

  /** @var ActiveRow */
  private $seasonRow;

  public function actionAll() {
    $this->ruleRow = $this->rulesRepository->getArchived()->order('id DESC')->fetch();

    if (!$this->ruleRow || !$this->ruleRow->is_present) {
      throw new BadRequestException(self::RULE_NOT_FOUND);
    }

    $this->getComponent(self::EDIT_FORM)->setDefaults($this->ruleRow);
  }

  public function renderAll() {
    $this->template->rule = $this->ruleRow;
  }

  public function actionArchView($id) {
    $this->seasonRow = $this->seasonsRepository->findById($id);

    if (!$this->seasonRow || !$this->seasonRow->is_present) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }

    $this->ruleRow = $this->rulesRepository->getArchived($id)->order('id DESC')->fetch();
  }

  public function renderArchView($id) {
    $this->template->rule = $this->ruleRow;
    $this->template->season = $this->seasonRow;
  }

  protected function createComponentEditForm() {
    $form = new Form;
    $form->addTextArea('content', 'Obsah')
          ->setAttribute('id', 'ckeditor');
    $form->addSubmit('save', 'Uložiť')
          ->setAttribute('class', self::BTN_SUCCESS);
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  public function submittedEditForm(Form $form, $values) {
    $this->ruleRow->update($values);
    $this->flashMessage('Pravidlá a smernice boli upravené', self::SUCCESS);
    $this->redirect('all');
  }
}
