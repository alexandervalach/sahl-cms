<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\FormHelper;
use App\Model\LinksRepository;
use App\Model\SponsorsRepository;
use App\Model\TeamsRepository;
use App\Model\GoalsRepository;
use App\Model\FightsRepository;
use App\Model\RoundsRepository;
use App\Model\PlayersRepository;
use App\Model\SeasonsTeamsRepository;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\ArrayHash;

class GoalsPresenter extends BasePresenter
{
  /** @var ActiveRow */
  private $goalRow;

  /** @var ActiveRow */
  private $fightRow;

  /** @var ActiveRow */
  private $roundRow;

  /** @var ActiveRow */
  private $team1;

  /** @var ActiveRow */
  private $team2;

  /** @var GoalsRepository */
  private $goalsRepository;

  /** @var FightsRepository */
  private $fightsRepository;

  /** @var RoundsRepository */
  private $roundsRepository;

  /** @var PlayersRepository */
  private $playersRepository;

  public function __construct(
    LinksRepository $linksRepository,
    SponsorsRepository $sponsorsRepository,
    TeamsRepository $teamsRepository,
    GoalsRepository $goalsRepository,
    FightsRepository $fightsRepository,
    PlayersRepository $playersRepository,
    RoundsRepository $roundsRepository,
    SeasonsTeamsRepository $seasonsTeamsRepository
  )
  {
    parent::__construct($linksRepository, $sponsorsRepository, $teamsRepository, $seasonsTeamsRepository);
    $this->goalsRepository = $goalsRepository;
    $this->fightsRepository = $fightsRepository;
    $this->playersRepository = $playersRepository;
    $this->roundsRepository = $roundsRepository;
  }

  public function actionView(int $id): void
  {
    $this->fightRow = $this->fightsRepository->findById($id);
    $this->roundRow = $this->roundsRepository->findById($this->fightRow->round_id);
  }

  public function renderView(int $id): void
  {
    $this->template->fight = $this->fightRow;
    $this->template->goals = $this->goalsRepository->findByValue('fight_id', $this->fightRow)
            ->order('home DESC, goals DESC');
    $this->template->team1 = $this->fightRow->ref('team1_id');
    $this->template->team2 = $this->fightRow->ref('team2_id');
  }

  public function actionEdit(int $id): void
  {
    $this->userIsLogged();
    $this->goalRow = $this->goalsRepository->findById($id);
    $this->fightRow = $this->goalRow->ref('fight_id');
  }

  public function renderEdit(int $id): void
  {
    if (!$this->goalRow || !$this->goalRow->is_present) {
      throw new BadRequestException(self::PLAYER_NOT_FOUND);
    }
    $this->template->goal = $this->goalRow;
    $this->template->player = $this->goalRow->ref('players', 'player_id');

    if ($this->isLoggedIn()) {
      $this->getComponent(self::EDIT_FORM)->setDefaults($this->goalRow);
    }
  }

  public function actionRemove(int $id): void
  {
    $this->userIsLogged();
    $this->goalRow = $this->goalsRepository->findById($id);
    $this->fightRow = $this->goalRow->ref('fights', 'fight_id');
    $this->submittedRemove();
  }

  /**
   * @return Nette\Aplication\UI\Form
   */
  protected function createComponentAddForm(): Form
  {
    $players = $this->teamPlayersHelper($this->fightRow);
    $form = new Form;
    $form->addHidden('fight_id', (string) $this->fightRow->id);
    $form->addSelect('player_id', 'Hráči', $players);
    $form->addText('number', 'Počet gólov')
          ->setDefaultValue(1)
          ->setAttribute('placeholder', 0)
          ->addRule(Form::FILLED, 'Ešte treba vyplniť počet gólov')
          ->addRule(Form::INTEGER, 'Počet gólov musí byť celé číslo.');
    $form->addCheckbox('home', ' Hráč domáceho tímu');
    $form->addSubmit('save', 'Uložiť');
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, self::SUBMITTED_ADD_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  protected function createComponentEditForm(): Form
  {
    $form = new Form;
    $form->addText('number', 'Počet gólov')
          ->setAttribute('placeholder', 0)
          ->addRule(Form::FILLED, 'Ešte treba vyplniť počet gólov.')
          ->addRule(Form::INTEGER, 'Počet gólov musí byť celé číslo.');
    $form->addCheckbox('home', ' Hráč domáceho tímu');
    $form->addSubmit('save', 'Uložiť');
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  public function submittedAddForm(Form $form, ArrayHash $values): Form
  {
    $this->goalsRepository->insert($values);
    $player = $this->playersRepository->findById($values->player_id);
    $numOfGoals = $player->goals + $values->number;
    $goals = array('number' => $numOfGoals);
    $player->update($goals);

    $this->flashMessage("Góly boli pridané", self::SUCCESS);
    $this->redirect('view', $this->fightRow->id);
  }

  public function submittedEditForm(Form $form, ArrayHash $values): Form
  {
    $goalDifference = $values->goals - $this->goalRow->goals;
    $this->goalRow->update($values);

    $player = $this->playersRepository->findById($this->goalRow->player_id);
    $numOfGoals = $player->goals + $goalDifference;
    $goals = array('number' => $numOfGoals);
    $player->update($goals);

    $this->flashMessage('Góly boli upravené', self::SUCCESS);
    $this->redirect('view', $this->fightRow->id);
  }

  public function submittedRemove(): Form
  {
    $player = $this->playersRepository->findById($this->goalRow->player_id);
    $numOfGoals = $player->goals - $this->goalRow->goals;
    $goals = array('goals' => $numOfGoals);
    $player->update($goals);

    $this->goalRow->delete();
    $this->flashMessage('Góly boli odpočítané', self::SUCCESS);
    $this->redirect('view', $this->fightRow->id);
  }

  public function formCancelled(): void
  {
    $this->redirect('view', $this->goalRow->fight_id);
  }

  protected function teamPlayersHelper(ActiveRow $row): array
  {
    $team1 = $this->teamsRepository->findById($row->team1_id);
    $team2 = $this->teamsRepository->findById($row->team2_id);
    $players1 = $this->teamsRepository->getPlayersForTeam($team1->id);
    $players2 = $this->teamsRepository->getPlayersForTeam($team2->id);
    $players = array_replace($players1, $players2);
    return $players;
  }

}
