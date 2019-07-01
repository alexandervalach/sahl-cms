<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\FileSystem;
use Nette\IOException;
use Nette\InvalidArgumentException;

class PostsPresenter extends BasePresenter {

  const POST_NOT_FOUND = 'Post not found';
  const IMAGE_NOT_FOUND = 'Image not found';
  const ADD_IMG_FORM = 'addImgForm';

  /** @var ActiveRow */
  private $postRow;

  /** @var ActiveRow */
  private $imgRow;

  public function renderAll() {
    $this->template->posts = $this->postsRepository->getAll()->order('id DESC');
  }

  public function actionView($id) {
    $this->postRow = $this->postsRepository->findById($id);

    if (!$this->postRow || !$this->postRow->is_present) {
      throw new BadRequestException(self::POST_NOT_FOUND);
    }

    if ($this->user->isLoggedIn()) {
      $this->getComponent(self::EDIT_FORM)->setDefaults($this->postRow);
    }
  }

  public function renderView($id) {
    $this->template->post = $this->postRow;
    $this->template->images = $this->postsRepository->getImages($this->postRow);
  }

  public function actionSetImg($postId, $id) {
    $this->imgRow = $this->postImagesRepository->findById($id);
    $this->postRow = $this->postsRepository->findById($postId);

    if (!$this->imgRow || !$this->imgRow->is_present) {
      throw new BadRequestException(self::IMAGE_NOT_FOUND);
    }

    if (!$this->postRow || !$this->postRow->is_present) {
      throw new BadRequestException(self::POST_NOT_FOUND);
    }

    $this->submittedSetImgForm();
  }

  public function actionRemoveImg($postId, $id) {
    $this->imgRow = $this->postImagesRepository->findById($id);
    $this->postRow = $this->postsRepository->findById($postId);

    if (!$this->imgRow || !$this->imgRow->is_present) {
      throw new BadRequestException(self::IMAGE_NOT_FOUND);
    }

    if (!$this->postRow || !$this->postRow->is_present) {
      throw new BadRequestException(self::POST_NOT_FOUND);
    }

    $this->submittedRemoveImgForm();
  }

  protected function createComponentAddForm() {
    $form = new Form;
    $form->addText('title', 'Názov')
          ->setAttribute('placeholder', 'Novinka SAHL 2018')
          ->setRequired('Názov je povinné pole.');
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

  protected function createComponentEditForm() {
    $form = new Form;
    $form->addText('title', 'Názov')
          ->setAttribute('placeholder', 'Novinka SAHL 2018')
          ->setRequired('Názov je povinné pole.');
    $form->addTextArea('content', 'Obsah')
          ->setAttribute('id', 'ckeditor');
    $form->addSubmit('save', 'Uložiť');
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  protected function createComponentRemoveForm() {
    $form = new Form;
    $form->addSubmit('delete', 'Odstrániť')
          ->setAttribute('class', self::BTN_DANGER);
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->addProtection(self::CSRF_TOKEN_EXPIRED);
    $form->onSuccess[] = [$this, self::SUBMITTED_REMOVE_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  protected function createComponentAddImgForm() {
    $form = new Form;
    $form->addMultiUpload('images', 'Obrázok');
    $form->addSubmit('upload', 'Nahrať');
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, self::SUBMITTED_ADD_IMG_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  public function submittedAddForm(Form $form, $values) {
    $post = $this->postsRepository->insert($values);
    $this->flashMessage('Príspevok bol pridaný', self::SUCCESS);
    $this->redirect('view', $post);
  }

  public function submittedEditForm(Form $form, $values) {
    $this->postRow->update($values);
    $this->flashMessage('Príspevok bol upravený', self::SUCCESS);
    $this->redirect('view', $this->postRow);
  }

  public function submittedRemoveForm() {
    $images = $this->postsRepository->getImages($this->postRow);

    foreach ($images as $image) {
      $this->postImagesRepository->remove($image);
    }

    $this->postsRepository->remove($this->postRow);
    $this->flashMessage('Príspevok bol odstránený', self::SUCCESS);
    $this->redirect('all');
  }

  public function submittedSetImgForm() {
    $values['thumbnail'] = $this->imgRow->name;
    $this->postRow->update($values);
    $this->flashMessage('Miniatúra bola nastavená', self::SUCCESS);
    $this->redirect('view', $this->postRow);
  }

  public function submittedRemoveImgForm() {
    try {
      FileSystem::delete($this->imageDir . $this->imgRow->name);
      $this->imgRow->delete();
      $this->flashMessage('Obrázok bol odstránený', self::SUCCESS);
    } catch (IOException $e) {
      $this->flashMessage('Obrázok sa nepodarilo odstrániť', self::DANGER);
    }
    $this->redirect('view', $this->postRow);
  }

  public function submittedAddImgForm(Form $form, $values) {
    foreach ($values['images'] as $file) {
      $name = strtolower($file->getSanitizedName());

      if (!$file->isOK() || !$file->isImage()) {
        throw new InvalidArgumentException;
      }

      $file->move($this->imageDir . '/' . $name);
      $data = array('name' => $name, 'post_id' => $this->postRow);
      $this->postImagesRepository->insert($data);
    }
    $this->flashMessage('Obrázky boli pridané', self::SUCCESS);
  }

}
