<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\ArrayHash;

class RoundsPresenter extends BasePresenter
{
	/** @var ActiveRow */
	private $roundRow;

	/** @var ActiveRow */
  private $seasonRow;

  /** @var array */
  private $fights;

  public function renderAll(): void
  {
		$this->template->rounds = $this->roundsRepository->getArchived();
	}

  public function actionView(int $id): void
  {
		$this->roundRow = $this->roundsRepository->findById($id);
    if (!$this->roundRow || !$this->roundRow->is_present)
    {
			throw new BadRequestException(self::ROUND_NOT_FOUND);
    }

    $fights = $this->roundRow->related('fights')->where('is_present', 1)->order('id DESC');
    $this->fights = [];

    foreach ($fights as $fight)
    {
      $playersSeasonsTeams1 = $fight->ref('players_seasons_teams', 'players_seasons_teams_id');
      $playersSeasonsTeams2 = $fight->ref('players_seasons_teams', 'players_seasons_teams_id');

      $seasonsTeams1 = $playersSeasonsTeams1;
      $seasonsTeams2 = $playersSeasonsTeams2;

      $this->fights[$fight->id]['team_1'] = $playersSeasonsTeams1;
      $this->fights[$fight->id]['team_2'] = $playersSeasonsTeams2;
      $this->fights[$fight->id]['home_goals'] = $fight->related('goals')->where('is_home_player', 1)->order('goals DESC');
      $this->fights[$fight->id]['guest_goals'] = $fight->related('goals')->where('is_home_player', 0)->order('goals DESC');

      if ($fight->score1 > $fight->score2)
      {
        $this->fights[$fight->id]['state_1'] = 'text-success';
        $this->fights[$fight->id]['state_2'] = 'text-danger';
      }
      else if ($fight->score1 < $fight->score2)
      {
        $this->fights[$fight->id]['state_1'] = 'text-danger';
        $this->fights[$fight->id]['state_2'] = 'text-success';
      }
      else
      {
        $this->fights[$fight->id]['state_1'] = '';
        $this->fights[$fight->id]['state_2'] = '';
      }
    }

    if ($this->user->loggedIn)
    {
      $this->getComponent('roundForm')->setDefaults($this->roundRow);
		}
  }

  public function renderView(int $id): void
  {
		$this->template->fights = $this->fights;
		$this->template->round = $this->roundRow;
	}

  public function actionArchAll($id): void
  {
		$this->archRow = $this->seasonsRepository->findById($id);
	}

  public function renderArchAll($id): void
  {
		$this->template->rounds = $this->roundsRepository->getArchived($id);
		$this->template->archive = $this->archRow;
	}

  public function actionArchView(int $seasonId, int $id): void
  {
    $this->seasonRow = $this->seasonsRepository->findById($seasonId);
    if (!$this->seasonRow || !$this->seasonRow->is_present) {
      throw new BadRequestException(self::SEASON_NOT_FOUND);
    }

    $this->roundRow = $this->roundsRepository->findById($id);
    if (!$this->roundRow || !$this->seasonRow->is_present) {
      throw new BadRequestException(self::ROUND_NOT_FOUND);
    }
	}

	public function renderArchView($archiveId, $id) {
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

  protected function createComponentRoundForm(): Form
  {
    $form = new Form;
    $form->addText('label', 'Názov')
          ->setAttribute('placeholder', '1.kolo')
          ->addRule(Form::FILLED, 'Ešte treba vyplniť názov kola');
    $form->addSubmit('save', 'Uložiť');
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, 'submittedRoundForm'];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
	}

  protected function createComponentAddFightForm(): Form
  {
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
    $this->redirect('view', $this->roundRow->id);
	}

  public function submittedRoundForm(Form $form, ArrayHash $values): void
  {
    $id = $this->getParameter('id');

    if ($id && $this->roundRow) {
      $this->roundRow->update($values);
    } else {
      $this->roundRow = $this->roundsRepository->insert($values);
    }

    $this->flashMessage(self::CHANGES_SAVED_SUCCESSFULLY, self::SUCCESS);
    $this->redirect('view', $this->roundRow->id);
	}

  public function submittedRemoveForm(): void
  {
    $fights = $this->fightsRepository->getForRound($this->roundRow->id);

    foreach ($fights as $fight) {
      $this->fightsRepository->remove($fight->id);
    }

    $this->roundsRepository->remove($this->roundRow->id);
    $this->flashMessage('Kolo bolo odstránené', self::SUCCESS);
    $this->redirect('all');
	}

  protected function updateTableRows($values, $type, $value = 1): void
  {
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

  protected function updateTablePoints($values, $type, $column = 'points'): void
  {
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

  protected function updateTableGoals($values, $type): void
  {
    $this->tablesRepository->incTabVal($values['team1_id'], $type, 'score1', $values['score1']);
    $this->tablesRepository->incTabVal($values['team1_id'], $type, 'score2', $values['score2']);
    $this->tablesRepository->incTabVal($values['team2_id'], $type, 'score1', $values['score2']);
    $this->tablesRepository->incTabVal($values['team2_id'], $type, 'score2', $values['score1']);
	}

}
