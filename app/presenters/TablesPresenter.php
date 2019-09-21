<?php

namespace App\Presenters;

use App\Model\GroupsRepository;
use App\Model\LinksRepository;
use App\Model\SeasonsGroupsRepository;
use App\Model\SponsorsRepository;
use App\Model\TeamsRepository;
use App\Model\TablesRepository;
use App\Model\SeasonsGroupsTeamsRepository;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Utils\ArrayHash;

/**
 * Class TablesPresenter
 * @package App\Presenters
 */
class TablesPresenter extends BasePresenter
{
  /** @var array */
  private $tables;

  /** @var ActiveRow */
  private $tableRow;

  /** @var ActiveRow */
  private $archRow;

  /** @var TablesRepository */
  private $tablesRepository;

  /**
   * @var IRow|null
   */
  private $seasonGroup;

  /**
   * @var ActiveRow
   */
  private $groupRow;

  /**
   * TablesPresenter constructor.
   * @param LinksRepository $linksRepository
   * @param SponsorsRepository $sponsorsRepository
   * @param TeamsRepository $teamsRepository
   * @param TablesRepository $tablesRepository
   * @param SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository
   * @param GroupsRepository $groupsRepository
   * @param SeasonsGroupsRepository $seasonsGroupsRepository
   */
  public function __construct(
    LinksRepository $linksRepository,
    SponsorsRepository $sponsorsRepository,
    TeamsRepository $teamsRepository,
    TablesRepository $tablesRepository,
    SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository,
    GroupsRepository $groupsRepository,
    SeasonsGroupsRepository $seasonsGroupsRepository
  )
  {
    parent::__construct($groupsRepository, $linksRepository, $sponsorsRepository, $teamsRepository,
        $seasonsGroupsRepository, $seasonsGroupsTeamsRepository);
    $this->tablesRepository = $tablesRepository;
    $this->tables = [];
  }

  /**
   * @param int $groupId
   */
  public function actionAll(int $groupId): void
  {
    $this->groupRow = $this->groupsRepository->findById($groupId);
    $this->seasonGroup = $this->seasonsGroupsRepository->getSeasonGroup($groupId);
    $tables = $this->tablesRepository->findByValue('season_group_id', $this->seasonGroup->id);

    foreach ($tables as $table) {
      $this->tables[$table->id]['data'] = $table;
      $this->tables[$table->id]['entries'] = $table->related('table_entries')->order('points DESC, (score1 - score2) DESC');
      $this->tables[$table->id]['type'] = $table->ref('table_types', 'table_type_id');
    }
  }

  /**
   * @param int $groupId
   */
  public function renderAll(int $groupId): void
  {
    $this->template->tables = ArrayHash::from($this->tables);
    $this->template->group = $this->groupRow;
  }

  /**
   * @param $id
   */
  public function actionAddToSidebar($id): void
  {
    $this->userIsLogged();
    $this->tableRow = $this->tablesRepository->findById($id);

    if (!$this->tableRow) {
      throw new BadRequestException(self::TABLE_ROW_NOT_FOUND);
    }

    $this->submittedSetVisible();
  }

  /**
   * @param int $id
   */
  public function actionArchAll(int $id): void
  {
    $this->archRow = $this->seasonsRepository->findById($id);
  }

  /**
   * @param int $id
   */
  public function renderArchAll(int $id): void
  {
    $tableTypes = $this->tableTypesRepository->findAll();
    $tableRows = array();

    foreach ($tableTypes as $type) {
      $tableRows[$type->name] = $this->tablesRepository
              ->findByValue('season_id', $this->archRow)
              ->where('table_type = ?', $type)
              ->order('points DESC, (score1 - score2) DESC');
    }

    $this->template->tables = $tableRows;
    $this->template->tableTypes = $tableTypes;
    $this->template->archive = $this->archRow;
  }

  /**
   *
   */
  public function submittedRemoveForm(): void
  {
    $this->tableRow->delete();
    $this->flashMessage('Záznam bol odstránený', self::SUCCESS);
    $this->redirect('all');
  }

  /**
   *
   */
  public function submittedSetVisible(): void
  {
    $this->tableRow->update(array('is_visible' => 1));
    $this->flashMessage('Tabuľka bola pridaná na domovskú stránku', self::SUCCESS);
    $this->redirect('all');
  }

}
