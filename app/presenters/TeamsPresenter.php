<?php

namespace App\Presenters;

use App\Forms\ModalRemoveFormFactory;
use App\Forms\PlayerFormFactory;
use App\Forms\TeamFormFactory;
use App\Forms\UploadFormFactory;
use App\Model\GroupsRepository;
use App\Model\LinksRepository;
use App\Model\PlayersRepository;
use App\Model\PlayersSeasonsGroupsTeamsRepository;
use App\Model\PlayerTypesRepository;
use App\Model\SeasonsGroupsRepository;
use App\Model\SponsorsRepository;
use App\Model\TableEntriesRepository;
use App\Model\TablesRepository;
use App\Model\TableTypesRepository;
use App\Model\TeamsRepository;
use App\Model\SeasonsGroupsTeamsRepository;
use Nette\Application\UI\Form;
use Nette\Application\BadRequetsException;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\FileSystem;
use Nette\IOException;
use Nette\Utils\ArrayHash;

/**
 * Class TeamsPresenter
 * @package App\Presenters
 */
class TeamsPresenter extends BasePresenter
{
  /** @var ActiveRow */
  private $teamRow;

  /** @var ActiveRow */
  private $seasonRow;

  /** @var ActiveRow */
  private $groupRow;

  /**
   * @var
   */
  private $seasonGroup;

  /** @var PlayersRepository */
  private $playersRepository;

  /** @var PlayersSeasonsGroupsTeamsRepository */
  private $playersSeasonsTeamsRepository;

  /** @var PlayerTypesRepository */
  private $playerTypesRepository;

  /** @var TeamFormFactory */
  private $teamFormFactory;

  /** @var PlayerFormFactory */
  private $playerAddFormFactory;

  /** @var UploadFormFactory */
  private $uploadFormFactory;

  /**
   * @var ModalRemoveFormFactory
   */
  private $removeFormFactory;

  /**
   * @var
   */
  private $teams;

  /**
   * @var TablesRepository
   */
  private $tablesRepository;

  /**
   * @var TableTypesRepository
   */
  private $tableTypesRepository;

  /**
   * @var TableEntriesRepository
   */
  private $tableEntriesRepository;

  /**
   * TeamsPresenter constructor.
   * @param LinksRepository $linksRepository
   * @param SponsorsRepository $sponsorsRepository
   * @param TeamsRepository $teamsRepository
   * @param SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository
   * @param GroupsRepository $groupsRepository
   * @param PlayersRepository $playersRepository
   * @param PlayerTypesRepository $playerTypesRepository
   * @param TeamFormFactory $teamFormFactory
   * @param PlayerFormFactory $playerAddFormFactory
   * @param PlayersSeasonsGroupsTeamsRepository $playersSeasonsTeamsRepository
   * @param UploadFormFactory $uploadFormFactory
   * @param SeasonsGroupsRepository $seasonsGroupsRepository
   * @param ModalRemoveFormFactory $removeFormFactory
   * @param TablesRepository $tablesRepository
   * @param TableTypesRepository $tableTypesRepository
   * @param TableEntriesRepository $tableEntriesRepository
   */
  public function __construct(
      LinksRepository $linksRepository,
      SponsorsRepository $sponsorsRepository,
      TeamsRepository $teamsRepository,
      SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository,
      GroupsRepository $groupsRepository,
      PlayersRepository $playersRepository,
      PlayerTypesRepository $playerTypesRepository,
      TeamFormFactory $teamFormFactory,
      PlayerFormFactory $playerAddFormFactory,
      PlayersSeasonsGroupsTeamsRepository $playersSeasonsTeamsRepository,
      UploadFormFactory $uploadFormFactory,
      SeasonsGroupsRepository $seasonsGroupsRepository,
      ModalRemoveFormFactory $removeFormFactory,
      TablesRepository $tablesRepository,
      TableTypesRepository $tableTypesRepository,
      TableEntriesRepository $tableEntriesRepository
  )
  {
    parent::__construct($groupsRepository, $linksRepository, $sponsorsRepository, $teamsRepository,
        $seasonsGroupsRepository, $seasonsGroupsTeamsRepository);
    $this->groupsRepository = $groupsRepository;
    $this->playersRepository = $playersRepository;
    $this->playerTypesRepository = $playerTypesRepository;
    $this->tablesRepository = $tablesRepository;
    $this->tableTypesRepository = $tableTypesRepository;
    $this->tableEntriesRepository = $tableEntriesRepository;
    $this->teamFormFactory = $teamFormFactory;
    $this->playerAddFormFactory = $playerAddFormFactory;
    $this->playersSeasonsTeamsRepository = $playersSeasonsTeamsRepository;
    $this->uploadFormFactory = $uploadFormFactory;
    $this->removeFormFactory = $removeFormFactory;
  }

