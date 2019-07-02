<?php

namespace App\Presenters;

use App\FormHelper;
use App\Model\AlbumsRepository;
use App\Model\EventsRepository;
use App\Model\FightsRepository;
use App\Model\ImagesRepository;
use App\Model\GoalsRepository;
use App\Model\GroupsRepository;
use App\Model\LinksRepository;
use App\Model\PlayersSeasonsTeamsRepository;
use App\Model\PlayersRepository;
use App\Model\PlayerTypesRepository;
use App\Model\PostImagesRepository;
use App\Model\PostsRepository;
use App\Model\PunishmentsRepository;
use App\Model\RulesRepository;
use App\Model\RoundsRepository;
use App\Model\SeasonsRepository;
use App\Model\SeasonsTeamsRepository;
use App\Model\SponsorsRepository;
use App\Model\TableEntriesRepository;
use App\Model\TableTypesRepository;
use App\Model\TablesRepository;
use App\Model\TeamsRepository;
use App\Model\UsersRepository;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Security\AuthenticationException;

/**
 * Base class for all application presenters.
 */
abstract class BasePresenter extends Presenter {

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
    const IMG_FOLDER = 'images';
    const DEFAULT_IMG = 'sahl.png';
    const CSRF_TOKEN_EXPIRED = 'Platnosť formulára vypršala. Odošlite ho, prosím, znovu.';
    const IMG_NOT_FOUND = 'Image not found';
    const PLAYER_NOT_FOUND = 'Player not found';
    const ROUND_NOT_FOUND = 'Round not found';
    const ARCHIVE_NOT_FOUND = 'Archive not found';
    const RULE_NOT_FOUND = 'Rule not found';
    const ITEM_NOT_FOUND = 'Item not found';

    /** @var AlbumsRepository */
    protected $albumsRepository;

    /** @var EventsRepository */
    protected $eventsRepository;

    /** @var FightsRepository */
    protected $fightsRepository;

    /** @var ImagesRepository */
    protected $imagesRepository;

    /** @var GoalsRepository */
    protected $goalsRepository;

    /** @var GroupsRepository */
    protected $groupsRepository;

    /** @var LinksRepository */
    protected $linksRepository;

    /** @var PlayersSeasonsTeamsRepository */
    protected $playersSeasonsTeamsRepository;

    /** @var PlayerTypesRepository */
    protected $playerTypesRepository;

    /** @var PlayersRepository */
    protected $playersRepository;

    /** @var PostImagesRepository */
    protected $postImagesRepository;

    /** @var PostsRepository */
    protected $postsRepository;

    /** @var PunishmentsRepository */
    protected $punishmentsRepository;

    /** @var RoundsRepository */
    protected $roundsRepository;

    /** @var RulesRepository */
    protected $rulesRepository;

    /** @var SeasonsRepository */
    protected $seasonsRepository;

    /** @var SeasonsTeamsRepository */
    protected $seasonsTeamsRepository;

    /** @var SponsorsRepository */
    protected $sponsorsRepository;

    /** @var TableEntriesRepository */
    protected $tableEntriesRepository;

    /** @var TableTypesRepository */
    protected $tableTypesRepository;

    /** @var TablesRepository */
    protected $tablesRepository;

    /** @var TeamsRepository */
    protected $teamsRepository;

    /** @var UsersRepository */
    protected $usersRepository;

    /** @var string */
    protected $webDir;

    /** @var string */
    protected $imageDir = 'images';

    /** @persistent */
    protected $backlink;

