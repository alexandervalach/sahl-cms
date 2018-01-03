<?php

namespace App\Presenters;

use App\BreadCrumb\BreadCrumb;
use App\FormHelper;
use App\Model\AlbumsRepository;
use App\Model\ArchivesRepository;
use App\Model\EventsRepository;
use App\Model\FightsRepository;
use App\Model\ImagesRepository;
use App\Model\GoalsRepository;
use App\Model\LinksRepository;
use App\Model\PlayersRepository;
use App\Model\PlayerTypesRepository;
use App\Model\PostImagesRepository;
use App\Model\PostsRepository;
use App\Model\PunishmentsRepository;
use App\Model\RepliesRepository;
use App\Model\RulesRepository;
use App\Model\RoundsRepository;
use App\Model\TableTypesRepository;
use App\Model\TablesRepository;
use App\Model\TopicsRepository;
use App\Model\TeamsRepository;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Security\AuthenticationException;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Presenter {

    /** @var AlbumsRepository */
    protected $albumsRepository;

    /** @var ArchivesRepository */
    protected $archivesRepository;

    /** @var EventsRepository */
    protected $eventsRepository;

    /** @var FightsRepository */
    protected $fightsRepository;

    /** @var ImagesRepository */
    protected $imagesRepository;

    /** @var GoalsRepository */
    protected $goalsRepository;

    /** @var LinksRepository */
    protected $linksRepository;
    
    /** @var TableTypesRepository */
    protected $tableTypesRepository;
    
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

    /** @var RepliesRepository */
    protected $repliesRepository;

    /** @var RoundsRepository */
    protected $roundsRepository;

    /** @var RulesRepository */
    protected $rulesRepository;

    /** @var TablesRepository */
    protected $tablesRepository;

    /** @var TeamsRepository */
    protected $teamsRepository;

    /** @var TopicsRepository */
    protected $topicsRepository;

    /** @var string */
    protected $imgFolder;

    /** @var string */
    protected $default_img;

    public function __construct(
    ArchivesRepository $archivesRepository, AlbumsRepository $albumsRepository, EventsRepository $eventsRepository, FightsRepository $fightsRepository, TopicsRepository $topicsRepository, ImagesRepository $imagesRepository, GoalsRepository $goalsRepository, LinksRepository $linksRepository, tableTypesRepository $tableTypesRepository, PlayerTypesRepository $playerTypesRepository, PlayersRepository $playersRepository, PostImagesRepository $postImagesRepository, PostsRepository $postsRepository, PunishmentsRepository $punishmentsRepository, RepliesRepository $repliesRepository, RoundsRepository $roundsRepository, RulesRepository $rulesRepository, TablesRepository $tablesRepository, TeamsRepository $teamsRepository) {
        parent::__construct();
        $this->archivesRepository = $archivesRepository;
        $this->albumsRepository = $albumsRepository;
        $this->eventsRepository = $eventsRepository;
        $this->fightsRepository = $fightsRepository;
        $this->topicsRepository = $topicsRepository;
        $this->imagesRepository = $imagesRepository;
        $this->goalsRepository = $goalsRepository;
        $this->linksRepository = $linksRepository;
        $this->tableTypesRepository = $tableTypesRepository;
        $this->playersRepository = $playersRepository;
        $this->playerTypesRepository = $playerTypesRepository;
        $this->postImagesRepository = $postImagesRepository;
        $this->postsRepository = $postsRepository;
        $this->punishmentsRepository = $punishmentsRepository;
        $this->repliesRepository = $repliesRepository;
        $this->roundsRepository = $roundsRepository;
        $this->rulesRepository = $rulesRepository;
        $this->tablesRepository = $tablesRepository;
        $this->teamsRepository = $teamsRepository;
        $this->default_img = "sahl.png";
        $this->imgFolder = "images";
    }

    public function beforeRender() {
        $this->template->links = $this->linksRepository->findByValue('sponsor', 0)->order('title');
        $this->template->sponsors = $this->linksRepository->getSponsors();
        $this->template->imgFolder = $this->imgFolder;

        $n_teams = $this->teamsRepository->findByValue('archive_id', NULL)->order('id');
        $this->template->n_teams = $n_teams;
        $this->template->teams_count = $n_teams->count();
    }

    protected function createComponentDeleteForm() {
        $form = new Form;
        $form->addSubmit('cancel', 'Zrušiť')
             ->setAttribute('class', 'btn btn-large btn-warning')
             ->onClick[] = $this->formCancelled;
        $form->addSubmit('delete', 'Odstrániť')
             ->setAttribute('class', 'btn btn-large btn-danger')
             ->onClick[] = $this->submittedDeleteForm;
        $form->addProtection();
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentSignInForm() {
        $form = new Form;
        $form->addText('username', 'Používateľské meno')
             ->setRequired('Zadajte používateľské meno.');
        $form->addPassword('password', 'Heslo')
             ->setRequired('Zadajte heslo.');
        $form->addSubmit('login', 'Administrácia')
             ->setAttribute('class', 'btn btn-success');
        $form->addProtection();
        $form->onSuccess[] = [$this, 'submittedSignInForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedSignInForm(Form $form, $values) {
        try {
            $this->getUser()->login($values->username, $values->password);
            $this->flashMessage('Vitajte v administrácií SAHL', 'success');
            $this->redirect('Posts:all');
        } catch (AuthenticationException $e) {
            $form->addError('Nesprávne meno alebo heslo');
        }
    }

    public function actionOut() {
        $this->getUser()->logout();
        $this->flashMessage('Boli ste odhlásený.', 'success');
        $this->redirect('Posts:all');
    }

    protected function userIsLogged() {
        if (!$this->user->isLoggedIn()) {
            $this->redirect('Posts:all');
        }
    }

    protected function createComponentBreadCrumb() {
        $breadCrumb = new BreadCrumb();
        $breadCrumb->addLink('Domov', $this->link('Posts:all'));
        return $breadCrumb;
    }

}
