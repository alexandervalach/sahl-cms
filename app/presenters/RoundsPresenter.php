<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;

class RoundsPresenter extends BasePresenter {

	/** @var ActiveRow */
	private $roundRow;

	/** @var ActiveRow */
	private $archRow;

	public function renderAll() {
		$this->template->rounds = $this->roundsRepository->getArchived();
	}

	public function actionView($id) {
		$this->roundRow = $this->roundsRepository->findById($id);
		if (!$this->roundRow) {
			throw new BadRequestException(self::ROUND_NOT_FOUND);
		}
	}

	public function renderView($id) {
		$i = 0;
		$fight_data = array();
		$fights = $this->roundRow->related('fights')->order('id DESC');

		foreach ($fights as $fight) {
      $fight_data[$i]['team_1'] = $fight->ref('teams', 'team1_id');
      $fight_data[$i]['team_2'] = $fight->ref('teams', 'team2_id');
      $fight_data[$i]['home_goals'] = $fight->related('goals')->where('home', 1)->order('goals DESC');
      $fight_data[$i]['guest_goals'] = $fight->related('goals')->where('home', 0)->order('goals DESC');

      if ($fight->score1 > $fight->score2) {
          $fight_data[$i]['state_1'] = 'text-success';
          $fight_data[$i]['state_2'] = 'text-danger';
      } else if ($fight->score1 < $fight->score2) {
          $fight_data[$i]['state_1'] = 'text-danger';
          $fight_data[$i]['state_2'] = 'text-success';
      } else {
          $fight_data[$i]['state_1'] = $fight_data[$i]['state_2'] = '';
      }
      $i++;
		}

		$this->template->fights = $fights;
		$this->template->fight_data = $fight_data;
		$this->template->i = 0;
		$this->template->round = $this->roundRow;

		if ($this->user->loggedIn) {
      $this->getComponent(self::EDIT_FORM)->setDefaults($this->roundRow);
		}
	}

	public function actionArchAll($id) {
		$this->archRow = $this->seasonsRepository->findById($id);
	}

	public function renderArchAll($id) {
		$this->template->rounds = $this->roundsRepository->getArchived($id);
		$this->template->archive = $this->archRow;
	}

	public function actionArchView($archiveId, $id) {
		$this->archRow = $this->seasonsRepository->findById($archiveId);
		$this->roundRow = $this->roundsRepository->findById($id);
	}

	public function renderArchView($archiveId, $id) {
			if (!$this->roundRow) {
					throw new BadRequestException(self::ROUND_NOT_FOUND);
			}
			if (!$this->archRow) {
					throw new BadRequestException(self::ARCHIVE_NOT_FOUND);
			}

			$i = 0;
			$fightData = array();
			$fights = $this->roundRow->related('fights');

			foreach ($fights as $fight) {
					$fightData[$i]['team_1'] = $fight->ref('teams', 'team1_id');
					$fightData[$i]['team_2'] = $fight->ref('teams', 'team2_id');
					$fightData[$i]['home_goals'] = $fight->related('goals')->where('home', 1)->order('goals DESC');
					$fightData[$i]['guest_goals'] = $fight->related('goals')->where('home', 0)->order('goals DESC');

					if ($fight->score1 > $fight->score2) {
							$fightData[$i]['state_1'] = 'text-success';
							$fightData[$i]['state_2'] = 'text-danger';
					} else if ($fight->score1 < $fight->score2) {
							$fightData[$i]['state_1'] = 'text-danger';
							$fightData[$i]['state_2'] = 'text-success';
					} else {
							$fightData[$i]['state_1'] = $fight_data[$i]['state_2'] = '';
					}
					$i++;
			}

			$this->template->fights = $fights;
			$this->template->fightData = $fightData;
			$this->template->i = 0;
			$this->template->round = $this->roundRow;
			$this->template->archive = $this->archRow;
	}

	protected function createComponentAddForm() {
			$form = new Form;
			$form->addText('label', 'Názov')
						->setAttribute('placeholder', '1.kolo')
						->addRule(Form::FILLED, 'Ešte treba vyplniť názov kola');
			$form->addSubmit('save', 'Uložiť');
			$form->addSubmit('cancel', 'Zrušiť')
						->setAttribute('class', self::BTN_WARNING)
						->setAttribute('data-dismiss', 'modal');
			$form->onSuccess[] = [$this, self::SUBMITTED_ADD_FORM];
			FormHelper::setBootstrapFormRenderer($form);
			return $form;
	}

