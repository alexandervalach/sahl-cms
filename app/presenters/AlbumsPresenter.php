<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\FormHelper;
use App\Forms\AlbumFormFactory;
use App\Forms\MultiUploadFormFactory;
use App\Forms\ModalRemoveFormFactory;
use App\Model\LinksRepository;
use App\Model\SponsorsRepository;
use App\Model\TeamsRepository;
use App\Model\AlbumsRepository;
use App\Model\ImagesRepository;
use App\Model\SeasonsTeamsRepository;
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

  /** @var AlbumFormFactory */
  private $albumFormFactory;

  /** @var MultiUploadFormFactory */
  private $multiUploadFormFactory;

  /** @var ModalRemoveFormFactory */
  private $removeFormFactory;

  public function __construct(
    LinksRepository $linksRepository,
    SponsorsRepository $sponsorsRepository,
    TeamsRepository $teamsRepository,
    AlbumsRepository $albumsRepository,
    ImagesRepository $imagesRepository,
    SeasonsTeamsRepository $seasonsTeamsRepository,
    AlbumFormFactory $albumFormFactory,
    MultiUploadFormFactory $multiUploadFormFactory,
    ModalRemoveFormFactory $removeFormFactory
  )
  {
    parent::__construct($linksRepository, $sponsorsRepository, $teamsRepository, $seasonsTeamsRepository);
    $this->albumsRepository = $albumsRepository;
    $this->imagesRepository = $imagesRepository;
    $this->albumFormFactory = $albumFormFactory;
    $this->multiUploadFormFactory = $multiUploadFormFactory;
    $this->removeFormFactory = $removeFormFactory;
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
      $this['albumForm']->setDefaults($this->albumRow);
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
      throw new BadRequestException(self::IMAGE_NOT_FOUND);
    }
    $this->submittedRemoveImage();
  }

  /**
   * Creates add album form
   * @return Nette\Application\UI\Form
   */
  protected function createComponentAlbumForm(): Form
  {
    return $this->albumFormFactory->create(function (Form $form, ArrayHash $values) {
      $id = $this->getParameter('id');

      if ($id) {
        $this->albumRow->update($values);
      } else {
        $this->albumRow = $this->albumsRepository->insert($values);
      }

      $this->flashMessage(self::CHANGES_SAVED_SUCCESSFULLY, self::SUCCESS);
      $this->redirect('view', $this->albumRow->id);
    });
  }

  protected function createComponentRemoveForm(): Form
  {
    return $this->removeFormFactory->create(function () {
      $images = $this->imagesRepository->getForAlbum($this->albumRow->id);

      foreach ($images as $image) {
        $this->imagesRepository->remove($image->id);
      }

      $this->albumsRepository->remove($this->albumRow->id);
      $this->flashMessage('Album bol odstránený', self::SUCCESS);
      $this->redirect('all');
    });
  }

  /**
   * Creates add image form
   * @return Nette\Application\UI\Form
   */
  protected function createComponentAddImageForm(): Form
  {
    return $this->multiUploadFormFactory->create(function (Form $form, ArrayHash $values) {
      foreach ($values->images as $image) {
        $name = strtolower($image->getSanitizedName());
        $data = array(
          'name' => $name,
          'album_id' => $this->albumRow
        );

        if (!$image->isOk() || !$image->isImage()) {
          throw new InvalidArgumentException;
        }

        if (!$image->move($this->imageDir . '/' . $name)) {
          throw new IOException;
        }

        $this->imagesRepository->insert($data);
      }

      $this->flashMessage('Obrázky boli pridané', self::SUCCESS);
      $this->redirect('Albums:view', $this->albumRow->id);
    });
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
    $data = array('thumbnail' => $this->imageRow->name);
    $this->albumRow->update($data);
    $this->flashMessage('Miniatúra bola nastavená', self::SUCCESS);
    $this->redirect('all');
  }
}
