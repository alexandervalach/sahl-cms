<?php

namespace App\Presenters;

use App\FormHelper;
use App\Model\AlbumsRepository;
use App\Model\EventsRepository;
use App\Model\FightsRepository;
use App\Model\ForumRepository;
use App\Model\GalleryRepository;
use App\Model\GoalsRepository;
use App\Model\LinksRepository;
use App\Model\PlayersRepository;
use App\Model\PostImageRepository;
use App\Model\PostsRepository;
use App\Model\PunishmentsRepository;
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

    /** @var PlayersRepository */
    protected $playersRepository;

    /** @var PostImageRepository */
    protected $postImageRepository;

    /** @var PostsRepository */
    protected $postsRepository;

    /** @var PunishmentsRepository */
    protected $punishmentsRepository;

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
    AlbumsRepository $albumsRepository, EventsRepository $eventsRepository, FightsRepository $fightsRepository, ForumRepository $forumRepository, GalleryRepository $galleryRepository, GoalsRepository $goalsRepository, LinksRepository $linksRepository, PlayersRepository $playersRepository, PostImageRepository $postImageRepository, PostsRepository $postsRepository, PunishmentsRepository $punishmentsRepository, RoundsRepository $roundsRepository, RulesRepository $rulesRepository, TablesRepository $tablesRepository, TeamsRepository $teamsRepository, UsersRepository $usersRepository) {

        $this->albumsRepository = $albumsRepository;
        $this->eventsRepository = $eventsRepository;
        $this->fightsRepository = $fightsRepository;
        $this->forumRepository = $forumRepository;
        $this->galleryRepository = $galleryRepository;
        $this->goalsRepository = $goalsRepository;
        $this->linksRepository = $linksRepository;
        $this->playersRepository = $playersRepository;
        $this->postImageRepository = $postImageRepository;
        $this->postsRepository = $postsRepository;
        $this->punishmentsRepository = $punishmentsRepository;
        $this->roundsRepository = $roundsRepository;
        $this->rulesRepository = $rulesRepository;
        $this->tablesRepository = $tablesRepository;
        $this->teamsRepository = $teamsRepository;
        $this->usersRepository = $usersRepository;
    }

    public function beforeRender() {
        $this->template->round = $this->roundsRepository->getLatestRound();
        $this->template->fights = $this->roundsRepository->getLatestRoundFights();
        $this->template->tables = $this->tablesRepository->getTableStats();
        $this->template->sponsors = $this->linksRepository->getSponsors();
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
        $form->addText('username', 'Užívateľské meno:')
                ->setRequired('Zadaj, prosím, užívateľské meno.');

        $form->addPassword('password', 'Heslo:')
                ->setRequired('Zadaj, prosím, heslo.');

        $form->addSubmit('send', 'Prihlásiť');

        $form->onSuccess[] = $this->submittedSignInForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedSignInForm($form) {
        $values = $form->values;

        try {
            $this->getUser()->login($values->username, $values->password);
            $this->redirect('Homepage:');
        } catch (AuthenticationException $e) {
            $form->addError('Nesprávne meno alebo heslo.');
        }
    }

    public function actionOut() {
        $this->getUser()->logout();
        $this->flashMessage('Boli ste odhlásený.', 'success');
        $this->redirect('Homepage:');
    }

    protected function userIsLogged() {
        if (!$this->user->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }

}