	protected function createComponentEditForm() {
    $form = new Form;
    $form->addText('label', 'Názov')
          ->setAttribute('placeholder', '1.kolo')
          ->addRule(Form::MAX_LENGTH, 'Dĺžka názvu môže byť len 50 znakov', 50)
          ->addRule(Form::FILLED, 'Názov je povinné pole');
    $form->addSubmit('save', 'Uložiť')
          ->setAttribute('class', 'btn btn-large btn-success');
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
	}

	protected function createComponentRemoveForm() {
    $form = new Form;
    $form->addSubmit('remove', 'Odstrániť')
          ->setAttribute('class', self::BTN_DANGER);
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, self::SUBMITTED_REMOVE_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
	}

	protected function createComponentAddFightForm() {
    $teams = $this->teamsRepository->getTeams();
    $form = new Form;
    $form->addSelect('team1_id', 'Tím 1', $teams);
    $form->addText('score1', 'Skóre tímu 1')
          ->setAttribute('placeholder', '1');
    $form->addSelect('team2_id', 'Tím 2', $teams);
    $form->addText('score2', 'Skóre tímu 2')
          ->setAttribute('placeholder', '0');
    $form->addCheckbox('type', ' Označiť zápas ako Play Off');
    $form->addSubmit('save', 'Uložiť');
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, 'submittedAddFightForm'];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
	}

	public function submittedAddFightForm(Form $form, $values) {
    if ($values['team1_id'] == $values['team2_id']) {
      $form->addError('Zvoľte dva rozdielne tímy.');
      return false;
    }
    $values['round_id'] = $this->roundRow;

    $values['type'] ? $type = 1 : $type = 2;
    unset($values['type']);

    $this->fightsRepository->insert($values);
    $this->updateTableRows($values, $type);
    $this->updateTablePoints($values, $type);
    $this->updateTableGoals($values, $type);
    $this->flashMessage('Zápas bol pridaný', self::SUCCESS);
    $this->redirect('Rounds:view', $this->roundRow);
	}

	public function submittedAddForm(Form $form, $values) {
    $this->roundsRepository->insert($values);
    $this->flashMessage('Kolo bolo pridané', self::SUCCESS);
    $this->redirect('all');
	}

	public function submittedEditForm(Form $form, $values) {
    $this->roundRow->update($values);
    $this->flashMessage('Kolo bolo upravené', self::SUCCESS);
    $this->redirect('view', $this->roundRow);
	}

	public function submittedRemoveForm() {
    $fights = $this->fightsRepository->getForRound($this->roundRow);

    foreach ($fights as $fight) {
      $this->fightsRepository->remove($fight);
    }

    $this->roundsRepository->remove($this->roundRow);
    $this->flashMessage('Kolo bolo odstránené', self::SUCCESS);
    $this->redirect('all');
	}

	protected function updateTableRows($values, $type, $value = 1) {
    $state1 = 'tram';
    $state2 = 'tram';

    if ($values['score1'] > $values['score2']) {
      $state1 = 'win';
      $state2 = 'lost';
    } elseif ($values['score1'] < $values['score2']) {
      $state1 = 'lost';
      $state2 = 'win';
    }
    $this->tablesRepository->incTabVal($values['team1_id'], $type, $state1, $value);
    $this->tablesRepository->incTabVal($values['team2_id'], $type, $state2, $value);
    $this->tablesRepository->updateFights($values['team1_id'], $type);
    $this->tablesRepository->updateFights($values['team2_id'], $type);
	}

	protected function updateTablePoints($values, $type, $column = 'points') {
    if ($values['score1'] > $values['score2']) {
      $this->tablesRepository->incTabVal($values['team1_id'], $type, $column, 2);
      $this->tablesRepository->incTabVal($values['team2_id'], $type, $column, 0);
    } elseif ($values['score1'] < $values['score2']) {
      $this->tablesRepository->incTabVal($values['team2_id'], $type, $column, 2);
      $this->tablesRepository->incTabVal($values['team1_id'], $type, $column, 0);
    } else {
      $this->tablesRepository->incTabVal($values['team2_id'], $type, $column, 1);
      $this->tablesRepository->incTabVal($values['team1_id'], $type, $column, 1);
    }
	}

	protected function updateTableGoals($values, $type) {
    $this->tablesRepository->incTabVal($values['team1_id'], $type, 'score1', $values['score1']);
    $this->tablesRepository->incTabVal($values['team1_id'], $type, 'score2', $values['score2']);
    $this->tablesRepository->incTabVal($values['team2_id'], $type, 'score1', $values['score2']);
    $this->tablesRepository->incTabVal($values['team2_id'], $type, 'score2', $values['score1']);
	}

}
