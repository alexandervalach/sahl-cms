<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\FileSystem;

class SponsorsPresenter extends BasePresenter {

  const ITEM_NOT_FOUND = 'Sponsor not found';

  /** @var ActiveRow */
  private $sponsorRow;

  public function actionAll() {
    $this->userIsLogged();
  }

  public function renderAll() {
    $this->template->sponsors = $this->sponsorsRepository->getAll();
  }

  public function actionRemove($id) {
    $this->userIsLogged();
    $this->sponsorRow = $this->sponsorsRepository->findById($id);
    if (!$this->sponsorRow || !$this->sponsorRow->is_present) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }
  }

  public function renderRemove($id) {
    $this->template->sponsor = $this->sponsorRow;
  }

  public function actionEdit($id) {
    $this->userIsLogged();
    $this->sponsorRow = $this->sponsorsRepository->findById($id);
    if (!$this->sponsorRow || !$this->sponsorRow->is_present) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }
    $this->getComponent(self::EDIT_FORM)->setDefaults($this->sponsorRow);
  }

  public function renderEdit($id) {
    $this->template->sponsor = $this->sponsorRow;
  }

  protected function createComponentAddForm() {
    $form = new Form;
    $form->addText('label', 'Názov')
          ->setAttribute('placeholder', 'Mesto Spišská Nová Ves')
          ->addRule(Form::FILLED, 'Názov je povinné pole');
    $form->addText('url', 'URL adresa')
          ->setAttribute('placeholder', 'http://www.spisskanovaves.eu')
          ->addRule(Form::FILLED, 'URL adresa je povinné pole.');
    $form->addUpload('image', 'Obrázok');
    $form->addSubmit('save', 'Uložiť');
    $form->onSuccess[] = [$this, self::SUBMITTED_ADD_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  protected function createComponentEditForm() {
    $form = new Form;
    $form->addText('label', 'Názov')
          ->setAttribute('placeholder', 'Mesto Spišská Nová Ves')
          ->addRule(Form::FILLED, 'Názov je povinné pole');
    $form->addText('url', 'URL adresa')
          ->setAttribute('placeholder', 'http://www.spisskanovaves.eu')
          ->addRule(Form::FILLED, 'URL adresa je povinné pole.');
    $form->addSubmit('save', 'Uložiť')
          ->setAttribute('class', 'btn btn-large btn-success');
    $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  /**
   * Component for creating a remove form
   * @return Nette\Application\UI\Form
   */
  protected function createComponentRemoveForm() {
    $form = new Form;
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

  public function submittedAddForm(Form $form, $values) {
    $img = $values['image'];

    $name = strtolower($img->getSanitizedName());
    try {
        if ($img->isOk() AND $img->isImage()) {
            $img->move($this->imageDir . '/' . $name);
        }
        $values['image'] = $name;
        $this->sponsorsRepository->insert($values);
        $this->flashMessage('Odkaz bol pridaný', self::SUCCESS);
    } catch (IOException $e) {
        $this->flashMessage('Obrázok ' . $name . ' sa nepodarilo nahrať', self::DANGER);
    }

    $this->redirect('all');
  }

  public function submittedRemoveForm() {
    $this->sponsorsRepository->remove($this->sponsorRow);
    $this->flashMessage('Sponzor bol odstránený', self::SUCCESS);
    $this->redirect('all');
  }

  public function submittedEditForm(Form $form, $values) {
    $this->sponsorRow->update($values);
    $this->flashMessage('Sponzor bol upravený', self::SUCCESS);
    $this->redirect('all');
  }

  public function formCancelled() {
    $this->redirect('all');
  }

}
