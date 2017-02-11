<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;

class ArchivePresenter extends BasePresenter {

	/** @var ActiveRow */
	private $archiveRow;

	/** @var string */
	private $error = "Archive not found.";

	public function renderAll() {
		$this->template->archive = $this->archiveRepository->findAll();
	}

	public function actionAdd() {
		$this->userIsLogged();
	}

	public function renderAdd() {
		$this->getComponent('addForm');
	}

	public function actionEdit($id) {
		$this->userIsLogged();
		$this->archiveRow = $this->archiveRepository->findById($id);
	}

	public function renderEdit($id) {
		if(!$this->archiveRow) {
			throw new BadRequestException($this->error);
		}
		$this->template->archive = $this->archiveRow;
		$this->getComponent('editForm')->setDefaults($this->archiveRow);
	}

	public function actionView($id) {
		$this->archiveRow = $this->archiveRepository->findById($id);
	}

	public function renderView($id) {
		if(!$this->archiveRow) {
			throw new BadRequestException($this->error);
		}
		$this->template->archive = $this->archiveRow;
	}

	public function actionDetauls($id) {

	}

	public function renderDetails($id) {

	}

	protected function createComponentAddForm() {
		$form = new Form;
		$form->addText('title', 'Názov')
		     ->addRule(Form::FILLED, 'Opa, názov ešte nie je vyplnený.');
		$form->addSubmit('save', 'Uložiť');

		$form->onSuccess[] = $this->submittedAddForm;

		FormHelper::setBootstrapFormRenderer($form);
		return $form;
	}

	protected function createComponentEditForm() {
		$form = new Form;
		$form->addText('title', 'Názov')
		     ->addRule(Form::FILLED, 'Opa, názov ešte nie je vyplnený.');
		$form->addSubmit('save', 'Uložiť');
		$form->onSuccess[] = $this->submittedEditForm;
		FormHelper::setBootstrapFormRenderer($form);
		return $form;
	}

	protected function createComponentArchiveForm() {
        $form = new Form;
        $archives = $this->archiveRepository->getArchives();
        $form->addSelect('archive_id', 'Vyber archív: ', $archives);
        $form->addSubmit('save', 'Archivovať');
        $form->onSuccess[] = $this->submittedArchiveForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
	}

	public function submittedAddForm(Form $form) {
		$values = $form->getValues();
		$this->archiveRepository->insert($values);
		$this->redirect('all#nav');
	}

	public function submittedEditForm(Form $form) {
		$values = $form->getValues();
		$this->archiveRow->update($values);
		$this->redirect('all#nav');
	}

	public function submittedArchiveForm(Form $form) {
		$values = $form->getValues();
		$rounds = $this->roundsRepository->getAsArray();
		$teams = $this->teamsRepository->getAsArray();
		$events = $this->eventsRepository->getAsArray();
		$rules = $this->rulesRepository->getAsArray();
		$players = $this->playersRepository->getAsArray();
		$tables = $this->tablesRepository->getAsArray();
		$punishments = $this->punishmentsRepository->getAsArray(); 
		$data = array();

		foreach ($rounds as $round) {
			$data['name'] = $round->name;
			$data['archive_id'] = $values['archive_id'];
			$this->roundsRepository->insert($data);
		}

		foreach ($teams as $team) {
			$data['name'] = $team->name;
			$data['image'] = $team->image;
			$data['archive_id'] = $values['archive_id'];
			$this->teamsRepository->insert($data);
		}

		$data = array();

		foreach ($events as $event) {
			$data['event'] = $event->event;
			$data['archive_id'] = $values['archive_id'];
			$this->eventsRepository->insert($data);
		}

		$data = array();

		foreach ($rules as $rule) {
			$data['rule'] = $rule->rule;
			$data['archive_id'] = $values['archive_id'];
			$this->rulesRepository->insert($data);
		}
		/*
		foreach ($players as $player) {

		}
		*/
		/*
		foreach ($tables as $table) {
			$data['team_id'] = $table->team_id;
			$data['counter'] = $table->counter;
			$data['win'] = $table->win;
			$data['tram'] = $table->tram;
			$data['lost'] = $table->lost;
			$data['score1'] = $table->score1;
			$data['score2'] = $table->score2;
			$data['points'] = $table->points;
			$data['type'] = $table->type;
			$data['archive_id'] = $values['archive_id'];
			$this->tablesRepository->insert($data);
		}
		*/
		/*
		foreach ($punishments as $pun) {
			$data['player_id'] = $pun->;
			$data['archive_id'] = $value['archive_id'];
			$this->punishmentsRepository->insert();
		}
		*/

		$this->redirect('all#nav');
	}

	public function formCancelled() {
		$this->redirect('all#nav');
	}
}