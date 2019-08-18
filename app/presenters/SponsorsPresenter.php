<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\Forms\RemoveFormFactory;
use App\Forms\SponsorAddFormFactory;
use App\Forms\SponsorEditFormFactory;
use App\Model\GroupsRepository;
use App\Model\LinksRepository;
use App\Model\SeasonsGroupsRepository;
use App\Model\SeasonsGroupsTeamsRepository;
use App\Model\SponsorsRepository;
use App\Model\TeamsRepository;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\FileSystem;
use Nette\Utils\ArrayHash;

/**
 * Class SponsorsPresenter
 * @package App\Presenters
 */
class SponsorsPresenter extends BasePresenter
{
  /** @var ActiveRow */
  private $sponsorRow;

  /**
   * @var SponsorAddFormFactory
   */
  private $sponsorAddFormFactory;

  /**
   * @var SponsorEditFormFactory
   */
  private $sponsorEditFormFactory;
  /**
   * @var RemoveFormFactory
   */
  private $removeFormFactory;

  /**
   * SponsorsPresenter constructor.
   * @param GroupsRepository $groupsRepository
   * @param LinksRepository $linksRepository
   * @param SponsorsRepository $sponsorsRepository
   * @param TeamsRepository $teamsRepository
   * @param SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository
   * @param SeasonsGroupsRepository $seasonsGroupsRepository
   * @param SponsorAddFormFactory $sponsorAddFormFactory
   * @param SponsorEditFormFactory $sponsorEditFormFactory
   * @param RemoveFormFactory $removeFormFactory
   */
  public function __construct(
      GroupsRepository $groupsRepository,
      LinksRepository $linksRepository,
      SponsorsRepository $sponsorsRepository,
      TeamsRepository $teamsRepository,
      SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository,
      SeasonsGroupsRepository $seasonsGroupsRepository,
      SponsorAddFormFactory $sponsorAddFormFactory,
      SponsorEditFormFactory $sponsorEditFormFactory, 
      RemoveFormFactory $removeFormFactory
  )
  {
    parent::__construct($groupsRepository, $linksRepository, $sponsorsRepository, $teamsRepository,
        $seasonsGroupsRepository, $seasonsGroupsTeamsRepository);
    $this->sponsorAddFormFactory = $sponsorAddFormFactory;
    $this->sponsorEditFormFactory = $sponsorEditFormFactory;
    $this->removeFormFactory = $removeFormFactory;
  }

  /**
   *
   */
  public function actionAll(): void
  {
    $this->userIsLogged();
  }

  /**
   *
   */
  public function renderAll(): void
  {
    $this->template->sponsors = $this->sponsorsRepository->getAll();
  }

  /**
   * @param int $id
   */
  public function actionRemove(int $id): void
  {
    $this->userIsLogged();
    $this->sponsorRow = $this->sponsorsRepository->findById($id);
    if (!$this->sponsorRow || !$this->sponsorRow->is_present) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }
  }

  /**
   * @param int $id
   */
  public function renderRemove(int $id): void
  {
    $this->template->sponsor = $this->sponsorRow;
  }

  /**
   * @param int $id
   */
  public function actionEdit(int $id): void
  {
    $this->userIsLogged();
    $this->sponsorRow = $this->sponsorsRepository->findById($id);
    if (!$this->sponsorRow || !$this->sponsorRow->is_present) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }
    $this[self::EDIT_FORM]->setDefaults($this->sponsorRow);
  }

  /**
   * @param int $id
   */
  public function renderEdit(int $id) {
    $this->template->sponsor = $this->sponsorRow;
  }

  /**
   * Generates new add form
   * @return Form
   */
  protected function createComponentAddForm(): Form
  {
    return $this->sponsorAddFormFactory->create( function (Form $form, ArrayHash $values) {
      $this->submittedAddForm($form, $values);
    });
  }

  /**
   * Generates new edit form
   * @return Form
   */
  protected function createComponentEditForm(): Form
  {
    return $this->sponsorEditFormFactory->create( function (SubmitButton $button, ArrayHash $values) {
      $this->userIsLogged();
      $this->sponsorRow->update($values);
      $this->flashMessage(self::ITEM_UPDATED, self::SUCCESS);
      $this->redirect('all');
    }, function () {
      $this->redirect('all');
    });
  }

  /**
   * Generates new remove form
   * @return Form
   */
  protected function createComponentRemoveForm(): Form
  {
    return $this->removeFormFactory->create( function () {
      $this->userIsLogged();
      $this->sponsorsRepository->remove($this->sponsorRow->id);
      $this->flashMessage(self::ITEM_REMOVED_SUCCESSFULLY, self::SUCCESS);
      $this->redirect('all');
    }, function () {
      $this->redirect('all');
    });
  }

  /**
   * @param Form $form
   * @param ArrayHash $values
   */
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
      $this->flashMessage(self::ITEM_ADDED_SUCCESSFULLY, self::SUCCESS);
    } catch (IOException $e) {
      $this->flashMessage('Obrázok ' . $name . ' sa nepodarilo nahrať', self::DANGER);
    }

    $this->redirect('all');
  }
}
