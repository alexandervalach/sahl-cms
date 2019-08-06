<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\FormHelper;
use App\Model\LinksRepository;
use App\Model\SponsorsRepository;
use App\Model\SeasonsTeamsRepository;
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
  const ADD_FORM = 'addForm';
  const EDIT_FORM = 'editForm';
  const REMOVE_FORM = 'removeForm';
  const UPLOAD_FORM = 'uploadForm';
  const SUBMITTED_ADD_FORM = 'submittedAddForm';
  const SUBMITTED_EDIT_FORM = 'submittedEditForm';
  const SUBMITTED_REMOVE_FORM = 'submittedRemoveForm';
  const SUBMITTED_UPLOAD_FORM = 'submittedUploadForm';
  const SUBMITTED_ADD_IMAGE_FORM = 'submittedAddImageForm';
  const SUBMITTED_RESET_FORM = 'submittedResetForm';
  const FORM_CANCELLED = 'formCancelled';
  const BTN_WARNING = 'btn btn-large btn-warning';
  const BTN_DANGER = 'btn btn-large btn-danger';
  const BTN_SUCCESS = 'btn btn-large btn-success';
  const BTN_PRIMARY = 'btn btn-large btn-primary';
  const BTN_INFO = 'btn btn-large btn-info';
  const BTN_DEFAULT = 'btn btn-large btn-default';
  const SUCCESS = 'success';
  const DANGER = 'danger';
  const WARNING = 'warning';
  const GOALIE = 'Brankár';
  const IMAGE_FOLDER = 'images';
  const DEFAULT_IMAGE = 'sahl.png';
  const CSRF_TOKEN_EXPIRED = 'Platnosť formulára vypršala. Odošlite ho, prosím, znovu.';
  const IMG_NOT_FOUND = 'Image not found.';
  const PLAYER_NOT_FOUND = 'Player not found.';
  const ROUND_NOT_FOUND = 'Round not found.';
  const SEASON_NOT_FOUND = 'Season not found.';
  const RULE_NOT_FOUND = 'Rule not found.';
  const ITEM_NOT_FOUND = 'Item not found.';
  const CHANGES_SAVED_SUCCESSFULLY = 'Zmeny boli uložené.';
  const ITEM_ALREADY_EXISTS = 'Záznam už existuje.';
  const ITEM_ADDED_SUCCESSFULLY = 'Položka bola pridaná.';
  const ITEM_REMOVED_SUCCESSFULLY = 'Položka bola odstránená.';

  /** @var LinksRepository */
  protected $linksRepository;

  /** @var SponsorsRepository */
  protected $sponsorsRepository;

  /** @var SeasonsTeamsRepository */
  protected $seasonsTeamsRepository;

  /** @var TeamsRepository */
  protected $teamsRepository;

  /** @var string */
  protected $imageDir;

  /** @var AraryHash */
  protected $teams;

  /**
   * Base constructor
   */
  public function __construct(
    LinksRepository $linksRepository,
    SponsorsRepository $sponsorsRepository,
    TeamsRepository $teamsRepository,
    SeasonsTeamsRepository $seasonsTeamsRepository)
  {
    parent::__construct();
    $this->linksRepository = $linksRepository;
    $this->sponsorsRepository = $sponsorsRepository;
    $this->teamsRepository = $teamsRepository;
    $this->seasonsTeamsRepository = $seasonsTeamsRepository;
    $this->imageDir = 'images';
  }

  /**
   * Method for saving previous link
   */
  /*
  protected function startup(): void
  {
    parent::startup();
    $this->backlink = $this->storeRequest();
  }
  */

  /**
   * Set before content rendering
   */
  public function beforeRender(): void
  {
    $teams = $this->seasonsTeamsRepository->getForSeason();
    $data = [];

    foreach ($teams as $team) {
      $data[$team->id] = $team->ref('teams', 'team_id');
    }

    $this->teams = ArrayHash::from($data);

    $this->template->links = $this->linksRepository->getAll();
    $this->template->sponsors = $this->sponsorsRepository->getAll();
    $this->template->teams = $this->teams;
    $this->template->imageFolder = self::IMAGE_FOLDER;
    $this->template->defaultImage = self::DEFAULT_IMAGE;
  }

  /**
   * Component for creating a remove form
   * @return Nette\Application\UI\Form
   */
  protected function createComponentRemoveForm(): Form
  {
    $form = new Form;
    $form->addSubmit('remove', 'Odstrániť')
          ->setAttribute('class', self::BTN_DANGER);
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->addProtection(self::CSRF_TOKEN_EXPIRED);
    $form->onSuccess[] = [$this, self::SUBMITTED_REMOVE_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
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

  public function formCancelled(): void
  {
    $this->redirect('all');
  }
}
