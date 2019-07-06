<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\FormHelper;
use App\Model\LinksRepository;
use App\Model\SponsorsRepository;
use App\Model\TeamsRepository;
use App\Model\AlbumsRepository;
use App\Model\ImagesRepository;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\FileSystem;
use Nette\IOException;
use Nette\InvalidArgumentException;
use Nette\Utils\ArrayHash;

class AlbumsPresenter extends BasePresenter
{
  const ALBUM_NOT_FOUND = 'Album not found';
  const IMAGE_NOT_FOUND = 'Image not found';

  /** @var ActiveRow */
  private $albumRow;

  /** @var ActiveRow */
  private $imageRow;

  /** @var SeasonsRepository */
  private $albumsRepository;

  /** @var ImagesRepository */
  private $imagesRepository;

  public function __construct(
    LinksRepository $linksRepository,
    SponsorsRepository $sponsorsRepository,
    TeamsRepository $teamsRepository,
    AlbumsRepository $albumsRepository,
    ImagesRepository $imagesRepository
  )
  {
    parent::__construct($linksRepository, $sponsorsRepository, $teamsRepository);
    $this->albumsRepository = $albumsRepository;
    $this->imagesRepository = $imagesRepository;
  }

  /**
   * Passes prepared data to template
   */
  public function renderAll(): void
  {
    $this->template->albums = $this->albumsRepository->getAll();
  }

  /**
   * Loads album data
   * @param int $id
   */
  public function actionView(int $id): void
  {
    $this->albumRow = $this->albumsRepository->findById($id);

    if (!$this->albumRow || !$this->albumRow->is_present) {
      throw new BadRequestException(self::ALBUM_NOT_FOUND);
    }

    if ($this->user->isLoggedIn()) {
      $this->getComponent('albumForm')->setDefaults($this->albumRow);
    }
  }

  /**
   * @param int $id
   */
  public function renderView(int $id): void
  {
    $this->template->album = $this->albumRow;
    $this->template->images = $this->imagesRepository->getForAlbum($this->albumRow->id);
  }

  /**
   * @param int $album_id
   * @param int $id
   */
  public function actionSetImage(int $albumId, int $id): void
  {
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
   * @param int $id
   * @throws Nette\Application\BadRequestException
   */
  public function actionRemoveImage(int $id): void
  {
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
  protected function createComponentAlbumForm(): Form
  {
    $form = new Form;
    $form->addText('name', 'Názov')
          ->addRule(Form::FILLED, 'Názov je povinné pole.')
          ->setAttribute('placeholder', 'Finále SAHL 2018/19');
    $form->addSubmit('save', 'Uložiť');
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, 'submittedAlbumForm'];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  /**
   * Creates add image form
   * @return Nette\Application\UI\Form
   */
  protected function createComponentAddImageForm(): Form
  {
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
   * @param ArrayHash $values
   */
  public function submittedAlbumForm(Form $form, ArrayHash $values): void
  {
    $id = $this->getParameter('id');

    if ($id) {
      $this->albumRow->update($values);
    } else {
      $this->albumRow = $this->albumsRepository->insert($values);
    }

    $this->flashMessage(self::CHANGES_SAVED_SUCCESSFULLY, self::SUCCESS);
    $this->redirect('view', $this->albumRow->id);
  }

  /***
   * Removes albums and related records from database
   */
  public function submittedRemoveForm(): void
  {
    $images = $this->imagesRepository->getForAlbum($this->albumRow->id);

    foreach ($images as $image) {
      $this->imagesRepository->remove($image->id);
    }

    $this->albumsRepository->remove($this->albumRow->id);
    $this->flashMessage('Album bol odstránený', self::SUCCESS);
    $this->redirect('all');
  }

  /**
   * Removes an image from database and filesystem
   */
  public function submittedRemoveImage(): void
  {
    $this->imagesRepository->remove($this->imageRow->id);
    $this->flashMessage('Obrázok bol odstránený', self::SUCCESS);
    $this->redirect('Albums:view', $this->imageRow->album_id);
  }

  public function submittedSetImage(): void
  {
    $data['thumbnail'] = $this->imageRow->name;
    $this->albumRow->update($data);
    $this->flashMessage('Miniatúra bola nastavená', self::SUCCESS);
    $this->redirect('all');
  }

  /**
   * Adds image into database and filesystem
   *
   * @param Nette\Application\UI\Form $form
   * @param ArrayHash $values
   */
  public function submittedAddImageForm(Form $form, ArrayHash $values): void
  {
    $data = array();

    foreach ($values->files as $image) {
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
    $this->redirect('Albums:view', $this->albumRow->id);
  }

}
