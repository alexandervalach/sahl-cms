<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\FormHelper;
use App\Model\LinksRepository;
use App\Model\SponsorsRepository;
use App\Model\TeamsRepository;
use App\Model\PlayersRepository;
use App\Model\PlayerTypesRepository;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\ArrayHash;

class PlayersPresenter extends BasePresenter
{
  const PLAYER_NOT_FOUND = "Player not found";
  const TEAM_NOT_FOUND = "Team not found";

  /** @var ActiveRow */
  private $playerRow;

  /** @var ActiveRow */
  private $teamRow;

  /** @var ActiveRow */
  private $archRow;

  /** @var ArrayHash */
  private $playerData;

  /** @var PlayersRepository */
  private $playersRepository;

  /** @var PlayerTypesRepository */
  private $playerTypesRepository;

  public function __construct(
    LinksRepository $linksRepository,
    SponsorsRepository $sponsorsRepository,
    TeamsRepository $teamsRepository,
    PlayersRepository $playersRepository,
    PlayerTypesRepository $playerTypesRepository
  )
  {
    parent::__construct($linksRepository, $sponsorsRepository, $teamsRepository);
    $this->playersRepository = $playersRepository;
    $this->playerTypesRepository = $playerTypesRepository;
  }

  public function renderAll(): void
  {
    $this->template->players = $this->playersRepository->getForSeason();
    $this->template->i = 0;
    $this->template->j = 0;
    $this->template->current = 0;
    $this->template->previous = 0;
  }

  /**
   * @param int $id
   * @throws Nette\Application\BadRequestException
   */
  public function actionView(int $id): void
  {
    $this->playerRow = $this->playersRepository->findById($id);
    if (!$this->playerRow || !$this->playerRow->is_present) {
      throw new BadRequestException(self::PLAYER_NOT_FOUND);
    }

    $data = [];
    $row = $this->teamsRepository->getForPlayer($this->playerRow->id);

    $data['player']['name'] = $this->playerRow->name;
    $data['player']['number'] = $this->playerRow->number;
    $data['player']['photo'] = $this->playerRow->photo;
    $data['player']['goals'] = $row->offsetGet('goals');
    $data['player']['is_transfer'] = $row->offsetGet('is_transfer');
    $data['team']['id'] = $row->offsetGet('team_id');
    $data['team']['name'] = $row->offsetGet('team_name');
    $data['team']['logo'] = $row->offsetGet('team_logo');
    $data['type']['label'] = $row->offsetGet('type_label');
    $data['type']['abbr'] = $row->offsetGet('type_abbr');
    $data['group']['label'] = $row->offsetGet('group_label');

    $this->playerData = ArrayHash::from($data);

    if ($this->user->isLoggedIn()) {
      $this->getComponent(self::EDIT_FORM)->setDefaults($this->playerData->player);
    }
  }

  public function renderView(int $id): void
  {
    $this->template->player = $this->playerData->player;
    $this->template->team = $this->playerData->team;
    $this->template->type = $this->playerData->type;
  }

  public function actionArchAll($id): void
  {
    $this->archRow = $this->seasonsRepository->findById($id);
  }

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

  public function actionArchView(int $id, $param): void
  {
    $this->teamRow = $this->teamsRepository->findById($param);
    if (!$this->teamRow || !$this->teamRow->is_present) {
      throw new BadRequestException($this->error);
    }
  }

  public function renderArchView(int $id, $param): void
  {
    $this->template->players = $this->playersRepository->findByValue('team_id', $param)->where('archive_id', $id)->where('NOT type_id', 2);
    $this->template->goalies = $players = $this->playersRepository->findByValue('team_id', $param)->where('archive_id', $id)->where('type_id', 2);
    $this->template->team = $this->teamRow;
    $this->template->archive = $this->teamRow->ref('archive', 'archive_id');
  }

  protected function createComponentEditForm(): Form
  {
    $types = $this->playerTypesRepository->getTypes();

    $form = new Form;
    $form->addText('name', 'Meno a priezvisko')
          ->setAttribute('placeholder', 'Zdeno Chára')
          ->addRule(Form::FILLED, 'Meno musí byť vyplnené');
    $form->addText('number', 'Číslo')
          ->setAttribute('placeholder', 14);
    $form->addText('goals', 'Góly');
    $form->addSelect('type_id', 'Typ hráča', $types);
    $form->addCheckbox('is_transfer', ' Prestupový hráč');
    $form->addSubmit('save', 'Uložiť')
          ->setAttribute('class', self::BTN_SUCCESS);
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  protected function createComponentResetForm(): Form
  {
    $form = new Form;
    $form->addSubmit('reset', 'Vynulovať')
          ->setAttribute('class', self::BTN_DANGER);
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, self::SUBMITTED_RESET_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  public function submittedEditForm(Form $form, $values): void
  {
    $this->playerRow->update($values);
    $this->flashMessage('Hráč bol upravený', self::SUCCESS);
    $this->redirect('view', $this->playerRow);
  }

  public function submittedRemoveForm(): void
  {
    $team = $this->teamsRepository->getForPlayer($this->playerRow);
    $this->playersRepository->remove($this->playerRow);
    $this->flashMessage('Hráč bol odstránený.', self::SUCCESS);
    $this->redirect('Teams:view', $team);
  }

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
