<?php

namespace App\Presenters;

use App\FormHelper;
use App\Model\LinksRepository;
use App\Model\SponsorsRepository;
use App\Model\TeamsRepository;
use App\Model\PostsRepository;
use App\Model\PostImagesRepository;
use Nette\Application\UI\Form;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\FileSystem;
use Nette\Utils\ArrayHash;
use Nette\IOException;
use Nette\InvalidArgumentException;

class PostsPresenter extends BasePresenter
{
  const POST_NOT_FOUND = 'Post not found';
  const IMAGE_NOT_FOUND = 'Image not found';
  const ADD_IMG_FORM = 'addImgForm';

  /** @var ActiveRow */
  private $postRow;

  /** @var ActiveRow */
  private $imgRow;

  /** @var PostsRepository */
  private $postsRepository;

  /** @var PostImagesRepository */
  private $postImagesRepository;

  public function __construct(
    LinksRepository $linksRepository,
    SponsorsRepository $sponsorsRepository,
    TeamsRepository $teamsRepository,
    PostsRepository $postsRepository,
    PostImagesRepository $postImagesRepository
  )
  {
    parent::__construct($linksRepository, $sponsorsRepository, $teamsRepository);
    $this->postsRepository = $postsRepository;
    $this->$postImagesRepository = $postImagesRepository;
  }

  public function renderAll(): void
  {
    $this->template->posts = $this->postsRepository->getAll()->order('id DESC');
  }

  public function actionView(int $id): void
  {
    $this->postRow = $this->postsRepository->findById($id);

    if (!$this->postRow || !$this->postRow->is_present) {
      throw new BadRequestException(self::POST_NOT_FOUND);
    }

    if ($this->user->isLoggedIn()) {
      $this['postForm']->setDefaults($this->postRow);
    }
  }

  public function renderView(int $id): void
  {
    $this->template->post = $this->postRow;
    $this->template->images = $this->postsRepository->getImages($this->postRow);
  }

  public function actionSetImg($postId, $id): void
  {
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

  public function actionRemoveImg($postId, $id): void
  {
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

  protected function createComponentPostForm(): Form
  {
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
    $form->onSuccess[] = [$this, 'submittedPostForm'];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  protected function createComponentAddImgForm(): Form
  {
    $form = new Form;
    $form->addMultiUpload('images', 'Obrázok');
    $form->addSubmit('upload', 'Nahrať');
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, self::SUBMITTED_ADD_IMAGE_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  public function submittedPostForm(Form $form, ArrayHash $values): void
  {
    $id = $this->getParameter('id');

    if ($id) {
      $this->postRow = $this->postsRepository->findById($id);
      $this->postRow->update($values);
    } else {
      $this->postRow = $this->postsRepository->insert($values);
    }

    $this->flashMessage(self::CHANGES_SAVED_SUCCESSFULLY, self::SUCCESS);
    $this->redirect('view', $this->postRow->id);
  }

  public function submittedRemoveForm(): void
  {
    $images = $this->postsRepository->getImages($this->postRow);

    foreach ($images as $image) {
      $this->postImagesRepository->remove($image);
    }

    $this->postsRepository->remove($this->postRow);
    $this->flashMessage('Príspevok bol odstránený', self::SUCCESS);
    $this->redirect('all');
  }

  public function submittedSetImgForm(): void
  {
    $values['thumbnail'] = $this->imgRow->name;
    $this->postRow->update($values);
    $this->flashMessage('Miniatúra bola nastavená', self::SUCCESS);
    $this->redirect('view', $this->postRow->id);
  }

  public function submittedRemoveImgForm(): void
  {
    try {
      FileSystem::delete($this->imageDir . $this->imgRow->name);
      $this->imgRow->delete();
      $this->flashMessage('Obrázok bol odstránený', self::SUCCESS);
    } catch (IOException $e) {
      $this->flashMessage('Obrázok sa nepodarilo odstrániť', self::DANGER);
    }
    $this->redirect('view', $this->postRow->id);
  }

  public function submittedAddImageForm(Form $form, ArrayHash $values): void
  {
    foreach ($values->images as $file) {
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
