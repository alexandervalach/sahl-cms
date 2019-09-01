<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\FormHelper;
use App\Model\GroupsRepository;
use App\Model\LinksRepository;
use App\Model\PlayersSeasonsGroupsTeamsRepository;
use App\Model\SeasonsGroupsRepository;
use App\Model\SponsorsRepository;
use App\Model\TeamsRepository;
use App\Model\GoalsRepository;
use App\Model\FightsRepository;
use App\Model\RoundsRepository;
use App\Model\PlayersRepository;
use App\Model\SeasonsGroupsTeamsRepository;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\ArrayHash;

/**
 * Class GoalsPresenter
 * @package App\Presenters
 */
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

  /**
   * @var PlayersSeasonsGroupsTeamsRepository
   */
  private $playersSeasonsGroupsTeamsRepository;

  /**
   * GoalsPresenter constructor.
   * @param LinksRepository $linksRepository
   * @param SponsorsRepository $sponsorsRepository
   * @param TeamsRepository $teamsRepository
   * @param GoalsRepository $goalsRepository
   * @param FightsRepository $fightsRepository
   * @param PlayersRepository $playersRepository
   * @param RoundsRepository $roundsRepository
   * @param SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository
   * @param GroupsRepository $groupsRepository
   * @param SeasonsGroupsRepository $seasonsGroupsRepository
   * @param PlayersSeasonsGroupsTeamsRepository $playersSeasonsGroupsTeamsRepository
   */
  public function __construct(
      LinksRepository $linksRepository,
      SponsorsRepository $sponsorsRepository,
      TeamsRepository $teamsRepository,
      GoalsRepository $goalsRepository,
      FightsRepository $fightsRepository,
      PlayersRepository $playersRepository,
      RoundsRepository $roundsRepository,
      SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository,
      GroupsRepository $groupsRepository,
      SeasonsGroupsRepository $seasonsGroupsRepository,
      PlayersSeasonsGroupsTeamsRepository $playersSeasonsGroupsTeamsRepository
  )
  {
    parent::__construct($groupsRepository, $linksRepository, $sponsorsRepository, $teamsRepository,
        $seasonsGroupsRepository, $seasonsGroupsTeamsRepository);
    $this->goalsRepository = $goalsRepository;
    $this->fightsRepository = $fightsRepository;
    $this->playersRepository = $playersRepository;
    $this->roundsRepository = $roundsRepository;
    $this->playersSeasonsGroupsTeamsRepository = $playersSeasonsGroupsTeamsRepository;
  }

  /**
   * @param int $id
   */
  public function actionView(int $id): void
  {
    $this->fightRow = $this->fightsRepository->findById($id);
    $this->roundRow = $this->roundsRepository->findById($this->fightRow->round_id);

    if (!$this->fightRow) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }

    if (!$this->roundRow) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }
  }

  /**
   * @param int $id
   */
  public function renderView(int $id): void
  {
    $this->template->fight = $this->fightRow;
    $this->template->goals = ArrayHash::from($this->goalsRepository->fetchForFight($this->fightRow->id));
    $this->template->team1 = $this->fightRow->ref('team1_id');
    $this->template->team2 = $this->fightRow->ref('team2_id');
  }

  /**
   * @param int $id
   */
  public function actionEdit(int $id): void
  {
    $this->userIsLogged();
    $this->goalRow = $this->goalsRepository->findById($id);
    $this->fightRow = $this->goalRow->ref('fights', 'fight_id');
  }

  /**
   * @param int $id
   */
  public function renderEdit(int $id): void
  {
    if (!$this->goalRow) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }

    $this->template->goal = $this->goalRow;
    $this->template->player = $this->goalRow->ref('players', 'player_id');

    if ($this->isLoggedIn()) {
      $this->getComponent(self::EDIT_FORM)->setDefaults($this->goalRow);
    }
  }

  /**
   * @param int $id
   */
  public function actionRemove(int $id): void
  {
    $this->userIsLogged();
    $this->goalRow = $this->goalsRepository->findById($id);
    $this->fightRow = $this->goalRow->ref('fights', 'fight_id');
    $this->submittedRemove();
  }

  /**
   * @return Form
   */
  protected function createComponentAddForm(): Form
  {
    $players = $this->teamPlayersHelper($this->fightRow);
    $form = new Form;
    $form->addHidden('fight_id', (string) $this->fightRow->id);
    $form->addSelect('player_season_group_team_id', 'Hráči', $players);
    $form->addText('number', 'Počet gólov')
          ->setDefaultValue(1)
          ->setAttribute('placeholder', 0)
          ->addRule(Form::FILLED, 'Ešte treba vyplniť počet gólov')
          ->addRule(Form::INTEGER, 'Počet gólov musí byť celé číslo.');
    // $form->addCheckbox('is_home_player', ' Hráč domáceho tímu');
    $form->addSubmit('save', 'Uložiť');
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, self::SUBMITTED_ADD_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  /**
   * @return Form
   */
  protected function createComponentEditForm(): Form
  {
    $form = new Form;
    $form->addText('number', 'Počet gólov')
          ->setAttribute('placeholder', 0)
          ->addRule(Form::FILLED, 'Ešte treba vyplniť počet gólov.')
          ->addRule(Form::INTEGER, 'Počet gólov musí byť celé číslo.');
    $form->addCheckbox('is_home_player', ' Hráč domáceho tímu');
    $form->addSubmit('save', 'Uložiť');
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  /**
   * @param Form $form
   * @param ArrayHash $values
   * @return Form
   */
  public function submittedAddForm(Form $form, ArrayHash $values): Form
  {
    $this->goalsRepository->insert($values);
    $player = $this->playersSeasonsGroupsTeamsRepository->findById($values->player_season_group_team_id);
    $player->update(array('goals' => $player->goals + $values->number));

    $this->flashMessage('Góly boli pridané', self::SUCCESS);
    $this->redirect('view', $this->fightRow->id);
  }

  /**
   * @param Form $form
   * @param ArrayHash $values
   * @return Form
   */
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

  /**
   * @return Form
   */
  public function submittedRemove(): Form
  {
    $player = $this->playersSeasonsGroupsTeamsRepository->findById($this->goalRow->player_season_group_team_id);
    $player->update(array('goals' => $player->goals - $this->goalRow->number));

    $this->goalsRepository->remove($this->goalRow->id);
    $this->flashMessage('Góly boli odpočítané', self::SUCCESS);
    $this->redirect('view', $this->fightRow->id);
  }

  /**
   *
   */
  public function formCancelled(): void
  {
    $this->redirect('view', $this->goalRow->fight_id);
  }

  /**
   * @param ActiveRow $row
   * @return array
   */
  protected function teamPlayersHelper(ActiveRow $row): array
  {
    return $this->teamsRepository->fetchPlayersForTeams($row->team1_id, $row->team2_id);
  }

}
