<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\Forms\AttendanceFormFactory;
use App\Model\AttendancesRepository;
use App\Model\FightsRepository;
use App\Model\GroupsRepository;
use App\Model\LinksRepository;
use App\Model\SeasonsGroupsRepository;
use App\Model\SeasonsGroupsTeamsRepository;
use App\Model\SponsorsRepository;
use App\Model\TeamsRepository;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\ArrayHash;

class AttendancesPresenter extends BasePresenter
{
  /** @var ActiveRow */
  private $fightRow;

  /** @var ActiveRow */
  private $team1Row;

  /** @var ActiveRow */
  private $team2Row;

  /** @var int */
  private $seasonGroupId;

  /** @var array */
  private $team1Players = [];

  /** @var array */
  private $team2Players = [];

  /** @var AttendancesRepository */
  private $attendancesRepository;

  /** @var FightsRepository */
  private $fightsRepository;

  /** @var AttendanceFormFactory */
  private $attendanceFormFactory;

  public function __construct(
      LinksRepository $linksRepository,
      SponsorsRepository $sponsorsRepository,
      TeamsRepository $teamsRepository,
      FightsRepository $fightsRepository,
      AttendancesRepository $attendancesRepository,
      AttendanceFormFactory $attendanceFormFactory,
      GroupsRepository $groupsRepository,
      SeasonsGroupsRepository $seasonsGroupsRepository,
      SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository)
  {
    parent::__construct($groupsRepository, $linksRepository, $sponsorsRepository, $teamsRepository,
      $seasonsGroupsRepository, $seasonsGroupsTeamsRepository);

    $this->fightsRepository = $fightsRepository;
    $this->attendancesRepository = $attendancesRepository;
    $this->attendanceFormFactory = $attendanceFormFactory;
  }

  public function actionEdit(int $id): void
  {
    $this->userIsLogged();
    $this->fightRow = $this->fightsRepository->findById($id);

    if (!$this->fightRow || !$this->fightRow->is_present) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }

    $table = $this->fightRow->ref('tables', 'table_id');
    $this->team1Row = $this->teamsRepository->findById( (int) $this->fightRow->team1_id );
    $this->team2Row = $this->teamsRepository->findById( (int) $this->fightRow->team2_id );

    if (!$table || !$this->team1Row || !$this->team2Row) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }

    $this->seasonGroupId = (int) $table->season_group_id;
    $this->team1Players = $this->attendancesRepository->fetchPlayersForFightTeam( (int) $this->fightRow->team1_id, $this->seasonGroupId, $id );
    $this->team2Players = $this->attendancesRepository->fetchPlayersForFightTeam( (int) $this->fightRow->team2_id, $this->seasonGroupId, $id );

    $selected = $this->attendancesRepository->fetchSelectedForFight($id);
    $this['attendanceForm']->setDefaults([
      'team1_players' => array_values(array_intersect(array_keys($this->team1Players), $selected)),
      'team2_players' => array_values(array_intersect(array_keys($this->team2Players), $selected)),
    ]);
  }

  public function renderEdit(int $id): void
  {
    $this->template->fight = $this->fightRow;
    $this->template->team1 = $this->team1Row;
    $this->template->team2 = $this->team2Row;
  }

  protected function createComponentAttendanceForm(): Form
  {
    return $this->attendanceFormFactory->create(
      $this->team1Players,
      $this->team2Players,
      function (Form $form, ArrayHash $values): void {
        $allowedIds = array_map('intval', array_merge(array_keys($this->team1Players), array_keys($this->team2Players)));
        $selectedIds = array_map('intval', array_merge(
          (array) $values->team1_players,
          (array) $values->team2_players
        ));

        // Ignore manipulated IDs that do not belong to either roster in this fight.
        $selectedIds = array_values(array_unique(array_intersect($selectedIds, $allowedIds)));

        $this->attendancesRepository->saveForFight((int) $this->fightRow->id, $selectedIds);

        $this->flashMessage('Dochádzka bola uložená.', self::SUCCESS);
        $this->redirect('Rounds:view', (int) $this->fightRow->round_id);
      },
      function (): void {
        $this->redirect('Rounds:view', (int) $this->fightRow->round_id);
      }
    );
  }
}