    /**
     * Base constructor
     */
    public function __construct(
      AlbumsRepository $albumsRepository,
      EventsRepository $eventsRepository,
      FightsRepository $fightsRepository,
      ImagesRepository $imagesRepository,
      GoalsRepository $goalsRepository,
      GroupsRepository $groupsRepository,
      LinksRepository $linksRepository,
      PlayersSeasonsTeamsRepository $playersSeasonsTeamsRepository,
      PlayerTypesRepository $playerTypesRepository,
      PlayersRepository $playersRepository,
      PostImagesRepository $postImagesRepository,
      PostsRepository $postsRepository,
      PunishmentsRepository $punishmentsRepository,
      RoundsRepository $roundsRepository,
      RulesRepository $rulesRepository,
      SeasonsRepository $seasonsRepository,
      SeasonsTeamsRepository $seasonsTeamsRepository,
      SponsorsRepository $sponsorsRepository,
      TableEntriesRepository $tableEntriesRepository,
      TableTypesRepository $tableTypesRepository,
      TablesRepository $tablesRepository,
      TeamsRepository $teamsRepository,
      UsersRepository $usersRepository)
    {
      parent::__construct();
      $this->albumsRepository = $albumsRepository;
      $this->eventsRepository = $eventsRepository;
      $this->fightsRepository = $fightsRepository;
      $this->imagesRepository = $imagesRepository;
      $this->goalsRepository = $goalsRepository;
      $this->groupsRepository = $groupsRepository;
      $this->linksRepository = $linksRepository;
      $this->playersRepository = $playersRepository;
      $this->playersSeasonsTeamsRepository = $playersSeasonsTeamsRepository;
      $this->playerTypesRepository = $playerTypesRepository;
      $this->postImagesRepository = $postImagesRepository;
      $this->postsRepository = $postsRepository;
      $this->punishmentsRepository = $punishmentsRepository;
      $this->roundsRepository = $roundsRepository;
      $this->rulesRepository = $rulesRepository;
      $this->seasonsRepository = $seasonsRepository;
      $this->seasonsTeamsRepository = $seasonsTeamsRepository;
      $this->sponsorsRepository = $sponsorsRepository;
      $this->tableEntriesRepository = $tableEntriesRepository;
      $this->tableTypesRepository = $tableTypesRepository;
      $this->tablesRepository = $tablesRepository;
      $this->teamsRepository = $teamsRepository;
      $this->usersRepository = $usersRepository;
      $this->backlink = '';
    }

    /**
     * Method for saving previous link
     */
    protected function startup() {
      parent::startup();
      $this->backlink = $this->storeRequest();
    }

    /**
     * Set before content rendering
     */
    public function beforeRender() {
      $this->template->links = $this->linksRepository->getAll();
      $this->template->sponsors = $this->sponsorsRepository->getAll();
      $this->template->sideTeams = $this->teamsRepository->getForSeason();
      $this->template->imgFolder = self::IMG_FOLDER;
      $this->template->defaultImg = self::DEFAULT_IMG;
    }

    /**
     * Component for creating a remove form
     * @return Nette\Application\UI\Form
     */
    protected function createComponentRemoveForm() {
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
     * Component for creating a sign in form
     * @return Nette\Application\UI\Form
     */
    protected function createComponentSignInForm() {
        $form = new Form;
        $form->addText('username', 'Používateľské meno')
                ->setRequired('Zadajte používateľské meno');
        $form->addPassword('password', 'Heslo')
                ->setRequired('Zadajte heslo');
        $form->addCheckbox('remember', ' Zapamätať si ma na 7 dní');
        $form->addSubmit('login', 'Prihlásiť');
        $form->addProtection(self::CSRF_TOKEN_EXPIRED);
        $form->onSuccess[] = [$this, 'submittedSignInForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    /**
     * Checking whether user exists
     *
     * @param Nette\Application\UI\Form $form
     * @param array $values
     * @throws Nette\Security\AuthenticationException
     */
    public function submittedSignInForm(Form $form, $values) {
        if ($values->remember) {
            $this->user->setExpiration('7 days', FALSE);
        } else {
            $this->user->setExpiration('30 minutes', TRUE);
        }

        try {
            $this->user->login($values->username, $values->password);
            $this->flashMessage('Vitajte v administrácií SAHL', self::SUCCESS);
            $this->redirect('Homepage:all');
        } catch (Nette\Security\AuthenticationException $e) {
            $this->flashMessage('Nesprávne meno alebo heslo', self::DANGER);
            $this->redirect('Homepage:all');
        }
    }

    /**
     * Log out action routing
     */
    public function actionOut() {
        $this->getUser()->logout();
        $this->flashMessage('Boli ste odhlásený', self::SUCCESS);
        $this->redirect('Homepage:all');
    }

    /**
     * Checks whether User is logged
     */
    protected function userIsLogged() {
        if (!$this->user->isLoggedIn()) {
            $this->redirect('Homepage:all');
        }
    }

}
