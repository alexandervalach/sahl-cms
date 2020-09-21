<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\Forms\PlayerFormFactory;
use App\Helpers\FormHelper;
use App\Forms\ModalRemoveFormFactory;
use App\Model\GoalsRepository;
use App\Model\GroupsRepository;
use App\Model\LinksRepository;
use App\Model\PlayersSeasonsGroupsTeamsRepository;
use App\Model\SeasonsGroupsRepository;
use App\Model\SponsorsRepository;
use App\Model\TeamsRepository;
use App\Model\PlayersRepository;
use App\Model\PlayerTypesRepository;
use App\Model\SeasonsGroupsTeamsRepository;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\ArrayHash;

/**
 * Class PlayersPresenter
 * @package App\Presenters
 */
class PlayersPresenter extends BasePresenter
{
  /** @var ActiveRow */
  private $playerRow;

  /** @var ActiveRow */
  private $teamRow;

  /** @var ActiveRow */
  private $archRow;

  /** @var ArrayHash */
  private $playerData;

  /**
   * @var
   */
  private $players;

  /** @var PlayersRepository */
  private $playersRepository;

  /** @var PlayerTypesRepository */
  private $playerTypesRepository;

  /**
   * @var ActiveRow
   */
  private $groupRow;

  /**
   * @var IRow|null
   */
  private $seasonGroup;

  /**
   * @var ModalRemoveFormFactory
   */
  private $modalRemoveFormFactory;

  /**
   * @var PlayersSeasonsGroupsTeamsRepository
   */
  private $playersSeasonsGroupsTeamsRepository;

  /**
   * @var GoalsRepository
   */
  private $goalsRepository;

  /**
   * @var PlayerFormFactory
   */
  private $playerFormFactory;

  /**
   * PlayersPresenter constructor.
   * @param LinksRepository $linksRepository
   * @param SponsorsRepository $sponsorsRepository
   * @param TeamsRepository $teamsRepository
   * @param PlayersRepository $playersRepository
   * @param PlayerTypesRepository $playerTypesRepository
   * @param SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository
   * @param GroupsRepository $groupsRepository
   * @param SeasonsGroupsRepository $seasonsGroupsRepository
   * @param ModalRemoveFormFactory $modalRemoveFormFactory
   * @param PlayersSeasonsGroupsTeamsRepository $playersSeasonsGroupsTeamsRepository
   * @param GoalsRepository $goalsRepository
   */
  public function __construct(
      LinksRepository $linksRepository,
      SponsorsRepository $sponsorsRepository,
      TeamsRepository $teamsRepository,
      PlayersRepository $playersRepository,
      PlayerTypesRepository $playerTypesRepository,
      SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository,
      GroupsRepository $groupsRepository,
      SeasonsGroupsRepository $seasonsGroupsRepository,
      ModalRemoveFormFactory $modalRemoveFormFactory,
      PlayersSeasonsGroupsTeamsRepository $playersSeasonsGroupsTeamsRepository,
      GoalsRepository $goalsRepository,
      PlayerFormFactory $playerFormFactory
  ) {
    parent::__construct($groupsRepository, $linksRepository, $sponsorsRepository, $teamsRepository,
        $seasonsGroupsRepository, $seasonsGroupsTeamsRepository);
    $this->playersRepository = $playersRepository;
    $this->playerTypesRepository = $playerTypesRepository;
    $this->modalRemoveFormFactory = $modalRemoveFormFactory;
    $this->playersSeasonsGroupsTeamsRepository = $playersSeasonsGroupsTeamsRepository;
    $this->goalsRepository = $goalsRepository;
    $this->playerFormFactory = $playerFormFactory;
  }

  /**
   * @param int $groupId
   */
  public function actionAll(int $groupId): void
  {
    $this->groupRow = $this->groupsRepository->findById($groupId);
    $this->seasonGroup = $this->seasonsGroupsRepository->getSeasonGroup($groupId);

    if (!$this->groupRow || !$this->seasonGroup) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }

