<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;

class TablesPresenter extends BasePresenter 
{
	/** @var ActiveRow */
	private $tableRow;

	/** @var string */
	private $error = "Row not found!";

	public function actionAll() {
	}

	public function renderAll() {
		$this->template->tables = $this->tablesRepository->findAll()->order('points DESC');;
	}

	public function actionCreate() {
		$this->userIsLogged();
	}

	public function renderCreate() {
		$this->getComponent('addTableRowForm');
	}

	public function actionEdit( $id ) {
		$this->userIsLogged();
		$this->tableRow = $this->tablesRepository->findById( $id );
	}

	public function renderEdit( $id ) {
		if(!$this->tableRow)	throw new BadRequestException( $this->error );
		$this->getComponent('editTableRowForm')->setDefaults( $this->tableRow );
	}

	public function actionDelete( $id ) {
		$this->userIsLogged();
		$this->tableRow = $this->tablesRepository->findById( $id );
	}

	public function renderDelete( $id ) {
		if ( !$this->tableRow ) throw new BadRequestException ( $this->error ); 
		$this->template->table = $this->tableRow;
	}

	protected function createComponentAddTableRowForm() {
		$form = new Form;

		$teams = $this->teamsRepository->getTeams();

		$form->addSelect('team_id','Mužstvo', $teams);

		$form->addSubmit('save','Uložiť');

		$form->onSuccess[] = $this->submittedAddTableRowForm;
		FormHelper::setBootstrapFormRenderer( $form );
		return $form;
	}

	protected function createComponentEditTableRowForm() {
		$form = new Form;

		$teams = $this->teamsRepository->getTeams();

		$form->addHidden('team_id');

		$form->addText('win','Výhry')
			 ->setType('number');

		$form->addText('tram','Remízy')
			 ->setType('number');

		$form->addText('lost','Prehry')
			 ->setType('number');

		$form->addText('score1','Skóre 1')
			 ->setType('number');

		$form->addText('score2','Skóre 2')
			 ->setType('number');

		$form->addText('points','Body')
			 ->setType('number');

		$form->addSubmit('save','Uložiť');

		$form->onSuccess[] = $this->submittedEditTableRowForm;
		FormHelper::setBootstrapFormRenderer( $form );
		return $form;
	}

	public function submittedAddTableRowForm( Form $form ) {
		$this->userIsLogged();
		$values = $form->getValues();
		$this->tablesRepository->insert($values);
		$this->redirect('all');
	}

	public function submittedEditTableRowForm( Form $form ) {
		$this->userIsLogged();
		$values = $form->getValues();	
		$this->tableRow->update($values);
		$this->redirect('all');
	}

	public function submittedDeleteForm() {
		$this->userIsLogged();
		$this->tableRow->delete();
		$this->flashMessage('Záznam zmazaný!','success');
		$this->redirect('all');
	}

	public function formCancelled() {
		$this->redirect('all');
	}
}