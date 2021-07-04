<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\Model\LinksRepository;
use App\Model\GroupsRepository;
use App\Model\SponsorsRepository;
use App\Model\SeasonsGroupsTeamsRepository;
use App\Model\SeasonsGroupsRepository;
use App\Model\TeamsRepository;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Utils\ArrayHash;

/**
 * Base class for all application presenters.
 */
abstract class BasePresenter extends Presenter
{
  /* Defined Constants */
  const EDIT_FORM = 'editForm';
  const SUBMITTED_ADD_FORM = 'submittedAddForm';
  const SUBMITTED_EDIT_FORM = 'submittedEditForm';
  const BTN_WARNING = 'btn btn-large btn-warning';
  const SUCCESS = 'success';
  const DANGER = 'danger';
  const WARNING = 'warning';
  const IMAGE_FOLDER = 'images';
  const DEFAULT_IMAGE = 'sahl.svg';
  const ITEM_NOT_FOUND = 'Item not found.';
  const CHANGES_SAVED_SUCCESSFULLY = 'Zmeny boli uložené.';
  const ITEM_ALREADY_EXISTS = 'Záznam už existuje.';
  const ITEM_UPDATED = self::CHANGES_SAVED_SUCCESSFULLY;
  const ITEM_ADDED_SUCCESSFULLY = 'Položka bola pridaná.';
  const ITEM_REMOVED_SUCCESSFULLY = 'Položka bola odstránená.';
  const ITEM_NOT_ADDED = 'Položka nebola pridaná.';
  const ITEM_NOT_REMOVED = 'Položku sa nepodarilo odstrániť.';
  const BASE_TABLE_LABEL = 'Základná časť';

  /** @var LinksRepository */
  protected $linksRepository;

  /** @var GroupsRepository */
  protected $groupsRepository;

  /** @var SponsorsRepository */
  protected $sponsorsRepository;

  /** @var SeasonsGroupsRepository */
  protected $seasonsGroupsRepository;

  /** @var SeasonsGroupsTeamsRepository */
  protected $seasonsGroupsTeamsRepository;

  /** @var TeamsRepository */
  protected $teamsRepository;

  /** @var string */
  protected $imageDir;

  /** @var array */
  protected $groups;

  /**
   * Base constructor
   * @param GroupsRepository $groupsRepository
   * @param LinksRepository $linksRepository
   * @param SponsorsRepository $sponsorsRepository
   * @param TeamsRepository $teamsRepository
   * @param SeasonsGroupsRepository $seasonsGroupsRepository
   * @param SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository
   */
  public function __construct(
      GroupsRepository $groupsRepository,
      LinksRepository $linksRepository,
      SponsorsRepository $sponsorsRepository,
      TeamsRepository $teamsRepository,
      SeasonsGroupsRepository $seasonsGroupsRepository,
      SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository)
  {
    parent::__construct();
    $this->groupsRepository = $groupsRepository;
    $this->linksRepository = $linksRepository;
    $this->sponsorsRepository = $sponsorsRepository;
    $this->teamsRepository = $teamsRepository;
    $this->seasonsGroupsRepository = $seasonsGroupsRepository;
    $this->seasonsGroupsTeamsRepository = $seasonsGroupsTeamsRepository;
    $this->imageDir = 'images';
    $this->groups = [];
  }

  /**
   * Set before content rendering
   */
  public function beforeRender(): void
  {
    $seasonsGroups = $this->seasonsGroupsRepository->getForSeason();

    foreach ($seasonsGroups as $seasonGroup) {
      $group = $this->groupsRepository->findById($seasonGroup->group_id);
      $this->groups[$seasonGroup->group_id]['id'] = $group->id;
      $this->groups[$seasonGroup->group_id]['label'] = $group->label;
      $this->groups[$seasonGroup->group_id]['teams'] = $this->teamsRepository->fetchForSeasonGroup($seasonGroup->id);
    }

    $this->template->links = $this->linksRepository->getAll();
    $this->template->sponsors = $this->sponsorsRepository->getAll();
    $this->template->groups = ArrayHash::from($this->groups);
    $this->template->imageFolder = self::IMAGE_FOLDER;
    $this->template->defaultImage = self::DEFAULT_IMAGE;
  }

  /**
   * Checks whether User is logged
   */
  protected function userIsLogged(): void
  {
    if (!$this->user->isLoggedIn()) {
      $this->redirect('Homepage:all');
    }
  }

  /**
   * Redirect user after form cancellation
   */
  public function formCancelled(): void
  {
    $this->redirect('all');
  }
}
