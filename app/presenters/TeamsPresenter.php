<?php

namespace App\Presenters;

use App\Forms\ModalRemoveFormFactory;
use App\Forms\PlayerAddFormFactory;
use App\Forms\TeamFormFactory;
use App\Forms\UploadFormFactory;
use App\Model\GroupsRepository;
use App\Model\LinksRepository;
use App\Model\PlayersRepository;
use App\Model\PlayersSeasonsGroupsTeamsRepository;
use App\Model\PlayerTypesRepository;
use App\Model\SeasonsGroupsRepository;
use App\Model\SponsorsRepository;
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

  private $seasonGroup;

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

  /**
   * @var ModalRemoveFormFactory
   */
  private $removeFormFactory;

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
      UploadFormFactory $uploadFormFactory,
      SeasonsGroupsRepository $seasonsGroupsRepository,
      ModalRemoveFormFactory $removeFormFactory
  )
  {
    parent::__construct($groupsRepository, $linksRepository, $sponsorsRepository, $teamsRepository,
        $seasonsGroupsRepository, $seasonsGroupsTeamsRepository);
    $this->groupsRepository = $groupsRepository;
    $this->playersRepository = $playersRepository;
    $this->playerTypesRepository = $playerTypesRepository;
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
   * @return Form
   */
  protected function createComponentRemoveForm(): Form
  {
    return $this->removeFormFactory->create( function () {
      $seasonTeam = $this->seasonsTeamsRepository->getTeam($this->teamRow->id);
      $this->seasonsTeamsRepository->remove($seasonTeam->id);
      $this->flashMessage(self::ITEM_REMOVED_SUCCESSFULLY, self::SUCCESS);
      $this->redirect('all');
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

  /**
   * Saves values to database and created new table entry for team in current season
   * @param Form $form
   * @param ArrayHash $values
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

    $seasonGroup = $this->seasonsGroupsRepository->getSeasonGroup($values->group_id);

    if ($seasonGroup && $this->teamRow) {
      $this->seasonsGroupsTeamsRepository->insert(
        array(
          'season_group_id' => $seasonGroup->id,
          'team_id' => $this->teamRow->id
        )
      );
      $this->flashMessage(self::ITEM_ADDED_SUCCESSFULLY, self::SUCCESS);
    } else {
      $this->flashMessage(self::ITEM_NOT_ADDED, self::DANGER);
    }

    // TODO: Insert also team entry to tables
    // $this->tablesRepository->insert(array('team_id' => $team));
    $this->redirect('all');
  }

}
