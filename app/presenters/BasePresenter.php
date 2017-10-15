<?php

namespace App\Presenters;

use App\FormHelper;
use App\Model\AlbumsRepository;
use App\Model\ArchiveRepository;
use App\Model\EventsRepository;
use App\Model\FightsRepository;
use App\Model\ForumRepository;
use App\Model\GalleryRepository;
use App\Model\GoalsRepository;
use App\Model\LinksRepository;
use App\Model\OptionsRepository;
use App\Model\PlayersRepository;
use App\Model\PlayerTypesRepository;
use App\Model\PostImageRepository;
use App\Model\PostsRepository;
use App\Model\PunishmentsRepository;
use App\Model\ReplyRepository;
use App\Model\RulesRepository;
use App\Model\RoundsRepository;
use App\Model\TablesRepository;
use App\Model\TeamsRepository;
use App\Model\UsersRepository;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Security\AuthenticationException;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Presenter {

    /** @var AlbumsRepository */
    protected $albumsRepository;

    /** @var ArchiveRepository */
    protected $archiveRepository;

    /** @var EventsRepository */
    protected $eventsRepository;

    /** @var FightsRepository */
    protected $fightsRepository;

    /** @var ForumRepository */
    protected $forumRepository;

    /** @var GalleryRepository */
    protected $galleryRepository;

    /** @var GoalsRepository */
    protected $goalsRepository;

    /** @var LinksRepository */
    protected $linksRepository;
    
    /** @var OptionsRepository */
    protected $optionsRepository;
    
    /** @var PlayerTypesRepository */
    protected $playerTypesRepository;

    /** @var PlayersRepository */
    protected $playersRepository;

    /** @var PostImageRepository */
    protected $postImageRepository;

    /** @var PostsRepository */
    protected $postsRepository;

    /** @var PunishmentsRepository */
    protected $punishmentsRepository;

    /** @var ReplyRepository */
    protected $replyRepository;

    /** @var RoundsRepository */
    protected $roundsRepository;

    /** @var RulesRepository */
    protected $rulesRepository;

    /** @var TablesRepository */
    protected $tablesRepository;

    /** @var TeamsRepository */
    protected $teamsRepository;

    /** @var UsersRepository */
    protected $usersRepository;

    /** @var string */
    protected $imgFolder = "/images/";

    public function __construct(
    ArchiveRepository $archiveRepository, AlbumsRepository $albumsRepository, EventsRepository $eventsRepository, FightsRepository $fightsRepository, ForumRepository $forumRepository, GalleryRepository $galleryRepository, GoalsRepository $goalsRepository, LinksRepository $linksRepository, OptionsRepository $optionsRepository, PlayerTypesRepository $playerTypesRepository, PlayersRepository $playersRepository, PostImageRepository $postImageRepository, PostsRepository $postsRepository, PunishmentsRepository $punishmentsRepository, ReplyRepository $replyRepository, RoundsRepository $roundsRepository, RulesRepository $rulesRepository, TablesRepository $tablesRepository, TeamsRepository $teamsRepository, UsersRepository $usersRepository) {
        parent::__construct();
        $this->archiveRepository = $archiveRepository;
        $this->albumsRepository = $albumsRepository;
        $this->eventsRepository = $eventsRepository;
        $this->fightsRepository = $fightsRepository;
        $this->forumRepository = $forumRepository;
        $this->galleryRepository = $galleryRepository;
        $this->goalsRepository = $goalsRepository;
        $this->linksRepository = $linksRepository;
        $this->optionsRepository = $optionsRepository;
        $this->playersRepository = $playersRepository;
        $this->playerTypesRepository = $playerTypesRepository;
        $this->postImageRepository = $postImageRepository;
        $this->postsRepository = $postsRepository;
        $this->punishmentsRepository = $punishmentsRepository;
        $this->replyRepository = $replyRepository;
        $this->roundsRepository = $roundsRepository;
        $this->rulesRepository = $rulesRepository;
        $this->tablesRepository = $tablesRepository;
        $this->teamsRepository = $teamsRepository;
        $this->usersRepository = $usersRepository;
    }

    public function beforeRender() {
        $sponsors = $this->linksRepository->getSponsors();
        $this->template->sideRound = $this->roundsRepository->getLatestRound();
        $this->template->sideFights = $this->roundsRepository->getLatestRoundFights();
        $this->template->baseTable = $this->tablesRepository->getTableStats(2);
        $this->template->playOff = $this->tablesRepository->getTableStats(1);
        $this->template->options = $this->optionsRepository->findByValue('visible', 1);
        $this->template->sponsorsCount = $sponsors->count();
        $this->template->links = $this->linksRepository->findByValue('sponsor', 0);
        $this->template->sponsors = $sponsors;
        $this->template->imgFolder = $this->imgFolder;
    }

    protected function createComponentDeleteForm() {
        $form = new Form;
        $form->addSubmit('cancel', 'Zruš')
             ->setAttribute('class', 'btn btn-warning btn-large')
             ->onClick[] = $this->formCancelled;
        $form->addSubmit('delete', 'Zmaž')
             ->setAttribute('class', 'btn btn-danger btn-large')
             ->onClick[] = $this->submittedDeleteForm;
        $form->addProtection();
        return $form;
    }

    protected function createComponentSignInForm() {
        $form = new Form;
        $form->addText('username', 'Používateľské meno')
             ->setRequired('Zadajte používateľské meno.');
        $form->addPassword('password', 'Heslo')
             ->setRequired('Zadajte heslo.');
        $form->addSubmit('send', 'Prihlásiť');
        $form->addProtection();
        $form->onSuccess[] = $this->submittedSignInForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedSignInForm($form) {
        $values = $form->values;

        try {
            $this->getUser()->login($values->username, $values->password);
            $this->redirect('Homepage:default#nav');
        } catch (AuthenticationException $e) {
            $form->addError('Nesprávne meno alebo heslo.');
        }
    }

    public function actionOut() {
        $this->getUser()->logout();
        $this->flashMessage('Boli ste odhlásený.', 'success');
        $this->redirect('Homepage:#nav');
    }

    protected function userIsLogged() {
        if (!$this->user->isLoggedIn()) {
            $this->redirect('Sign:in#nav');
        }
    }

}
