<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\Forms\RoundFormFactory;
use App\Forms\FightAddFormFactory;
use App\Forms\ModalRemoveFormFactory;
use App\Model\FightsRepository;
use App\Model\GroupsRepository;
use App\Model\LinksRepository;
use App\Model\PlayersRepository;
use App\Model\PlayersSeasonsGroupsTeamsRepository;
use App\Model\SeasonsGroupsRepository;
use App\Model\SponsorsRepository;
use App\Model\TeamsRepository;
use App\Model\RoundsRepository;
use App\Model\SeasonsGroupsTeamsRepository;
use App\Model\TablesRepository;
use App\Model\TableEntriesRepository;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\ArrayHash;

/**
 * Class RoundsPresenter
 * @package App\Presenters
 */
class RoundsPresenter extends BasePresenter
{
  /** @var ActiveRow */
  private $roundRow;

  /** @var ActiveRow */
  private $seasonRow;

  /** @var ActiveRow */
  private $groupRow;

  private $seasonGroup;

  /** @var ArrayHash */
  private $items;

  /** @var FightsRepository */
  private $fightsRepository;

  /** @var RoundsRepository */
  private $roundsRepository;

  /** @var TablesRepository */
  private $tablesRepository;

  /** @var TableEntriesRepository */
  private $tableEntriesRepository;

  /** @var RoundFormFactory */
  private $roundFormFactory;

  /** @var FightAddFormFactory */
  private $fightAddFormFactory;

  /** @var ModalRemoveFormFactory */
  private $modalRemoveFormFactory;

  /**
   * @var PlayersSeasonsGroupsTeamsRepository
   */
  private $playersSeasonsGroupsTeamsRepository;

  /**
   * @var PlayersRepository
   */
  private $playersRepository;

  /**
   * RoundsPresenter constructor.
   * @param LinksRepository $linksRepository
   * @param SponsorsRepository $sponsorsRepository
   * @param TeamsRepository $teamsRepository
   * @param RoundsRepository $roundsRepository
   * @param FightsRepository $fightsRepository
   * @param PlayersRepository $playersRepository
   * @param PlayersSeasonsGroupsTeamsRepository $playersSeasonsGroupsTeamsRepository
   * @param SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository
   * @param TablesRepository $tablesRepository
   * @param TableEntriesRepository $tableEntriesRepository
   * @param RoundFormFactory $roundFormFactory
   * @param FightAddFormFactory $fightAddFormFactory
   * @param ModalRemoveFormFactory $modalRemoveFormFactory
   * @param GroupsRepository $groupsRepository
   * @param SeasonsGroupsRepository $seasonsGroupsRepository
   */
  public function __construct(
      LinksRepository $linksRepository,
      SponsorsRepository $sponsorsRepository,
      TeamsRepository $teamsRepository,
      RoundsRepository $roundsRepository,
      FightsRepository $fightsRepository,
      PlayersRepository $playersRepository,
      PlayersSeasonsGroupsTeamsRepository $playersSeasonsGroupsTeamsRepository,
      SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository,
      TablesRepository $tablesRepository,
      TableEntriesRepository $tableEntriesRepository,
      RoundFormFactory $roundFormFactory,
      FightAddFormFactory $fightAddFormFactory,
      ModalRemoveFormFactory $modalRemoveFormFactory,
      GroupsRepository $groupsRepository,
      SeasonsGroupsRepository $seasonsGroupsRepository
  ) {
    parent::__construct($groupsRepository, $linksRepository, $sponsorsRepository, $teamsRepository,
        $seasonsGroupsRepository, $seasonsGroupsTeamsRepository);
    $this->roundsRepository = $roundsRepository;
    $this->fightsRepository = $fightsRepository;
    $this->tablesRepository = $tablesRepository;
    $this->tableEntriesRepository = $tableEntriesRepository;
    $this->roundFormFactory = $roundFormFactory;
    $this->fightAddFormFactory = $fightAddFormFactory;
    $this->modalRemoveFormFactory = $modalRemoveFormFactory;
    $this->playersSeasonsGroupsTeamsRepository = $playersSeasonsGroupsTeamsRepository;
    $this->playersRepository = $playersRepository;
  }

