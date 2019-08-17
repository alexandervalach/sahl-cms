<?php

namespace App\Presenters;

use App\FormHelper;
use App\Forms\PlayerAddFormFactory;
use App\Forms\TeamFormFactory;
use App\Forms\UploadFormFactory;
use App\Model\GroupsRepository;
use App\Model\LinksRepository;
use App\Model\PlayersRepository;
use App\Model\PlayersSeasonsGroupsTeamsRepository;
use App\Model\PlayerTypesRepository;
use App\Model\SponsorsRepository;
use App\Model\TeamsRepository;
use App\Model\SeasonsGroupsTeamsRepository;
use Nette\Application\UI\Form;
use Nette\Application\BadRequetsException;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\FileSystem;
use Nette\IOException;
use Nette\Utils\ArrayHash;

class TeamsPresenter extends BasePresenter
{
  const TEAM_NOT_FOUND = 'Team not found';
  const ADD_PLAYER_FORM = 'addPlayerForm';
  const SUBMITTED_ADD_PLAYER_FORM = 'submittedAddPlayerForm';

  /** @var ActiveRow */
  private $teamRow;

  /** @var ActiveRow */
  private $seasonRow;

  /** @var GroupsRepository */
  private $groupsRepository;

  /** @var PlayersRepository */
  private $playersRepository;

  /** @var PlayersSeasonsGroupsTeamsRepository */
  private $playersSeasonsTeamsRepository;

  /** @var PlayerTypesRepository */
  private $playerTypesRepository;

  /** @var TeamFormFactory */
  private $teamFormFactory;

  /** @var PlayerAddFormFactory */
  private $playerAddFormFactory;

  /** @var UploadFormFactory */
  private $uploadFormFactory;

  public function __construct(
      LinksRepository $linksRepository,
      SponsorsRepository $sponsorsRepository,
      TeamsRepository $teamsRepository,
      SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository,
      GroupsRepository $groupsRepository,
      PlayersRepository $playersRepository,
      PlayerTypesRepository $playerTypesRepository,
      TeamFormFactory $teamFormFactory,
      PlayerAddFormFactory $playerAddFormFactory,
      PlayersSeasonsGroupsTeamsRepository $playersSeasonsTeamsRepository,
      UploadFormFactory $uploadFormFactory
  )
  {
    parent::__construct($linksRepository, $sponsorsRepository, $teamsRepository, $seasonsGroupsTeamsRepository);
    $this->groupsRepository = $groupsRepository;
    $this->playersRepository = $playersRepository;
    $this->playerTypesRepository = $playerTypesRepository;
    $this->teamFormFactory = $teamFormFactory;
    $this->playerAddFormFactory = $playerAddFormFactory;
    $this->playersSeasonsTeamsRepository = $playersSeasonsTeamsRepository;
    $this->uploadFormFactory = $uploadFormFactory;
  }

  /**
   * @param int $id
   */
  public function actionView(int $id): void
  {
    $this->teamRow = $this->teamsRepository->findById($id);

    if (!$this->teamRow || !$this->teamRow->is_present) {
      throw new BadRequetsException(self::ITEM_NOT_FOUND);
    }

    if ($this->user->isLoggedIn()) {
      $this['teamForm']->setDefaults($this->teamRow);
    }
  }

  /**
   * @param int $id
   */
  public function renderView(int $id): void
  {
    $this->template->players = $this->playersRepository->getForTeam($id);
    $this->template->goalies = []; // $this->playersRepository->getArchived()->where('team_id', $id);
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
        $this->flashMessage('Obrázok bol pridaný', self::SUCCESS);
      } else {
        $this->flashMessage('Nastala chyba. Skúste znova', self::DANGER);
      }
      $this->redirect('view', $this->teamRow->id);
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
   * Add new player and
   * @param Nette\Application\UI\Form;
   * @param Nette\Utils\ArrayHash;
   */
  public function submittedAddPlayerForm(Form $form, ArrayHash $values): void
  {
    $player = $this->playersRepository->getPlayer($values->name, $values->number);

    if (!$player) {
      $player = $this->playersRepository->insert(
        array( 'name' => $values->name, 'number' => $values->number )
      );
      $this->flashMessage(self::ITEM_ADDED_SUCCESSFULLY, self::SUCCESS);
    } else {
      $this->flashMessage(self::ITEM_ALREADY_EXISTS, self::WARNING);
    }

    $seasonTeam = $this->seasonsTeamsRepository->getSeasonTeam($this->teamRow->id);

    $this->playersSeasonsTeamsRepository->insert(
      array(
        'seasons_teams_id' => $seasonTeam->id,
        'player_id' => $player->id,
        'is_transfer' => $values->is_transfer,
        'player_type_id' => $values->player_type_id
      )
    );

    $this->redirect('view', $this->teamRow->id);
  }

  public function submittedRemoveForm(): void
  {
    $seasonTeam = $this->seasonsTeamsRepository->getTeam($this->teamRow->id);
    $this->seasonsTeamsRepository->remove($seasonTeam->id);
    $this->flashMessage('Tím bol odstránený', self::SUCCESS);
    $this->redirect('all');
  }

  /**
   * Saves values to database and created new table entry for team in current season
   * @param Form $form
   * @param AraryHash $values
   */
  public function submittedTeamForm(Form $form, ArrayHash $values): void
  {
    $id = $this->getParameter('id');

    if ($id) {
      $this->teamRow = $this->teamsRepository->findById($id);
      $this->teamRow->update( array('name' => $values->name) );

      $this->flashMessage(self::CHANGES_SAVED_SUCCESSFULLY, self::SUCCESS);
      $this->redirect('view', $this->teamRow->id);
    }

    $this->teamRow = $this->teamsRepository->findByName($values->name);

    if (!$this->teamRow) {
      $this->teamRow = $this->teamsRepository->insert( array('name' => $values->name) );
    }

    $this->seasonsTeamsRepository->insert(
      array(
        'team_id' => $this->teamRow->id,
        'group_id' => $values->group_id
      )
    );

    // $this->tablesRepository->insert(array('team_id' => $team));
    $this->flashMessage(self::CHANGES_SAVED_SUCCESSFULLY, self::SUCCESS);
    $this->redirect('all');
  }

}
