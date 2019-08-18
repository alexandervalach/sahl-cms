<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\Forms\GroupFormFactory;
use App\Forms\ModalRemoveFormFactory;
use App\Forms\TeamFormFactory;
use App\Model\LinksRepository;
use App\Model\SeasonsGroupsRepository;
use App\Model\SponsorsRepository;
use App\Model\TeamsRepository;
use App\Model\GroupsRepository;
use App\Model\SeasonsGroupsTeamsRepository;
use Nette\Application\UI\Form;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\ArrayHash;

/**
 * Class GroupsPresenter
 * @package App\Presenters
 */
class GroupsPresenter extends BasePresenter
{

  /** @var ActiveRow */
  private $groupRow;

  /**
   * @var GroupFormFactory
   */
  private $groupFormFactory;

  /**
   * @var ModalRemoveFormFactory
   */
  private $removeFormFactory;

  /**
   * @var TeamFormFactory
   */
  private $teamFormFactory;

  /**
   * GroupsPresenter constructor.
   * @param GroupsRepository $groupsRepository
   * @param LinksRepository $linksRepository
   * @param SponsorsRepository $sponsorsRepository
   * @param TeamsRepository $teamsRepository
   * @param SeasonsGroupsRepository $seasonsGroupsRepository
   * @param SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository
   * @param GroupFormFactory $groupFormFactory
   * @param ModalRemoveFormFactory $removeFormFactory
   * @param TeamFormFactory $teamFormFactory
   */
  public function __construct(
      GroupsRepository $groupsRepository,
      LinksRepository $linksRepository,
      SponsorsRepository $sponsorsRepository,
      TeamsRepository $teamsRepository,
      SeasonsGroupsRepository $seasonsGroupsRepository,
      SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository,
      GroupFormFactory $groupFormFactory,
      ModalRemoveFormFactory $removeFormFactory,
      TeamFormFactory $teamFormFactory
  )
  {
    parent::__construct($groupsRepository, $linksRepository, $sponsorsRepository, $teamsRepository,
        $seasonsGroupsRepository, $seasonsGroupsTeamsRepository);
    $this->groupsRepository = $groupsRepository;
    $this->groupFormFactory = $groupFormFactory;
    $this->removeFormFactory = $removeFormFactory;
    $this->teamFormFactory = $teamFormFactory;
  }

  /**
   *
   */
  public function actionAll(): void
  {
    $this->userIsLogged();
  }

  /**
   *
   */
  public function renderAll(): void
  {
    $this->template->groups = ArrayHash::from($this->groups);
  }

  /**
   * @param int $id
   */
  public function actionView(int $id): void
  {
    $this->groupRow = $this->groupsRepository->findById($id);

    if (!$this->groupRow) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }

    if ($this->user->isLoggedIn()) {
      $this['groupForm']->setDefaults($this->groupRow);
    }
  }

  /**
   * @param int $id
   */
  public function renderView(int $id): void
  {
    $this->template->group = ArrayHash::from($this->groups[$this->groupRow->id]);
  }

  /**
   * Generates new add/edit group form
   * @return Form
   */
  protected function createComponentGroupForm(): Form
  {
    return $this->groupFormFactory->create( function (Form $form, ArrayHash $values) {
      $this->userIsLogged();
      $id = $this->getParameter('id');

      if ($id) {
        $this->groupRow->update($values);
        $this->flashMessage(self::ITEM_UPDATED, self::SUCCESS);
        $this->redirect('view', $this->groupRow->id);
      } else {
        $this->groupRow = $this->groupsRepository->getByLabel($values->label);

        if (!$this->groupRow) {
          $this->groupRow = $this->groupsRepository->insert($values);
        }

        $this->seasonsGroupsRepository->insert( array('group_id' => $this->groupRow->id) );
        $this->flashMessage(self::ITEM_ADDED_SUCCESSFULLY, self::SUCCESS);
        $this->redirect('all');
      }
    });
  }

  /**
   * Generates new remove group form
   * @return Form
   */
  protected function createComponentRemoveForm(): Form
  {
    return $this->removeFormFactory->create( function() {
      $this->userIsLogged();
      $seasonGroup = $this->seasonsGroupsRepository->getSeasonGroup($this->groupRow->id);

      if ($seasonGroup) {
        $this->seasonsGroupsRepository->remove($seasonGroup->id);
      }

      // $this->groupsRepository->remove($this->groupRow->id);
      $this->flashMessage(self::ITEM_REMOVED_SUCCESSFULLY, self::SUCCESS);
      $this->redirect('all');
    });
  }

  /**
   * Generates new team form
   * @return Form
   */
  protected function createComponentTeamForm(): Form
  {
    return $this->teamFormFactory->create(function (Form $form, ArrayHash $values) {
      $this->userIsLogged();
      $team = $this->teamsRepository->findByName($values->name);

      if (!$team) {
        $team = $this->teamsRepository->insert( array('name' => $values->name) );
      }

      $seasonGroup = $this->seasonsGroupsRepository->getSeasonGroup($values->group_id);

      if ($seasonGroup && $team) {
        $this->seasonsGroupsTeamsRepository->insert(
          array(
            'season_group_id' => $seasonGroup->id,
            'team_id' => $team->id
          )
        );
        $this->flashMessage(self::ITEM_ADDED_SUCCESSFULLY, self::SUCCESS);
      } else {
        $this->flashMessage(self::ITEM_NOT_ADDED, self::DANGER);
      }

      // TODO: Insert also team entry to tables
      // $this->tablesRepository->insert(array('team_id' => $team));
      $this->redirect('view', $this->groupRow->id);
    });
  }

}
