<?php

namespace App\Presenters;

use App\FormHelper;
use App\Model\FightsRepository;
use App\Model\LinksRepository;
use App\Model\SponsorsRepository;
use App\Model\TablesRepository;
use App\Model\TeamsRepository;
use Nette\Application\UI\Form;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;

class FightsPresenter extends BasePresenter
{
  const FIGHT_NOT_FOUND = 'Fight not found';

  /** @var ActiveRow */
  private $roundRow;

  /** @var ActiveRow */
  private $fightRow;

  /** @var ActiveRow */
  private $seasonRow;

  /** @var ActiveRow */
  private $team1;

  /** @var ActiveRow */
  private $team2;

  /** @var FightsRepository */
  private $fightsRepository;

  /** @var TablesRepository */
  private $tablesRepository;

  public function __construct(
    LinksRepository $linksRepository,
    SponsorsRepository $sponsorsRepository,
    TeamsRepository $teamsRepository,
    FightsRepository $fightsRepository,
    TablesRepository $tablesRepository
  )
  {
    parent::__construct($linksRepository, $sponsorsRepository, $teamsRepository);
    $this->fightsRepository = $fightsRepository;
    $this->tablesRepository = $tablesRepository;
  }

  public function actionEdit(int $id): void
  {
    $this->userIsLogged();
    $this->fightRow = $this->fightsRepository->findById($id);
    $this->roundRow = $this->fightRow->ref('rounds', 'round_id');

    if (!$this->fightRow || !$this->fightRow->is_present) {
      throw new BadRequestException(self::FIGHT_NOT_FOUND);
    }
  }

  public function renderEdit(int $id): void
  {
    $this->template->round = $this->roundRow;
    $this->getComponent('editForm')->setDefaults($this->fightRow);
  }

  public function actionRemove(int $id): void
  {
    $this->userIsLogged();
    $this->fightRow = $this->fightsRepository->findById($id);
    $this->roundRow = $this->fightRow->ref('rounds', 'round_id');

    if (!$this->fightRow || !$this->fightRow->is_present) {
      throw new BadRequestException(self::FIGHT_NOT_FOUND);
    }
  }

  public function renderRemove(int $id): void
  {
    $this->template->fight = $this->fightRow;
  }

  public function actionArchView(int $id, $param): void
  {
    $this->roundRow = $this->roundsRepository->findById($param);
    $this->seasonRow = $this->seasonsRepository->findById($id);

    if (!$this->roundRow || !$this->roundRow->is_present) {
      throw new BadRequestException($this->error);
    }
  }

  public function renderArchView(int $id, $param): void
  {
    $this->template->fights = $this->fightsRepository
            ->findByValue('round_id', $param)
            ->where('archive_id', $id);
    $this->template->round = $this->roundRow;
    $this->template->archive = $this->roundRow->ref('archive', 'archive_id');
  }

  protected function createComponentEditForm(): Form
  {
    $teams = $this->teamsRepository->getTeams();
    $form = new Form;
    $form->addSelect('team1_id', 'Tím 1', $teams);
    $form->addText('score1', 'Skóre 1');
    $form->addSelect('team2_id', 'Tím 2', $teams);
    $form->addText('score2', 'Skóre 2');
    $form->addHidden('round_id', (string) $this->roundRow->id);
    $form->addSubmit('save', 'Uložiť');
    $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  /**
   * Component for creating a remove form
   * @return Nette\Application\UI\Form
   */
  protected function createComponentRemoveForm(): Form
  {
    $form = new Form;
    $form->addSubmit('remove', 'Odstrániť')
          ->setAttribute('class', self::BTN_DANGER)
          ->onClick[] = [$this, self::SUBMITTED_REMOVE_FORM];
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->onClick[] = [$this, self::FORM_CANCELLED];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  public function submittedAddForm(Form $form, ArrayHash $values)
  {
    if ($values->team1_id === $values->team2_id)
    {
      $form->addError('Zvoľte dva rozdielne tímy.');
      return false;
    }

    $fight = $this->fightsRepository->insert($values);
    /*
    $round = $fight->ref('rounds', 'round_id');
    $this->updateTableRows($values, $type);
    $this->updateTablePoints($values, $type);
    $this->updateTableGoals($values, $type);
    */
    $this->flashMessage('Zápas bol pridaný', 'success');
    $this->redirect('Rounds:view', $round->id);
  }

  public function submittedEditForm(Form $form, ArrayHash $values)
  {
    if ($values->team1_id == $values->team2_id) {
      $form->addError('Zvoľte dva rozdielne tímy.');
      return false;
    }
    $this->fightRow->update($values);
    $this->flashMessage('Zápas bol upravený', 'success');
    $this->redirect('Rounds:view', $this->roundRow->id);
  }

  public function submittedRemoveForm(): void
  {
    $this->fightsRepository->remove($this->fightRow->id);
    $this->flashMessage('Zápas bol odstránený', 'success');
    $this->redirect('Rounds:view', $this->roundRow->id);
  }

  public function updateTableRows($values, $type, $value = 1): void
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

  public function updateTablePoints($values, $type, $column = 'points'): void
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

  public function updateTableGoals($values, $type): void
  {
    $this->tablesRepository->incTabVal($values['team1_id'], $type, 'score1', $values['score1']);
    $this->tablesRepository->incTabVal($values['team1_id'], $type, 'score2', $values['score2']);
    $this->tablesRepository->incTabVal($values['team2_id'], $type, 'score1', $values['score2']);
    $this->tablesRepository->incTabVal($values['team2_id'], $type, 'score2', $values['score1']);
  }

  public function formCancelled(): void
  {
    $this->redirect('Rounds:view', $this->roundRow->id);
  }

}
