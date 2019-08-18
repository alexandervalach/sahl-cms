<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\FormHelper;
use App\Model\GroupsRepository;
use App\Model\LinksRepository;
use App\Model\SeasonsGroupsRepository;
use App\Model\SponsorsRepository;
use App\Model\TeamsRepository;
use App\Model\PlayersRepository;
use App\Model\PunishmentsRepository;
use App\Model\SeasonsGroupsTeamsRepository;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\ArrayHash;

/**
 * Class PunishmentsPresenter
 * @package App\Presenters
 */
class PunishmentsPresenter extends BasePresenter
{
  /** @var ActiveRow */
  private $punishmentRow;

  /** @var ActiveRow */
  private $seasonRow;

  /** @var array */
  private $punishments;

  /** @var PunishmentsRepository */
  private $punishmentsRepository;

  /** @var PlayersRepository */
  private $playersRepository;

  /**
   * PunishmentsPresenter constructor.
   * @param LinksRepository $linksRepository
   * @param SponsorsRepository $sponsorsRepository
   * @param TeamsRepository $teamsRepository
   * @param PlayersRepository $playersRepository
   * @param PunishmentsRepository $punishmentsRepository
   * @param SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository
   * @param GroupsRepository $groupsRepository
   * @param SeasonsGroupsRepository $seasonsGroupsRepository
   */
  public function __construct(
      LinksRepository $linksRepository,
      SponsorsRepository $sponsorsRepository,
      TeamsRepository $teamsRepository,
      PlayersRepository $playersRepository,
      PunishmentsRepository $punishmentsRepository,
      SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository,
      GroupsRepository $groupsRepository,
      SeasonsGroupsRepository $seasonsGroupsRepository
  )
  {
    parent::__construct($groupsRepository, $linksRepository, $sponsorsRepository, $teamsRepository,
        $seasonsGroupsRepository, $seasonsGroupsTeamsRepository);
    $this->playersRepository = $playersRepository;
    $this->punishmentsRepository = $punishmentsRepository;
  }

  /**
   * @param int $groupId
   */
  public function actionAll(int $groupId): void
  {
    $this->punishments = array();
  }

  /**
   * @param int $groupId
   */
  public function renderAll(int $groupId): void
  {
    $this->template->punishments = $this->punishmentsRepository->getForSeason();
  }

  /**
   * @param int $id
   */
  public function actionEdit(int $id): void
  {
    $this->userIsLogged();
    $this->punishmentRow = $this->punishmentsRepository->findById($id);

    if (!$this->punishmentRow || !$this->punishmentRow->is_present) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }

    $this->getComponent(self::EDIT_FORM)->setDefaults($this->punishmentRow);
  }

  /**
   * @param int $id
   */
  public function renderEdit(int $id): void
  {
    $this->template->player = $this->punishmentRow->ref('players', 'player_id');
  }

  /**
   * @param $id
   */
  public function actionRemove($id): void
  {
    $this->userIsLogged();
    $this->punishmentRow = $this->punishmentsRepository->findById($id);

    if (!$this->punishmentRow || !$this->punishmentRow->is_present) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }
  }

  /**
   * @param int $id
   */
  public function renderRemove(int $id): void
  {
    $this->template->punishment = $this->punishmentRow;
  }

  /**
   * @param int $id
   */
  public function actionArchAll(int $id): void
  {
    $this->seasonRow = $this->seasonsRepository->findById($id);
  }

  /**
   * @param int $id
   */
  public function renderArchAll(int $id): void
  {
    $this->template->season = $this->seasonRow;
    $this->template->punishments = $this->punishmentsRepository->getArchived($id);
  }

  /**
   * @return Nette\Application\UI\Form;
   */
  protected function createComponentEditForm(): Form
  {
    $form = new Form;
    $form->addText('text', 'Dôvod')
          ->setAttribute('placeholder', 'Nešportové správanie');
    $form->addText('round', 'Kolá')
          ->setAttribute('placeholder', '3. kolo');
    $form->addCheckbox('condition', ' Podmienka');
    $form->addSubmit('save', 'Uložiť');
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  /**
   * @return Form
   */
  protected function createComponentAddForm(): Form
  {
    $players = $this->playersRepository->getNonEmptyPlayers();
    $form = new Form;
    $form->addSelect('player_id', 'Hráč*', $players);
    $form->addText('text', 'Dôvod')
          ->setAttribute('placeholder', 'Nešportové správanie');
    $form->addText('round', 'Stop na kolo')
          ->setAttribute('placeholder', '3. kolo');
    $form->addCheckbox('condition', ' Podmienka');
    $form->addSubmit('save', 'Uložiť');
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, self::SUBMITTED_ADD_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  /**
   * @param Form $form
   * @param ArrayHash $values
   */
  public function submittedEditForm(Form $form, ArrayHash $values): void
  {
    $this->userIsLogged();
    $this->punishmentRow->update($values);
    $this->flashMessage('Trest bol upravený', self::SUCCESS);
    $this->redirect('all');
  }

  /**
   * @param Form $form
   * @param ArrayHash $values
   */
  public function submittedAddForm(Form $form, ArrayHash $values): void
  {
    $this->userIsLogged();
    $this->punishmentsRepository->insert($values);
    $this->flashMessage('Trest bol pridaný', self::SUCCESS);
    $this->redirect('all');
  }

  /**
   *
   */
  public function submittedRemoveForm(): void
  {
    $this->userIsLogged();
    $this->punishmentsRepository->remove($this->punishmentRow->id);
    $this->flashMessage('Trest bol odstránený', self::SUCCESS);
    $this->redirect('all');
  }
}
