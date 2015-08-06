<?php

namespace App\Presenters;

use App\FormHelper;
use App\Model\AlbumsRepository;
use App\Model\EventsRepository;
use App\Model\FightsRepository;
use App\Model\ForumRepository;
use App\Model\GalleryRepository;
use App\Model\LinksRepository;
use App\Model\PlayersRepository;
use App\Model\PostImageRepository;
use App\Model\PostsRepository;
use App\Model\RulesRepository;
use App\Model\RoundsRepository;
use App\Model\Table_namesRepository;
use App\Model\TablesRepository;
use App\Model\TeamsRepository;
use App\Model\UserManager;
use App\Model\UsersRepository;
use DateInterval;
use DatePeriod;
use Nette\Utils\DateTime;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Security\AuthenticationException;
use Nette\Database\Context;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Presenter
{
	/** @var AlbumsRepository @inject */
	public $albumsRepository;

	/** @var EventsRepository @inject */
	public $eventsRepository;

	/** @var FightsRepository @inject */
	public $fightsRepository;

	/** @var ForumRepository @inject */
	public $forumRepository;

	/** @var GalleryRepository @inject */
	public $galleryRepository;

	/** @var LinksRepository @inject */
	public $linksRepository;

	/** @var PlayersRepository @inject */
	public $playersRepository;

	/** @var PostImageRepository @inject */
	public $postImageRepository;

	/** @var PostsRepository @inject */
	public $postsRepository;

        /** @var RoundsRepository @inject */
        public $roundsRepository;
        
	/** @var RulesRepository @inject */
	public $rulesRepository;

	/** @var TablesRepository @inject */
	public $tablesRepository;

	/** @var Table_namesRepository @inject */
	public $tableNameRepository;

	/** @var TeamsRepository @inject */
	public $teamsRepository;

	/** @var UsersRepository @inject */
	public $usersRepository;
        
        /** @var string */
        protected $imgFolder = "/images/";

	protected function createComponentDeleteForm()
	{
		$form = new Form;

		$form->addSubmit('cancel','Zruš')
			 ->setAttribute('class','btn btn-warning btn-large')
			 ->onClick[] = $this->formCancelled;

		$form->addSubmit('delete','Zmaž')
			 ->setAttribute('class','btn btn-danger btn-large')
			 ->onClick[] = $this->submittedDeleteForm;

		$form->addProtection();
		return $form;
	}

	protected function createComponentSignInForm()
	{
		$form = new Form;
		$form->addText('username', 'Užívateľské meno:')
			 ->setRequired('Zadaj, prosím, užívateľské meno.');

		$form->addPassword('password', 'Heslo:')
			 ->setRequired('Zadaj, prosím, heslo.');

		$form->addSubmit('send', 'Prihlásiť');

		$form->onSuccess[] = $this->submittedSignInForm;
		FormHelper::setBootstrapFormRenderer( $form );
		return $form;
	}

	public function submittedSignInForm( $form )
	{
		$values = $form->values;

		try {
			$this->getUser()->login( $values->username, $values->password );
			$this->redirect('Homepage:');

		} catch ( AuthenticationException $e ) {
			$form->addError('Nesprávne meno alebo heslo.');
		}
	}

	public function actionOut()
	{
		$this->getUser()->logout();
		$this->flashMessage('Boli ste odhlásený.','success');
		$this->redirect('Homepage:');
	}

	protected function userIsLogged() {		
		if( !$this->user->isLoggedIn() ) 	$this->redirect('Sign:in');
	}
}
