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
		$round_id = array();
		$team_id = array();
		$player_id = array();
		$data = array();

		$rows = $this->roundsRepository->getAsArray();
		foreach ($rows as $round) {
			$data['name'] = $round->name;
			$data['archive_id'] = $values['archive_id'];
			$id = $this->roundsRepository->insert($data);
			$round_id[$round->id] = $id;
		}
		
		$rows = $this->teamsRepository->getAsArray();
		foreach ($rows as $team) {
			$data['name'] = $team->name;
			$data['image'] = $team->image;
			$data['archive_id'] = $values['archive_id'];
			$id = $this->teamsRepository->insert($data);
			$team_id[$team->id] = $id;
		}

		$data = array();
		$rows = $this->eventsRepository->getAsArray();
		foreach ($rows as $event) {
			$data['event'] = $event->event;
			$data['archive_id'] = $values['archive_id'];
			$this->eventsRepository->insert($data);
		}

		$data = array();
		$rows = $this->rulesRepository->getAsArray();
		foreach ($rows as $rule) {
			$data['rule'] = $rule->rule;
			$data['archive_id'] = $values['archive_id'];
			$this->rulesRepository->insert($data);
		}

		$data = array();
		$rows = $this->playersRepository->getAsArray();
		foreach ($rows as $player) {
			$data['team_id'] = $team_id[$player->team_id];
			$data['type_id'] = $player->type_id;
			$data['lname'] = $player->lname;
			$data['num'] = $player->num;
			$data['born'] = $player->born;
			$data['goals'] = $player->goals;
			$data['trans'] = $player->trans;
			$data['archive_id'] = $values['archive_id'];
			$id = $this->playersRepository->insert($data);
			$player_id[$player->id] = $id;
		}
		
		$data = array();
		$rows = $this->tablesRepository->getAsArray();
		foreach ($rows as $table) {
			$data['team_id'] = $team_id[$table->team_id];
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

		$data = array();
		$rows = $this->punishmentsRepository->getAsArray();
		foreach ($rows as $pun) {
			$data['player_id'] = $player_id[$pun->player_id];
			$data['archive_id'] = $values['archive_id'];
			$this->punishmentsRepository->insert($data);
		}

		$data = array();
		$rows = $this->fightsRepository->getAsArray();
		foreach ($rows as $fight) {
			$data['round_id'] = $round_id[$fight->round_id];
			$data['team1_id'] = $team_id[$fight->team1_id];
			$data['team2_id'] = $team_id[$fight->team2_id];
			$data['score1'] = $fight->score1;
			$data['score2'] = $fight->score2;
			$data['st_third_1'] = $fight->st_third_1;
			$data['st_third_2'] = $fight->st_third_2;
			$data['nd_third_1'] = $fight->nd_third_1;
			$data['nd_third_2'] = $fight->nd_third_2;
			$data['th_third_1'] = $fight->th_third_1;
			$data['th_third_2'] = $fight->th_third_2;
			$data['archive_id'] = $values['archive_id'];
			$this->fightsRepository->insert($data);
		}

		$this->redirect('all#nav');
	}

	public function formCancelled() {
		$this->redirect('all#nav');
	}
}