<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\FileSystem;
use Nette\Utils\ArrayHash;

class SponsorsPresenter extends BasePresenter
{
  const ITEM_NOT_FOUND = 'Sponsor not found';

  /** @var ActiveRow */
  private $sponsorRow;

  public function actionAll(): void
  {
    $this->userIsLogged();
  }

  public function renderAll(): void
  {
    $this->template->sponsors = $this->sponsorsRepository->getAll();
  }

  public function actionRemove(int $id): void
  {
    $this->userIsLogged();
    $this->sponsorRow = $this->sponsorsRepository->findById($id);
    if (!$this->sponsorRow || !$this->sponsorRow->is_present) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }
  }

  public function renderRemove(int $id): void
  {
    $this->template->sponsor = $this->sponsorRow;
  }

  public function actionEdit(int $id): void
  {
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

  protected function createComponentAddForm(): Form
  {
    $form = new Form;
    $form->addText('label', 'Názov')
          ->addRule(Form::FILLED, 'Názov je povinné pole');
    $form->addText('url', 'URL adresa')
          ->addRule(Form::FILLED, 'URL adresa je povinné pole.');
    $form->addUpload('image', 'Obrázok')
          ->addRule(Form::FILLED, 'Ešte treba doplniť obrázok')
          ->addRule(Form::IMAGE, 'Obrázok môže byť len vo formáte JPEG, PNG alebo GIF')
          ->addRule(Form::MAX_FILE_SIZE, 'Obrázok môže mať len 10 MB', 10 * 1024 * 1024);
    $form->addSubmit('save', 'Uložiť');
    $form->onSuccess[] = [$this, self::SUBMITTED_ADD_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  protected function createComponentEditForm(): Form
  {
    $form = new Form;
    $form->addText('label', 'Názov')
          ->addRule(Form::FILLED, 'Názov je povinné pole');
    $form->addText('url', 'URL adresa')
          ->addRule(Form::FILLED, 'URL adresa je povinné pole.');
    $form->addSubmit('save', 'Uložiť')
          ->setAttribute('class', 'btn btn-large btn-success');
    $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  public function submittedAddForm(Form $form, ArrayHash $values): void
  {
    $this->userIsLogged();
    $img = $values->image;

    $name = strtolower($img->getSanitizedName());
    try {
      if ($img->isOk() AND $img->isImage()) {
        $img->move($this->imageDir . '/' . $name);
      }
      $values->image = $name;
      $this->sponsorsRepository->insert($values);
      $this->flashMessage('Odkaz bol pridaný', self::SUCCESS);
    } catch (IOException $e) {
      $this->flashMessage('Obrázok ' . $name . ' sa nepodarilo nahrať', self::DANGER);
    }

    $this->redirect('all');
  }

  public function submittedRemoveForm(): void
  {
    $this->userIsLogged();
    $this->sponsorsRepository->remove($this->sponsorRow);
    $this->flashMessage('Sponzor bol odstránený', self::SUCCESS);
    $this->redirect('all');
  }

  public function submittedEditForm(Form $form, $values): void
  {
    $this->userIsLogged();
    $this->sponsorRow->update($values);
    $this->flashMessage('Sponzor bol upravený', self::SUCCESS);
    $this->redirect('all');
  }
}