  /**
   *
   */
  public function renderAll(): void
  {
    $this->template->rounds = $this->roundsRepository->getArchived();
  }

  /**
   * @param int $id
   */
  public function actionView(int $id): void
  {
    $this->roundRow = $this->roundsRepository->findById($id);
    if (!$this->roundRow) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }

    $fights = $this->fightsRepository->getForRound($this->roundRow->id);
    $data = [];

    foreach ($fights as $fight) {
      $data[$fight->id]['fight'] = $fight;
      $data[$fight->id]['team1'] = $fight->ref('teams', 'team1_id');
      $data[$fight->id]['team2'] = $fight->ref('teams', 'team2_id');
      $homeGoals = $fight->related('goals')
          ->where('is_home_player', 1)
          ->where('is_present', 1)
          ->order('number DESC');
      $guestGoals = $fight->related('goals')
          ->where('is_home_player', 0)
          ->where('is_present', 1)
          ->order('number DESC');
      $data[$fight->id]['homeGoals'] = [];
      $data[$fight->id]['guestGoals'] = [];

      foreach ($homeGoals as $goal) {
        $playerSeasonGroupTeam = $this->playersSeasonsGroupsTeamsRepository->findById($goal->player_season_group_team_id);
        $data[$fight->id]['homeGoals'][$goal->id]['number'] = $goal->number;
        $data[$fight->id]['homeGoals'][$goal->id]['player'] = $this->playersRepository->findById($playerSeasonGroupTeam->player_id);
      }

      foreach ($guestGoals as $goal) {
        $playerSeasonGroupTeam = $this->playersSeasonsGroupsTeamsRepository->findById($goal->player_season_group_team_id);
        $data[$fight->id]['guestGoals'][$goal->id]['number'] = $goal->number;
        $data[$fight->id]['guestGoals'][$goal->id]['player'] = $this->playersRepository->findById($playerSeasonGroupTeam->player_id);
      }

      // Determining CSS bootstrap classes
      if ($fight->score1 > $fight->score2) {
        $data[$fight->id]['class1'] = 'text-success';
        $data[$fight->id]['class2'] = 'text-danger';
      } else if ($fight->score1 < $fight->score2) {
        $data[$fight->id]['class1'] = 'text-danger';
        $data[$fight->id]['class2'] = 'text-success';
      } else {
        $data[$fight->id]['class1'] = $data[$fight->id]['class2'] = '';
      }
    }

    $this->items = ArrayHash::from($data);

