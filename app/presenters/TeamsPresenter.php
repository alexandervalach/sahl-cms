<?php

namespace App\Presenters;

use App\FormHelper;
use App\Model\GroupsRepository;
use App\Model\LinksRepository;
use App\Model\PlayersRepository;
use App\Model\PlayerTypesRepository;
use App\Model\SponsorsRepository;
use App\Model\TeamsRepository;
use App\Model\SeasonsTeamsRepository;
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

  /** @var PlayerTypesRepository */
  private $playerTypesRepository;

  public function __construct(
    LinksRepository $linksRepository,
    SponsorsRepository $sponsorsRepository,
    TeamsRepository $teamsRepository,
    SeasonsTeamsRepository $seasonsTeamsRepository,
    GroupsRepository $groupsRepository,
    PlayersRepository $playersRepository,
    PlayerTypesRepository $playerTypesRepository
  )
  {
    parent::__construct($linksRepository, $sponsorsRepository, $teamsRepository, $seasonsTeamsRepository);
    $this->groupsRepository = $groupsRepository;
    $this->playersRepository = $playersRepository;
    $this->playerTypesRepository = $playerTypesRepository;
  }

  public function actionView(int $id): void
  {
    $this->teamRow = $this->teamsRepository->findById($id);

    if (!$this->teamRow || !$this->teamRow->is_present) {
      throw new BadRequetsException(self::TEAM_NOT_FOUND);
    }

    if ($this->user->isLoggedIn()) {
      $this['teamForm']->setDefaults($this->teamRow);
    }
  }

  public function renderView($id): void
  {
    $this->template->players = $this->playersRepository->getForTeam($id);
    $this->template->goalies = []; // $this->playersRepository->getArchived()->where('team_id', $id);
    $this->template->team = $this->teamRow;
    $this->template->i = 0;
    $this->template->j = 0;
  }

  public function actionArchAll(int $id): void
  {
    $this->seasonRow = $this->seasonsRepository->findById($id);
    if (!$this->seasonRow || !$this->seasonRow->is_present) {
      throw new BadRequetsException(self::ITEM_NOT_FOUND);
    }

    $teams = $this->seasonsTeamsRepository->getForSeason($id);
    $data = [];
    foreach ($teams as $team) {
      $data[$team->id] = $team->ref('teams', 'team_id');
    }
    $this->teams = ArrayHash::from($data);
  }

  public function renderArchAll(int $id): void
  {
    $this->template->teams = $this->teams;
    $this->template->archive = $this->seasonRow;
  }

  public function actionArchView(int $id): void
  {
    $this->seasonRow = $this->seasonsRepository->findById($id);
    if (!$this->seasonRow || !$this->seasonRow->is_present) {
      throw new BadRequetsException(self::ITEM_NOT_FOUND);
    }
  }

  public function renderArchView($id): void
  {
    // $this->template->teams = $this->teamsRepository->getAll($id);
    $this->template->season = $this->seasonRow;
  }

  protected function createComponentUploadForm(): Form
  {
    $form = new Form;
    $form->addUpload('image', 'Nahrajte obrázok');
    $form->addSubmit('upload', 'Nastaviť obrázok');
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', 'btn btn-large btn-warning')
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, self::SUBMITTED_UPLOAD_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  protected function createComponentTeamForm(): Form
  {
    $groups = $this->groupsRepository->getAsArray();
    $form = new Form;
    $form->addText('name', 'Názov tímu')
          ->setAttribute('placeholder', 'SKV Aligators')
          ->setRequired('Názov tímu je povinné pole.')
          ->addRule(Form::MAX_LENGTH, "Dĺžka názvu smie byť len 255 znakov.", 255);
    $form->addSelect('group_id', 'Divízia', $groups);
    $form->addSubmit('save', 'Uložiť');
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', 'btn btn-large btn-warning')
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, 'submittedTeamForm'];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  protected function createComponentAddPlayerForm(): Form
  {
    $types = $this->playerTypesRepository->getTypes();
    $form = new Form;
    $form->addText('name', 'Meno a priezvisko')
          ->setAttribute('placeholder', 'Zdeno Chára')
          ->addRule(Form::FILLED, 'Opa, ešte nie je vyplnené Meno a priezvisko');
    $form->addText('number', 'Číslo')
          ->setAttribute('placeholder', 14);
    $form->addSelect('player_type_id', 'Typ hráča', $types);
    $form->addCheckbox('is_transfer', ' Prestupový hráč');
    $form->addSubmit('save', 'Uložiť');
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', 'btn btn-large btn-warning')
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, self::SUBMITTED_ADD_PLAYER_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  public function submittedAddPlayerForm(Form $form, ArrayHash $values): void
  {
    $playerId = $this->playersRepository->findByValue('name', $values->name)
      ->where('number', $values->number)->fetch();

    if (!$playerId) {
      $playerId = $this->playersRepository->insert(
        array(
          'name' => $values->name,
          'number' => $values->number
        )
      );
    }

    $seasonTeam = $this->seasonsTeamsRepository
      ->findByValue('team_id', $this->teamRow->id)
      ->where('season_id', null)->select('id')->fetch();

    $this->playersSeasonsTeamsRepository->insert(
      array(
        'seasons_teams_id' => $seasonTeam->id,
        'player_id' => $playerId,
        'is_transfer' => $values->is_transfer,
        'player_type_id' => $values->player_type_id
      )
    );

    $this->flashMessage('Hráč bol pridaný', self::SUCCESS);
    $this->redirect('view', $this->teamRow->id);
  }

  public function submittedUploadForm(Form $form, ArrayHash $values): void
  {
    $img = $values->image;

    if ($img->isOk() AND $img->isImage()) {
      $imgName = $img->getSanitizedName();
      $img->move($this->imageDir . '/' . $imgName);
      $data = array('logo' => $imgName);
      $this->teamRow->update($data);
      $this->flashMessage('Obrázok bol pridaný', self::SUCCESS);
    } else {
      $this->flashMessage('Nastala chyba. Skúste znova', self::DANGER);
    }
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
   * @param array $values
   */
  public function submittedTeamForm(Form $form, array $values): void
  {
    $id = $this->getParameter('id');

    if ($id) {
      $this->teamRow = $this->teamsRepository->findById($id);
      $this->teamRow->update(
        array('name' => $values['name'])
      );

      $this->flashMessage(self::CHANGES_SAVED_SUCCESSFULLY, self::SUCCESS);
      $this->redirect('view', $this->teamRow->id);

    } else {
      $this->teamRow = $this->teamsRepository->findByValue('name', $values['name'])->fetch();

      if (!$this->teamRow) {
        $this->teamRow = $this->teamsRepository->insert(
          array('name' => $values['name'])
        );
      }

      $this->seasonsTeamsRepository->insert(
        array(
          'team_id' => $this->teamRow->id,
          'group_id' => $values['group_id']
        )
      );
    }

    // $this->tablesRepository->insert(array('team_id' => $team));
    $this->flashMessage(self::CHANGES_SAVED_SUCCESSFULLY, self::SUCCESS);
    $this->redirect('all');
  }

}
