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
		$team_id = array();
		$player_id = array();
		$arch_id = array( 'archive_id' => $values['archive_id'] );

		$rounds = $this->roundsRepository->findAll();
		$events = $this->eventsRepository->findAll();
		$rules = $this->rulesRepository->findAll();

		$this->addToArchive($rounds, $arch_id);
		$this->addToArchive($events, $arch_id);
		$this->addToArchive($rules, $arch_id);

		// Vytvoríme duplicitné záznamy tímov s novým archive id
		$teams = $this->teamsRepository->getAsArray();
		foreach ($teams as $team) {
			$data = array (
				'name' => $team->name,
				'image' => $team->image,
				'archive_id' => $values['archive_id']
			);
			$id = $this->teamsRepository->insert($data);
			$team_id[$team->id] = $id;
		}

		// Vytvoríme duplicitné záznamy o hráčoch s novým archive_id
		$data = array();
		$players = $this->playersRepository->getAsArray();
		foreach ($players as $player) {
			if (isset($team_id[$player->team_id])) {
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
		}
		
		$tables = $this->tablesRepository->findAll();
		$data = array ( 
			'team_id' => null,
			'archive_id' => $values['archive_id']
		);

		foreach ($tables as $table) {
			if (isset($team_id[$table->team_id])) {
				$data['team_id'] = $team_id[$table->team_id];
				$table->update($data);
			}
		}

		$puns = $this->punishmentsRepository->findAll();
		$data = array (
			'player_id' => null,
			'archive_id' => $values['archive_id']
		);

		foreach ($puns as $pun) {
			if (isset($player_id[$pun->player_id])) {
				$data['player_id'] = $player_id[$pun->player_id];
				$pun->update($data);
			}
		}

		$fights = $this->fightsRepository->findAll();
		$data = array( 
			'team1_id' => null,
			'team2_id' => 1,
			'archive_id' => $values['archive_id']
		);

		foreach ($fights as $fight) {
			if (isset($team_id[$fight->team1_id]) && isset($team_id[$fight->team2_id])) {
				$data['team1_id'] = $team_id[$fight->team1_id];
				$data['team2_id'] = $team_id[$fight->team2_id];
				$fight->update($data);
			}
		}

		$goals = $this->goalsRepository->findAll();
		$data = array(
			'player_id' => null,
			'archive_id' =>  $values['archive_id']
		);

		foreach ($goals as $goal) {
			if (isset($player_id[$goal->player_id])) {
				$data['player_id'] = $player_id[$goal->player_id];
				$goal->update($data);
			}
		}

		$this->redirect('all#nav');
	}

	public function formCancelled() {
		$this->redirect('all#nav');
	}

	private function addToArchive($items, $arch_id) {
		foreach ($items as $item) {
			$item->update($arch_id);
		}
	}
}