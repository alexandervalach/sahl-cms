<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\FileSystem;
use Nette\IOException;
use Nette\InvalidArgumentException;

class AlbumsPresenter extends BasePresenter {

  const ALBUM_NOT_FOUND = 'Album not found';
  const IMAGE_NOT_FOUND = 'Image not found';

  /** @var ActiveRow */
  private $albumRow;

  /** @var ActiveRow */
  private $imageRow;

  /**
   * Passes prepared data to template
   */
  public function renderAll() {
    $this->template->albums = $this->albumsRepository->getAll();
  }

  /**
   * Loads album data
   *
   * @param ActiveRow|string $id
   */
  public function actionView($id) {
    $this->albumRow = $this->albumsRepository->findById($id);

    if (!$this->albumRow || !$this->albumRow->is_present) {
      throw new BadRequestException(self::ALBUM_NOT_FOUND);
    }

    if ($this->user->isLoggedIn()) {
      $this->getComponent(self::EDIT_FORM)->setDefaults($this->albumRow);
    }
  }

  /**
   * @param string $id
   */
  public function renderView(string $id) {
    $this->template->album = $this->albumRow;
    $this->template->images = $this->imagesRepository->getForAlbum($this->albumRow);
  }

  /**
   * @param int $album_id
   * @param ActiveRow|string $id
   */
  public function actionSetImage(int $albumId, $id) {
    $this->userIsLogged();
    $this->albumRow = $this->albumsRepository->findById($albumId);
    $this->imageRow = $this->imagesRepository->findById($id);

    if (!$this->albumRow || !$this->albumRow->is_present) {
      throw new BadRequestException(self::ALBUM_NOT_FOUND);
    }

    if (!$this->imageRow || !$this->imageRow->is_present) {
      throw new BadRequestException(self::IMAGE_NOT_FOUND);
    }

    $this->submittedSetImage();
  }

  /**
   * @param ActiveRow|string $id
   */
  public function actionRemoveImage($id) {
    $this->userIsLogged();
    $this->imageRow = $this->imagesRepository->findById($id);
    if (!$this->imageRow) {
        throw new BadRequestException(self::IMAE_NOT_FOUND);
    }
    $this->submittedRemoveImage();
  }

  /**
   * Creates add album form
   * @return Nette\Application\UI\Form
   */
  protected function createComponentAddForm()
  {
    $form = new Form;
    $form->addText('name', 'Názov')
          ->setRequired('Názov je povinné pole.')
          ->setAttribute('placeholder', 'Finále SAHL 2018/19');
    $form->addSubmit('save', 'Uložiť');
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, self::SUBMITTED_ADD_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  /**
   * Creates edit album form
   * @return Nette\Application\UI\Form
   */
  protected function createComponentEditForm() {
    $form = new Form;
    $form->addText('name', 'Názov')
          ->setRequired('Názov je povinné pole')
          ->setAttribute('placeholder', 'Finále SAHL 2018/19');
    $form->addSubmit('edit', 'Upraviť')
          ->setAttribute('class', self::BTN_SUCCESS);
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  /**
   * Creates remove album form
   * @return Nette\Application\UI\Form
   */
  protected function createComponentRemoveForm() {
    $form = new Form;
    $form->addSubmit('remove', 'Odstrániť')
          ->setAttribute('class', self::BTN_DANGER);
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->addProtection();
    $form->onSuccess[] = [$this, self::SUBMITTED_REMOVE_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  /**
   * Creates add image form
   * @return Nette\Application\UI\Form
   */
  protected function createComponentAddImageForm() {
    $form = new Form;
    $form->addMultiUpload('files', 'Obrázky')
          ->addRule(Form::FILLED, 'Vyberte obrázky, prosím')
          ->addRule(Form::IMAGE, 'Obrázok môže byť len vo formáte JPEG, PNG alebo GIF.');
    $form->addSubmit('upload', 'Nahrať');
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, self::SUBMITTED_ADD_IMAGE_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  /**
   * Adds form values to database
   *
   * @param Nette\Application\UI\Form $form
   * @param array $values
   */
  public function submittedAddForm(Form $form, array $values) {
    $this->albumsRepository->insert($values);
    $this->flashMessage('Album bol pridaný', self::SUCCESS);
    $this->redirect('all');
  }

  /**
   * Submites edited values to database
   *
   * @param Nette\Application\UI\Form $form
   * @param array $values
   */
  public function submittedEditForm(Form $form, array $values) {
    $this->albumRow->update($values);
    $this->flashMessage('Album bol upravený', self::SUCCESS);
    $this->redirect('view', $this->albumRow);
  }

  /***
   * Removes albums and related records from database
   */
  public function submittedRemoveForm() {
    $images = $this->imagesRepository->getForAlbum($this->albumRow);

    foreach ($images as $image) {
      $this->imagesRepository->remove($image);
    }

    $this->albumsRepository->remove($this->albumRow);
    $this->flashMessage('Album bol odstránený', self::SUCCESS);
    $this->redirect('all');
  }

  /**
   * Removes an image from database and filesystem
   */
  public function submittedRemoveImage() {
    $this->imagesRepository->remove($this->imageRow);
    $this->flashMessage('Obrázok bol odstránený', self::SUCCESS);
    $this->redirect('Albums:view', $this->imageRow->album_id);
  }

  public function submittedSetImage() {
    $data['thumbnail'] = $this->imageRow->name;
    $this->albumRow->update($data);
    $this->flashMessage('Miniatúra bola nastavená', self::SUCCESS);
    $this->redirect('all');
  }

  /**
   * Adds image into database and filesystem
   *
   * @param Nette\Application\UI\Form $form
   * @param array $values
   */
  public function submittedAddImageForm(Form $form, $values) {
    $data = array();

    foreach ($values['files'] as $image) {
      $name = strtolower($image->getSanitizedName());
      $data = array(
        'name' => $name,
        'album_id' => $this->albumRow
      );

      if (!$image->isOk() OR !$image->isImage()) {
        throw new InvalidArgumentException;
      }

      if (!$image->move($this->imageDir . '/' . $name)) {
        throw new IOException;
      }

      $this->imagesRepository->insert($data);
    }

    $this->flashMessage('Obrázky boli pridané', self::SUCCESS);
    $this->redirect('Albums:view', $this->albumRow);
  }

}
