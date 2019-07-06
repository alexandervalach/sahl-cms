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

  /** @var ArrayHash */
  private $items;

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
    $data = [];

    foreach ($fights as $fight)
    {
      $data[$fight->id]['fight'] = $fight;
      $data[$fight->id]['team1'] = $fight->ref('teams', 'team1_id');
      $data[$fight->id]['team2'] = $fight->ref('teams', 'team2_id');;
      $homeGoals = $fight->related('goals')->where('is_home_player', 1)->order('number DESC');
      $guestGoals = $fight->related('goals')->where('is_home_player', 0)->order('number DESC');
      $data[$fight->id]['homeGoals'] = [];
      $data[$fight->id]['guestGoals'] = [];

      foreach ($homeGoals as $goal)
      {
        $data[$fight->id]['homeGoals'][$goal->id]['goal'] = $goal;
        $data[$fight->id]['homeGoals'][$goal->id]['player'] = $goal->ref('players', 'player_id');
      }

      foreach ($guestGoals as $goal)
      {
        $data[$fight->id]['guestGoals'][$goal->id]['goal'] = $goal;
        $data[$fight->id]['guestGoals'][$goal->id]['player'] = $goal->ref('players', 'player_id');
      }

      // Determining CSS bootstrap classes
      if ($fight->score1 > $fight->score2)
      {
        $data[$fight->id]['class1'] = 'text-success';
        $data[$fight->id]['class2'] = 'text-danger';
      }
      else if ($fight->score1 < $fight->score2)
      {
        $data[$fight->id]['class1'] = 'text-danger';
        $data[$fight->id]['class2'] = 'text-success';
      }
      else
      {
        $data[$fight->id]['class1'] = '';
        $data[$fight->id]['class2'] = '';
      }
    }

    $this->items = ArrayHash::from($data);

    if ($this->user->loggedIn)
    {
      $this->getComponent('roundForm')->setDefaults($this->roundRow);
		}
  }

  public function renderView(int $id): void
  {
		$this->template->items = $this->items;
		$this->template->round = $this->roundRow;
	}

  public function actionArchAll($id): void
  {
    $this->seasonRow = $this->seasonsRepository->findById($id);

    if (!$this->seasonRow || !$this->seasonRow->is_present)
    {
			throw new BadRequestException(self::SEASON_NOT_FOUND);
    }
	}

  public function renderArchAll($id): void
  {
		$this->template->rounds = $this->roundsRepository->getArchived($id);
		$this->template->season = $this->seasonRow;
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

  public function renderArchView($archiveId, $id): void
  {
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
    $form->addHidden('round_id', (string) $this->roundRow->id);
    $form->addSubmit('save', 'Uložiť');
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, 'submittedAddFightForm'];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
	}

  public function submittedAddFightForm(Form $form, ArrayHash $values)
  {
    if ($values->team1_id === $values->team2_id)
    {
      $form->addError('Zvoľte dva rozdielne tímy.');
      return false;
    }

    // Insert data into database tables
    $this->fightsRepository->insert($values);
    /*
    $this->updateTableRows($values, $type);
    $this->updateTablePoints($values, $type);
    $this->updateTableGoals($values, $type);
    */
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
    $state1 = $state2 = 'tram';

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
