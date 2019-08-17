<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\FormHelper;
use App\Forms\EventAddFormFactory;
use App\Forms\EventEditFormFactory;
use App\Forms\RemoveFormFactory;
use App\Model\GroupsRepository;
use App\Model\LinksRepository;
use App\Model\SeasonsGroupsRepository;
use App\Model\SponsorsRepository;
use App\Model\TeamsRepository;
use App\Model\EventsRepository;
use App\Model\SeasonsGroupsTeamsRepository;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\ArrayHash;
use Nette\Forms\Controls\SubmitButton;

class EventsPresenter extends BasePresenter
{
  const EVENT_NOT_FOUND = 'Event not found';

  /** @var ActiveRow */
  private $eventRow;

  /** @var ActiveRow */
  private $seasonRow;

  /** @var EventsRepository */
  private $eventsRepository;

  /** @var EventAddFormFactory */
  private $eventAddFormFactory;

  /** @var EventEditFormFactory */
  private $eventEditFormFactory;

  /** @var RemoveFormFactory */
  private $removeFormFactory;

  public function __construct(
      LinksRepository $linksRepository,
      SponsorsRepository $sponsorsRepository,
      TeamsRepository $teamsRepository,
      EventsRepository $eventsRepository,
      SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository,
      EventAddFormFactory $eventAddFormFactory,
      EventEditFormFactory $eventEditFormFactory,
      RemoveFormFactory $removeFormFactory,
      GroupsRepository $groupsRepository,
      SeasonsGroupsRepository $seasonsGroupsRepository
  )
  {
    parent::__construct($groupsRepository, $linksRepository, $sponsorsRepository, $teamsRepository,
        $seasonsGroupsRepository, $seasonsGroupsTeamsRepository);
    $this->eventsRepository = $eventsRepository;
    $this->eventAddFormFactory = $eventAddFormFactory;
    $this->eventEditFormFactory = $eventEditFormFactory;
    $this->removeFormFactory = $removeFormFactory;
  }

  /**
   * Renders data for all view
   */
  public function renderAll(): void
  {
    $this->template->events = $this->eventsRepository->getArchived()->order('id DESC');
  }

  /**
   * Authenticates user and loads data from repository
   * @param int $id
   */
  public function actionEdit(int $id): void
  {
    $this->userIsLogged();
    $this->eventRow = $this->eventsRepository->findById($id);

    if (!$this->eventRow || !$this->eventRow->is_present) {
      throw new BadRequestException(self::EVENT_NOT_FOUND);
    }

    if ($this->user->isLoggedIn()) {
      $this[self::EDIT_FORM]->setDefaults($this->eventRow);
    }
  }

  /**
   * Passes data to template
   * @param int $id
   */
  public function renderEdit(int $id): void
  {
    $this->template->event = $this->eventRow;
  }

  /**
   * Remove action handler
   * @param int $id
   */
  public function actionRemove(int $id): void
  {
    $this->userIsLogged();
    $this->eventRow = $this->eventsRepository->findById($id);

    if (!$this->eventRow || !$this->eventRow->is_present) {
      throw new BadRequestException(self::EVENT_NOT_FOUND);
    }
  }

  /**
   * Passes data to template
   * @param int $id
   */
  public function renderRemove(int $id): void
  {
    $this->template->event = $this->eventRow;
  }

  /**
   * Get data for event arch page
   * @param int $id
   */
  public function actionArchAll(int $id): void
  {
    $this->seasonRow = $this->seasonsRepository->findById($id);
  }

  /**
   * Renders arch view page
   * @param int $id
   */
  public function renderArchAll(int $id): void
  {
    $this->template->archive = $this->seasonRow;
    $this->template->events = $this->eventsRepository->getArchived($id)->order('id DESC');
  }

  /**
   * Creates add form component
   * @return Form
   */
  protected function createComponentAddForm(): Form
  {
    return $this->eventAddFormFactory->create(function (Form $form, ArrayHash $values) {
      $this->userIsLogged();
      $this->eventsRepository->insert($values);
      $this->flashMessage('Rozpis bol pridaný', self::SUCCESS);
      $this->redirect('all');
    });
  }

  /**
   * Creates edit form component
   * @return Form
   */
  protected function createComponentEditForm(): Form
  {
    return $this->eventEditFormFactory->create(function (SubmitButton $button, ArrayHash $values) {
      $this->userIsLogged();
      $this->eventRow->update($values);
      $this->flashMessage('Rozpis bol upravený', self::SUCCESS);
      $this->redirect('all');
    }, function () {
      $this->redirect('all');
    });
  }

  /**
   * Renders remove form component
   * @return Nette\Application\UI\Form
   */
  protected function createComponentRemoveForm(): Form
  {
    return $this->removeFormFactory->create(function () {
      $this->userIsLogged();
      $this->eventsRepository->remove($this->eventRow->id);
      $this->flashMessage('Rozpis bol odstránený', self::SUCCESS);
      $this->redirect('all');
    }, function () {
      $this->redirect('all');
    });
  }
}