    if ($this->user->loggedIn) {
      $this['roundForm']->setDefaults($this->roundRow);
    }
  }

  /**
   * @param int $id
   */
  public function renderView(int $id): void
  {
    $this->template->items = $this->items;
    $this->template->round = $this->roundRow;
  }

  /**
   * @param int $id
   */
  public function actionArchAll(int $id): void
  {
    $this->seasonRow = $this->seasonsRepository->findById($id);

    if (!$this->seasonRow || !$this->seasonRow->is_present) {
      throw new BadRequestException(self::SEASON_NOT_FOUND);
    }
  }

  /**
   * @param int $id
   */
  public function renderArchAll(int $id): void
  {
    $this->template->rounds = $this->roundsRepository->getArchived($id);
    $this->template->season = $this->seasonRow;
  }

  /**
   * @param int $seasonId
   * @param int $id
   */
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

  /**
   * @param int $seasonId
   * @param int $id
   */
  public function renderArchView(int $seasonId, int $id): void
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
    $this->template->archive = $this->seasonRow;
  }

  /**
   * @return Form
   */
  protected function createComponentRoundForm(): Form
  {
    return $this->roundFormFactory->create(function (Form $form, ArrayHash $values) {
      $id = $this->getParameter('id');

      if ($id) {
        $this->roundRow->update($values);
      } else {
        $this->roundRow = $this->roundsRepository->insert($values);
      }

      $this->flashMessage(self::CHANGES_SAVED_SUCCESSFULLY, self::SUCCESS);
      $this->redirect('view', $this->roundRow->id);
    });
	}

  /**
   * @return Form
   */
  protected function createComponentAddFightForm(): Form
  {
    return $this->fightAddFormFactory->create(function (Form $form, ArrayHash $values) {
      if ($values->team1_id === $values->team2_id) {
        $form->addError('Zvoľte dva rozdielne tímy.');
        return false;
      }

      if (($this->groupRow = $this->findGroup($values->team1_id, $values->team2_id)) === null) {
        $form->addError('Zvoľte dva tímy z tej istej skupiny.');
        return false;
      }

      $table = $this->tablesRepository->getByTableTypeId($values->table_type_id, $this->seasonGroup->id);

      if ($values->score1 > $values->score2) {
        $state1 = 'win';
        $state2 = 'lost';
      } else if ($values->score2 > $values->score1) {
        $state1 = 'lost';
        $state2 = 'win';
      } else {
        $state1 = $state2 = 'tram';
      }

      // Update table statistics
      $this->tableEntriesRepository->updateEntry($table->id, $values->team1_id, $state1);
      $this->tableEntriesRepository->updateEntry($table->id, $values->team2_id, $state2);

      $this->updateTablePoints($table->id, $values);
      $this->updateScore($table->id, $values);

      // Processing data
      $values->offsetSet('round_id', $this->roundRow->id);
      $values->offsetUnset('table_type_id');
      $this->fightsRepository->insert($values);

      $this->flashMessage(self::ITEM_ADDED_SUCCESSFULLY, self::SUCCESS);
      $this->redirect('view', $this->roundRow->id);
    });
  }

  /**
   * @return Form
   */
  protected function createComponentRemoveForm(): Form
  {
    return $this->modalRemoveFormFactory->create(function () {
      $fights = $this->fightsRepository->getForRound($this->roundRow->id);

      foreach ($fights as $fight) {
        $this->fightsRepository->remove($fight->id);
      }

      $this->roundsRepository->remove($this->roundRow->id);
      $this->flashMessage(self::ITEM_REMOVED_SUCCESSFULLY, self::SUCCESS);
      $this->redirect('all');
    });
  }

  /**
   * Updates points based on fight result
   * @param int $tableId
   * @param ArrayHash $values
   */
  protected function updateTablePoints(int $tableId, ArrayHash $values): void
  {
    if ($values->score1 > $values->score2) {
      $this->tableEntriesRepository->updatePoints($tableId, $values->team1_id, 2);
    } elseif ($values->score2 > $values->score1) {
      $this->tableEntriesRepository->updatePoints($tableId, $values->team2_id, 2);
    } else {
      $this->tableEntriesRepository->updatePoints($tableId, $values->team2_id, 1);
      $this->tableEntriesRepository->updatePoints($tableId, $values->team1_id, 1);
    }
  }

  /**
   * Updates score for both teams
   * @param int $tableId
   * @param ArrayHash $values
   */
  protected function updateScore(int $tableId, ArrayHash $values): void
  {
    $this->tableEntriesRepository->updateEntry($tableId, $values->team1_id, 'score1', (int) $values->score1);
    $this->tableEntriesRepository->updateEntry($tableId, $values->team1_id, 'score2', (int) $values->score2);
    $this->tableEntriesRepository->updateEntry($tableId, $values->team2_id, 'score1', (int) $values->score2);
    $this->tableEntriesRepository->updateEntry($tableId, $values->team2_id, 'score2', (int) $values->score1);
  }

  /**
   * @param $team1
   * @param $team2
   * @return ActiveRow|null
   */
  public function findGroup($team1, $team2)
  {
    $seasonGroupTeam1 = $this->seasonsGroupsTeamsRepository->getSeasonGroupForTeam($team1);
    $seasonGroupTeam2 = $this->seasonsGroupsTeamsRepository->getSeasonGroupForTeam($team2);

    if ($seasonGroupTeam1->season_group_id !== $seasonGroupTeam2->season_group_id) {
      return null;
    }

    $this->seasonGroup = $this->seasonsGroupsRepository->findById($seasonGroupTeam1->season_group_id);
    return $this->groupsRepository->findById($this->seasonGroup->group_id);
  }

}
