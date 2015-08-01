<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Utils\Arrays;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Utils\FileSystem;
use Nette\Database\Table\ActiveRow;

class PlayerPresenter extends BasePresenter 
{
	/** @var ActiveRow */
	private $playerRow;

	/** @var ActiveRow */
	private $teamRow;

	/** @var string */
	private $error = "Player not found!";

	/* OK */
	public function actionView( $id ) {
		$this->teamRow = $this->teamsRepository->findById( $id );
	}

	/* OK */
	public function renderView( $id ) {
		$team = $this->teamRow;
		$this->template->players = $team->related('players')->order('lname ASC')->order('fname ASC');
		$this->template->team = $team;
	}

	public function actionShow( $id ) {
		$this->playerRow = $this->playersRepository->findById( $id );
	}

	public function renderShow( $id ) {
		$player = $this->playerRow;
		if( !$player ) {
			throw new BadRequestException( $this->error );
		}

		$this->template->post = $player;
	}

	/* OK */
	public function actionCreate( $id )
	{
		$this->userIsLogged();
		$this->teamRow = $this->teamsRepository->findById( $id );
	}

	/* OK */
	public function renderCreate( $id ) {
		$this->getComponent('addPlayerForm');
	}

	public function actionEditStats( $id ) {
		$this->userIsLogged();
		$this->playerRow = $this->playersRepository->findById( $id );
	}

	public function renderEditStats( $id ) {
		$this->getComponent('editStatsForm')->setDefaults( $this->playerRow );
	}

	/* OK */
	public function actionEdit( $id ) 
	{
		$this->userIsLogged();
		$this->playerRow = $this->playersRepository->findById( $id );
	}

	/* OK */
	public function renderEdit( $id ) {
		if( !$this->playerRow ) {
			throw new BadRequestException( $this->error );
		}

		$this->getComponent('editPlayerForm')->setDefaults( $this->playerRow );
	}

	public function actionDelete( $id )
	{
		$this->userIsLoggedIn();
		$player = $this->playersRepository->findById( $id );

		if( !$player ) {
			throw new BadRequestException;
		}

		$this->template->player = $player;
	}

	/* OK */
	protected function createComponentAddPlayerForm() 
	{
		$form = new Form;

		$form->addText('fname', 'Meno:')
			 ->setRequired();

		$form->addText('lname', 'Priezvisko:')
			 ->setRequired();

		$form->addText('num', 'Číslo:');

		$form->addSubmit('submit', 'Uložiť');

		$form->onSuccess[] = $this->submittedAddPlayerForm;

		FormHelper::setBootstrapFormRenderer( $form );
		return $form;
	}

	/* OK */
	public function submittedAddPlayerForm( $form ){
		$team_id = $this->getParameter( 'id' );
		$values = $form->getValues();
		$values['team_id'] = $team_id;
		$id = $this->playersRepository->insert( $values );
		$this->redirect( 'show', $id );
	}

	/* OK */
	protected function createComponentEditPlayerForm() {
		$form = new Form;
		$form->addText('fname', 'Meno:')
			 ->setRequired();

		$form->addText('lname', 'Priezvisko:')
			 ->setRequired();

		$form->addText('num', 'Číslo:');

		$form->addText( 'born' , 'Dátum narodenia:' )
			 	->setType( 'date' );

		$form->addSubmit('save', 'Uložiť');

		$form->onSuccess[] = $this->submittedEditPlayerForm;
		FormHelper::setBootstrapFormRenderer( $form );
		return $form;
	}

	protected function createComponentEditStatsForm() {
		$form = new Form;
		$form->addText('fights', 'Zápasy:')
			 ->setType('number');
		$form->addText('goals', 'Góly:')
			 ->setType('number');
		$form->addSubmit('save', 'Uložiť');
		$form->onSuccess[] = $this->submittedEditStatsForm;

		FormHelper::setBootstrapFormRenderer( $form );
		return $form;
	}

	public function postFormSucceeded( $form ) 
	{
		$values = $form->getValues( TRUE );
		$id = $this->getParameter( 'id' );
		$playerRow = $this->playersRepository;

		if( $id ) {

			$file = $values['file'];
			$player = $this->playerRow->findById( $id );
			$team_id = 1;//$player->teams->id;

			$values['team_id'] = $team_id;

			if( $file_name = $file->getSanitizedName() ) {

				if( $file->isOk() && $file->isImage() ) {
			    	$file->move('images/photo/' . $file_name);
			    }

	    	} else {

	    		//$file_name = $player->photo;
	    	
	    	}

	    	Arrays::renameKey($values, 'file', 'photo');
		    $values['photo'] = $file_name;
			$this->playerRow->update($values);

		} else {

	   		$file = $values['file'];
	   		Arrays::renameKey($values, 'file', 'photo');
	   		$values['team_id'] = "1";

	   		if( $file->isOk() && $file->isImage() )
	   		{
	    		$file_name = $file->name;
	    		$file->move('images/photo/' . $file_name);
	    		$values['photo'] = $file_name;
	    	} 

		    $player = $this->playerRow->insert( $values );
		}

		$this->flashMessage("Hráč bol pridaný.",'success');
		$this->redirect('show', $player->id);
	}

	public function submittedEditPlayerForm( $form ){
		$values = $form->getValues();
		$id = $this->getParameter('id');
		$this->playerRow->update( $values );
		$this->redirect( 'show' , $id );
	}

	public function submittedEditStatsForm( $form ) {
		$values = $form->getValues();
		$id = $this->getParameter( 'id' );
		$this->playerRow->update( $values );
		$this->redirect( 'show', $id ); 
	}

	public function deleteFormSucceeded()
	{
		$id = $this->getParameter( 'id' );

		$player = $this->playersRepository->findById( $id );

	    $file = new FileSystem;
	    $file->delete( 'images/photo/' . $player->photo );
	      
	    $player->delete();

		$this->flashMessage('Hráč zmazaný.','success');
		$this->redirect('default');
	}

	public function formCancelled()
	{
		$this->redirect('default');
	}
}