  /**
   * @param int $groupId
   */
  public function actionAll(int $groupId): void
  {
    $this->userIsLogged();
    $this->groupRow = $this->groupsRepository->findById($groupId);

    if (!$this->groupRow) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }
  }

  /**
   * @param int $groupId
   */
  public function renderAll(int $groupId): void
  {
    $this->template->group = ArrayHash::from($this->groups[$this->groupRow->id]);
  }

  /**
   * @param int $id
   * @param int $groupId
   */
  public function actionView(int $id, int $groupId): void
  {
    $this->teamRow = $this->teamsRepository->findById($id);
    $this->groupRow = $this->groupsRepository->findById($groupId);

    if (!$this->teamRow || !$this->groupRow) {
      throw new BadRequetsException(self::ITEM_NOT_FOUND);
    }

    $this->seasonGroup = $this->seasonsGroupsRepository->getGroup($groupId);

    if ($this->user->isLoggedIn()) {
      $this['teamForm']->setDefaults($this->teamRow);
    }
  }

  /**
   * @param int $id
   * @param int $groupId
   */
  public function renderView(int $id, int $groupId): void
  {
    $this->template->players = $this->playersRepository->getForTeam($id, $this->seasonGroup->id);
    $this->template->team = $this->teamRow;
    $this->template->i = 0;
    $this->template->j = 0;
  }

  /**
   * @param int $id
   */
  public function actionArchAll(int $id): void
  {
    $this->seasonRow = $this->seasonsGroupsTeamsRepository->findById($id);
    if (!$this->seasonRow || !$this->seasonRow->is_present) {
      throw new BadRequetsException(self::ITEM_NOT_FOUND);
    }

    $teams = $this->seasonsGroupsTeamsRepository->getForSeason($id);
    $data = [];
    foreach ($teams as $team) {
      $data[$team->id] = $team->ref('teams', 'team_id');
    }
    $this->teams = ArrayHash::from($data);
  }

  /**
   * @param int $id
   */
  public function renderArchAll(int $id): void
  {
    $this->template->teams = $this->teams;
    $this->template->archive = $this->seasonRow;
  }

  /**
   * @param int $id
   */
  public function actionArchView(int $id): void
  {
    $this->seasonRow = $this->seasonsRepository->findById($id);
    if (!$this->seasonRow || !$this->seasonRow->is_present) {
      throw new BadRequetsException(self::ITEM_NOT_FOUND);
    }
  }

  /**
   * @param int $id
   */
  public function renderArchView(int $id): void
  {
    // $this->template->teams = $this->teamsRepository->getAll($id);
    $this->template->season = $this->seasonRow;
  }

  /**
   * @return Form
   */
  protected function createComponentUploadForm(): Form
  {
    return $this->uploadFormFactory->create(function (Form $form, ArrayHash $values) {
      $img = $values->image;

      if ($img->isOk() && $img->isImage()) {
        $imgName = $img->getSanitizedName();
        $img->move($this->imageDir . '/' . $imgName);
        $this->teamRow->update( array('logo' => $imgName) );
        $this->flashMessage(self::ITEM_ADDED_SUCCESSFULLY, self::SUCCESS);
      } else {
        $this->flashMessage('Nastala chyba. Skúste znova', self::DANGER);
      }
      $this->redirect('view', $this->teamRow->id, $this->groupRow->id);
    });
  }

  /**
   * @return Form
   */
  protected function createComponentTeamForm(): Form
  {
    return $this->teamFormFactory->create(function (Form $form, ArrayHash $values) {
      $this->submittedTeamForm($form, $values);
    });
  }

  /**
   * @return Form
   */
  protected function createComponentAddPlayerForm(): Form
  {
    return $this->playerAddFormFactory->create(function (Form $form, ArrayHash $values) {
      $this->submittedAddPlayerForm($form, $values);
    });
  }

  /**
   * @return Form
   */
  protected function createComponentRemoveForm(): Form
  {
    return $this->removeFormFactory->create( function () {
      $seasonGroupTeam = $this->seasonsGroupsTeamsRepository->getByTeam($this->teamRow->id, $this->seasonGroup->id);

      if ($seasonGroupTeam) {
        $this->seasonsGroupsTeamsRepository->remove($seasonGroupTeam->id);
        $this->flashMessage(self::ITEM_REMOVED_SUCCESSFULLY, self::SUCCESS);
      } else {
        $this->flashMessage(self::ITEM_NOT_REMOVED, self::DANGER);
      }

      $this->redirect('all', $this->groupRow->id);
    });
  }

  /**
   * Add new player and
   * @param Form $form
   * @param ArrayHash $values
   */
  public function submittedAddPlayerForm(Form $form, ArrayHash $values): void
  {
    $player = $this->playersRepository->getPlayer($values->name, $values->number);

    if (!$player) {
      $player = $this->playersRepository->insert(
        array( 'name' => $values->name, 'number' => $values->number )
      );
      $this->flashMessage(self::ITEM_ADDED_SUCCESSFULLY, self::SUCCESS);
    }

    $seasonGroupTeam = $this->seasonsGroupsTeamsRepository->getTeam($this->teamRow->id, $this->seasonGroup->id);

    $this->playersSeasonsTeamsRepository->insert(
      array(
        'season_group_team_id' => $seasonGroupTeam->id,
        'player_id' => $player->id,
        'is_transfer' => $values->is_transfer,
        'player_type_id' => $values->player_type_id
      )
    );

    $this->redirect('view', $this->teamRow->id, $this->groupRow->id);
  }

  /**
   * Saves values to database and created new table entry for team in current season
   * @param Form $form
   * @param ArrayHash $values
   */
  public function submittedTeamForm(Form $form, ArrayHash $values): void
  {
    $id = $this->getParameter('id');

    // UPDATE existing team
    if ($id) {
      $this->updateTeam($id, $values);
    }

    // CREATE new entry
    $this->createTeam($values);
  }

  /**
   * Updates existing team
   * @param int $id
   * @param ArrayHash $values
   */
  private function updateTeam(int $id, ArrayHash $values): void
  {
    if ($this->teamRow) {
      $this->teamRow = $this->teamsRepository->findById($id);
      $this->teamRow->update($values);
      $this->flashMessage(self::ITEM_UPDATED, self::SUCCESS);
      $this->redirect('view', $this->teamRow->id, $this->groupRow->id);
    }
  }

  /**
   * Creates new entry
   * @param ArrayHash $values
   */
  private function createTeam(ArrayHash $values): void
  {
    // Check if team already exists
    $team = $this->getTeam($values);

    // Add team to season and group
    $seasonGroup = $this->seasonsGroupsRepository->getSeasonGroup($this->groupRow->id);

    if ($seasonGroup && $team) {
      // Add team entry to current season
      $this->setSeasonGroupTeam($team->id, $seasonGroup->id);

      // Add team entry to tables
      $tableType = $this->getTableType(self::BASE_TABLE_LABEL);
      $table = $this->getTable($tableType->id, $seasonGroup->id);
      $this->setTableEntry($team->id, $table->id);

      $this->flashMessage(self::ITEM_ADDED_SUCCESSFULLY, self::SUCCESS);
    } else {
      $this->flashMessage(self::ITEM_NOT_ADDED, self::DANGER);
    }

    $this->redirect('all', $this->groupRow->id);
  }

  /**
   * @param int $teamId
   * @param int $seasonGroupId
   */
  private function setSeasonGroupTeam(int $teamId, int $seasonGroupId): void
  {
    $seasonGroupTeam = $this->seasonsGroupsTeamsRepository->getByTeam($teamId, $seasonGroupId);
    if (!$seasonGroupTeam) {
      $this->seasonsGroupsTeamsRepository->insertData($teamId, $seasonGroupId);
    }
  }

  /**
   * @param int $teamId
   * @param int $tableId
   */
  private function setTableEntry(int $teamId, int $tableId): void
  {
    $tableEntry = $this->tableEntriesRepository->getByTableAndTeam($teamId, $tableId);
    if (!$tableEntry) {
      $this->tableEntriesRepository->insertData($teamId, $tableId);
    }
  }

  /**
   * @param string $label
   * @return bool|int|\Nette\Database\IRow|ActiveRow|null
   */
  private function getTableType(string $label)
  {
    $tableType = $this->tableTypesRepository->findByLabel($label);
    return (!$tableType) ? $this->tableTypesRepository->insertData($label) : $tableType;
  }

  /**
   * @param int $tableTypeId
   * @param int $seasonGroupId
   * @return bool|int|\Nette\Database\IRow|ActiveRow|null
   */
  private function getTable(int $tableTypeId, int $seasonGroupId)
  {
    $table = $this->tablesRepository->getByType($tableTypeId, $seasonGroupId);
    return (!$table) ? $this->tablesRepository->insertData($tableTypeId, $seasonGroupId) : $table;
  }

  /**
   * @param ArrayHash $values
   * @return bool|int|\Nette\Database\IRow|ActiveRow|null
   */
  private function getTeam (ArrayHash $values)
  {
    $team = $this->teamsRepository->findByName($values->name);
    return (!$team) ? $this->teamsRepository->insert($values) : $team;
  }
}