    $this->players = $this->playersRepository->getForSeasonGroup($this->seasonGroup->id);
  }

  /**
   * @param int $groupId
   */
  public function renderAll(int $groupId): void
  {
    $this->template->players = $this->players;
    $this->template->group = $this->groupRow;
    $this->template->i = 0;
    $this->template->j = 0;
    $this->template->current = 0;
    $this->template->previous = 0;
  }

  /**
   * @param int $id
   * @param int $teamId
   */
  public function actionView(int $id, int $teamId): void
  {
    $player = $this->playersRepository->getPlayerInfo($id);
    $this->playerRow = $this->playersRepository->findById($id);
    $this->teamRow = $this->teamsRepository->findById($teamId);

    if (!$this->playerRow || !$this->teamRow || !$player) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }

    $data['player']['name'] = $player->name;
    $data['player']['number'] = $player->number;
    $data['player']['goals'] = $player->goals;
    $data['player']['assistances'] = $player->assistances;
    $data['player']['is_transfer'] = $player->is_transfer;
    $data['player']['player_type_id'] = $player->player_type_id;
    $data['team']['id'] = $this->teamRow->id;
    $data['team']['name'] = $this->teamRow->name;
    $data['team']['logo'] = $this->teamRow->logo;
    $data['type']['label'] = $player->type_label;
    $data['type']['abbr'] = $player->type_abbr;

    $this->playerData = ArrayHash::from($data);

    if ($this->user->isLoggedIn()) {
      $this[self::EDIT_FORM]->setDefaults($this->playerData->player);
    }
  }

  /**
   * @param int $id
   */
  public function renderView(int $id): void
  {
    $this->template->player = $this->playerData->player;
    $this->template->team = $this->playerData->team;
    $this->template->type = $this->playerData->type;
  }

  /**
   * @param $id
   */
  public function actionArchAll($id): void
  {
    $this->archRow = $this->seasonsRepository->findById($id);
  }

  /**
   * @param $id
   */
  public function renderArchAll($id): void
  {
    $this->template->stats = $this->playersRepository->getArchived($id)
            ->where('name != ?', ' ')
            ->order('goals DESC, name DESC');
    $this->template->archive = $this->archRow;
    $this->template->i = 0;
    $this->template->j = 0;
    $this->template->current = 0;
    $this->template->previous = 0;
  }

  /**
   * @param int $id
   * @param $param
   */
  public function actionArchView(int $id, $param): void
  {
    $this->teamRow = $this->teamsRepository->findById($param);
    if (!$this->teamRow || !$this->teamRow->is_present) {
      throw new BadRequestException($this->error);
    }
  }

  /**
   * @param int $id
   * @param $param
   */
  public function renderArchView(int $id, $param): void
  {
    $this->template->players = $this->playersRepository->findByValue('team_id', $param)->where('archive_id', $id)->where('NOT type_id', 2);
    $this->template->goalies = $players = $this->playersRepository->findByValue('team_id', $param)->where('archive_id', $id)->where('type_id', 2);
    $this->template->team = $this->teamRow;
    $this->template->archive = $this->teamRow->ref('archive', 'archive_id');
  }

  /**
   * @return Form
   */
  protected function createComponentEditForm(): Form
  {
    return $this->playerFormFactory->create(function (Form $form, ArrayHash $values) {
      // Update player row
      $playerData['number'] = $values->number;
      $playerData['name'] = $values->name;
      $this->playerRow->update(ArrayHash::from($playerData));

      $playerInSeason = $this->playersSeasonsGroupsTeamsRepository->findByPlayer($this->playerRow->id);

      if ($playerInSeason) {
        $playerSeasonGroupTeam = $this->playersSeasonsGroupsTeamsRepository->findById($playerInSeason->id);
        if ($playerSeasonGroupTeam) {
          $playerSeasonData['player_type_id'] = $values->player_type_id;
          $playerSeasonData['is_transfer'] = $values->is_transfer;
          $playerSeasonGroupTeam->update(ArrayHash::from($playerSeasonData));
        }
      }

      $this->flashMessage(self::ITEM_UPDATED, self::SUCCESS);
      $this->redirect('view', $this->playerRow->id, $this->teamRow->id);
    });
  }

  /**
   * @return Form
   */
  protected function createComponentRemoveForm(): Form
  {
    return $this->modalRemoveFormFactory->create(function () {
      $playerSeasonTeamGroup = $this->playersSeasonsGroupsTeamsRepository->findByPlayer($this->playerRow->id);

      if (!$playerSeasonTeamGroup) {
        $this->flashMessage(self::ITEM_NOT_REMOVED, self::DANGER);
        $this->redirect('view', $this->playerRow->id);
      }

      $seasonTeamGroup = $this->seasonsGroupsTeamsRepository->findById($playerSeasonTeamGroup->season_group_team_id);

      if (!$seasonTeamGroup) {
        $this->flashMessage(self::ITEM_NOT_REMOVED, self::DANGER);
        $this->redirect('view', $this->playerRow->id);
      }

      $seasonGroup = $this->seasonsGroupsRepository->findById($seasonTeamGroup->season_group_id);

      if (!$seasonGroup) {
        $this->flashMessage(self::ITEM_NOT_REMOVED, self::DANGER);
        $this->redirect('view', $this->playerRow->id);
      }

      // Fetch goals for selected player in selected team, group and season
      $goals = $this->goalsRepository->findByValue('player_season_group_team_id', $playerSeasonTeamGroup->id);

      // Remove goals
      foreach ($goals as $goal) {
        $this->goalsRepository->softDelete($goal->id);
      }

      // Remove player from team
      $this->playersSeasonsGroupsTeamsRepository->remove($playerSeasonTeamGroup->id);
      $this->flashMessage(self::ITEM_REMOVED_SUCCESSFULLY, self::SUCCESS);
      $this->redirect('Teams:view', $seasonTeamGroup->team_id, $seasonGroup->group_id);
    });
  }

  /**
   *
   */
  public function submittedResetForm(): void
  {
    $players = $this->playersRepository
      ->findByValue('archive_id', null)
      ->where('goals != ?', 0);

    $values = array('goals' => 0);

    foreach ($players as $player) {
      $player->update($values);
    }

    $this->redirect('all');
  }

}
