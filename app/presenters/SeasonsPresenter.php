<?php

namespace App\Presenters;

use App\Forms\ArchiveFormFactory;
use App\Forms\SeasonFormFactory;
use App\Model\GroupsRepository;
use App\Model\LinksRepository;
use App\Model\SeasonArchiveService;
use App\Model\SeasonsGroupsRepository;
use App\Model\SponsorsRepository;
use App\Model\TeamsRepository;
use App\Model\SeasonsRepository;
use App\Model\SeasonsGroupsTeamsRepository;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\ArrayHash;
use Throwable;
use Tracy\Debugger;
use Tracy\ILogger;

/**
 * Class SeasonsPresenter
 * @package App\Presenters
 */
class SeasonsPresenter extends BasePresenter
{
  /** @var ActiveRow */
  private $seasonRow;

  /** @var SeasonsRepository */
  private $seasonsRepository;

  /** @var SeasonArchiveService */
  private $seasonArchiveService;

  /** @var ArchiveFormFactory */
  private $archiveFormFactory;

  /** @var SeasonFormFactory */
  private $seasonFormFactory;

  /** @var array<int, string> */
  private $archiveTeamOptions = [];

  /** @var array<int> */
  private $teamsWithEmptyPlaces = [];

  public function __construct(
      LinksRepository $linksRepository,
      SponsorsRepository $sponsorsRepository,
      TeamsRepository $teamsRepository,
      SeasonsRepository $seasonsRepository,
      SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository,
      SeasonArchiveService $seasonArchiveService,
      ArchiveFormFactory $archiveFormFactory,
      SeasonFormFactory $seasonFormFactory,
      SeasonsGroupsRepository $seasonsGroupsRepository,
      GroupsRepository $groupsRepository
  )
  {
    parent::__construct($groupsRepository, $linksRepository, $sponsorsRepository, $teamsRepository,
        $seasonsGroupsRepository, $seasonsGroupsTeamsRepository);
    $this->seasonsRepository = $seasonsRepository;
    $this->seasonArchiveService = $seasonArchiveService;
    $this->archiveFormFactory = $archiveFormFactory;
    $this->seasonFormFactory = $seasonFormFactory;
  }

  /**
   * Prepares the archive form. All current teams are selected by default.
   */
  public function actionAll(): void
  {
    if (!$this->user->isLoggedIn()) {
      return;
    }

    foreach ($this->seasonArchiveService->getCurrentTeams() as $team) {
      $id = (int) $team->season_group_team_id;
      $emptyPlaces = (int) $team->empty_places;
      $label = $team->group_label . ' — ' . $team->team_name;

      if ($emptyPlaces > 0) {
        $label .= ' (aktuálne voľné miesta: ' . $emptyPlaces . ')';
        $this->teamsWithEmptyPlaces[] = $id;
      }

      $this->archiveTeamOptions[$id] = $label;
    }

    $form = $this['archiveForm'];
    if (!$form->isSubmitted()) {
      $form->setDefaults([
        'teams' => array_keys($this->archiveTeamOptions),
      ]);
    }
  }

  /**
   * Prepare data for archive list.
   */
  public function renderAll(): void
  {
    $this->template->seasons = $this->seasonsRepository->getAll()->order('id DESC');
    $this->template->archiveTeamCount = count($this->archiveTeamOptions);
    $this->template->archiveEmptyTeamIds = implode(',', $this->teamsWithEmptyPlaces);
  }

  /**
   * @param int $id
   */
  public function actionView(int $id): void
  {
    $this->seasonRow = $this->seasonsRepository->findById($id);

    if (!$this->seasonRow || !$this->seasonRow->is_present) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }

    if ($this->user->isLoggedIn()) {
      $this['seasonForm']->setDefaults($this->seasonRow);
    }
  }

  /**
   * @param int $id
   */
  public function renderView(int $id): void
  {
    $this->template->season = $this->seasonRow;
  }

  /**
   * Add/edit archive label form.
   */
  protected function createComponentSeasonForm(): Form
  {
    return $this->seasonFormFactory->create(function (Form $form, ArrayHash $values): void {
      $this->userIsLogged();
      $id = $this->getParameter('id');

      if ($id) {
        $this->seasonRow->update($values);
        $this->flashMessage(self::CHANGES_SAVED_SUCCESSFULLY, self::SUCCESS);
      } else {
        $this->seasonsRepository->insert($values);
        $this->flashMessage(self::ITEM_ADDED_SUCCESSFULLY, self::SUCCESS);
      }

      $this->redirect('all');
    });
  }

  /**
   * Archive current season form.
   */
  protected function createComponentArchiveForm(): Form
  {
    return $this->archiveFormFactory->create(
      $this->archiveTeamOptions,
      function (Form $form, ArrayHash $values): void {
        $this->submittedArchiveForm($values);
      },
      function (): void {
        $this->redirect('all');
      }
    );
  }

  /**
   * Archives the complete current season and creates a clean current season.
   */
  private function submittedArchiveForm(ArrayHash $values): void
  {
    $this->userIsLogged();

    try {
      $archiveSeasonId = $this->seasonArchiveService->archiveCurrentSeason(
        (string) $values->label,
        (array) $values->teams
      );

      $this->flashMessage(
        'Sezóna bola archivovaná a nová sezóna bola vytvorená s vybranými tímami.',
        self::SUCCESS
      );
      $this->redirect('view', $archiveSeasonId);
    } catch (Throwable $e) {
      Debugger::log($e, ILogger::ERROR);
      $this->flashMessage(
        'Archiváciu sa nepodarilo dokončiť. Databázové zmeny boli vrátené späť.',
        self::DANGER
      );
      $this->redirect('all');
    }
  }
}
