<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Forms\Controls\SubmitButton;

class LinksPresenter extends BasePresenter {

  const LINK_NOT_FOUND = 'Link not found';

  /** @var ActiveRow */
  private $linkRow;

  public function actionAll(): void
  {
    $this->userIsLogged();
  }

  public function renderAll(): void
  {
    $this->template->links = $this->linksRepository->getAll();
  }

  public function actionRemove($id): void
  {
    $this->userIsLogged();
    $this->linkRow = $this->linksRepository->findById($id);
    if (!$this->linkRow) {
      throw new BadRequestException(self::LINK_NOT_FOUND);
    }
  }

  public function renderRemove($id): void
  {
    $this->template->link = $this->linkRow;
  }

  public function actionEdit($id): void
  {
    $this->userIsLogged();
    $this->linkRow = $this->linksRepository->findById($id);
    if (!$this->linkRow) {
      throw new BadRequestException(self::LINK_NOT_FOUND);
    }
    $this->getComponent(self::EDIT_FORM)->setDefaults($this->linkRow);
  }

  public function renderEdit($id) {
    $this->template->link = $this->linkRow;
  }

  /**
   * @return Nette\Application\UI\Form
   */
  protected function createComponentAddForm(): Form
  {
    $form = new Form;
    $form->addText('label', 'Názov')
          ->addRule(Form::FILLED, 'Názov je povinné pole');
    $form->addText('url', 'URL adresa');
    $form->addSubmit('save', 'Uložiť');
    $form->onSuccess[] = [$this, self::SUBMITTED_ADD_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  /**
   * @return Nette\Application\UI\Form
   */
  protected function createComponentEditForm(): Form
  {
    $form = new Form;
    $form->addText('label', 'Názov')
          ->addRule(Form::FILLED, 'Názov je povinné pole');
    $form->addText('url', 'URL adresa');
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
   * Component for creating a remove form
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
    $form->addProtection(self::CSRF_TOKEN_EXPIRED);
    $form->onSuccess[] = [$this, self::SUBMITTED_REMOVE_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  /**
   * @param Nette\Application\UI\Form $form
   * @param $values
   */
  public function submittedAddForm(Form $form, $values): void
  {
    $this->linksRepository->insert($values);
    $this->flashMessage('Odkaz bol pridaný', self::SUCCESS);
    $this->redirect('all');
  }

  /**
   * @param Nette\Forms\Control\SubmitButton $button
   * @param $values
   */
  public function submittedEditForm(SubmitButton $button, $values): void
  {
    $this->linkRow->update($values);
    $this->flashMessage('Odkaz bol upravený', self::SUCCESS);
    $this->redirect('all');
  }

  public function submittedRemoveForm(): void
  {
    $this->linksRepository->remove($this->linkRow);
    $this->flashMessage('Odkaz bol odstránený', self::SUCCESS);
    $this->redirect('all');
  }

  public function formCancelled(): void
  {
    $this->redirect('all');
  }

}
