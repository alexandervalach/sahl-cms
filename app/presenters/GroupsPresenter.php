<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\Forms\GroupFormFactory;
use App\Forms\ModalRemoveFormFactory;
use App\Forms\TeamFormFactory;
use App\Model\LinksRepository;
use App\Model\SeasonsGroupsRepository;
use App\Model\SponsorsRepository;
use App\Model\TablesRepository;
use App\Model\TableTypesRepository;
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

  /** @var GroupFormFactory */
  private $groupFormFactory;

  /** @var ModalRemoveFormFactory */
  private $removeFormFactory;

  /** @var TeamFormFactory  */
  private $teamFormFactory;

  /** @var TableTypesRepository */
  private $tableTypesRepository;

  /** @var TablesRepository */
  private $tablesRepository;

  /**
   * GroupsPresenter constructor.
   * @param GroupsRepository $groupsRepository
   * @param LinksRepository $linksRepository
   * @param SponsorsRepository $sponsorsRepository
   * @param TeamsRepository $teamsRepository
   * @param SeasonsGroupsRepository $seasonsGroupsRepository
   * @param SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository
   * @param TableTypesRepository $tableTypesRepository
   * @param TablesRepository $tablesRepository
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
      TableTypesRepository $tableTypesRepository,
      TablesRepository $tablesRepository,
      GroupFormFactory $groupFormFactory,
      ModalRemoveFormFactory $removeFormFactory,
      TeamFormFactory $teamFormFactory
  )
  {
    parent::__construct($groupsRepository, $linksRepository, $sponsorsRepository, $teamsRepository,
        $seasonsGroupsRepository, $seasonsGroupsTeamsRepository);
    $this->groupsRepository = $groupsRepository;
    $this->tableTypesRepository = $tableTypesRepository;
    $this->tablesRepository = $tablesRepository;
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
   * Authenticates user and loads data from repository
   * @param int $id
   */
  public function actionView(int $id): void
  {
    $this->groupRow = $this->groupsRepository->findById($id);

    if (!$this->groupRow || !$this->groupRow->is_present) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }

    if ($this->user->isLoggedIn()) {
      $this['groupForm']->setDefaults($this->groupRow);
    }
  }

  /**
   * Passes data to template
   * @param int $id
   */
  public function renderView(int $id): void
  {
    $this->template->group = $this->groupRow;
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
        $this->updateGroup($values);
      } else {
        $this->addGroup($values);
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
   * @param ArrayHash $values
   */
  private function addGroup (ArrayHash $values): void
  {
    $this->groupRow = $this->getGroup($values->label);
    $seasonGroup = $this->seasonsGroupsRepository->insertData($this->groupRow->id);

    if ($seasonGroup) {
      $tableType = $this->getTableType(self::BASE_TABLE_LABEL);

      if ($tableType) {
        $this->getTable($tableType->id, $seasonGroup->id);
      }
    }

    $this->flashMessage(self::ITEM_ADDED_SUCCESSFULLY, self::SUCCESS);
    $this->redirect('all');
  }

  /**
   * @param ArrayHash $values
   */
  private function updateGroup (ArrayHash $values): void
  {
    if ($this->groupRow) {
      $this->groupRow->update($values);
      $this->flashMessage(self::ITEM_UPDATED, self::SUCCESS);
      $this->redirect('view', $this->groupRow->id);
    }
  }

  /**
   * @param string $label
   * @return bool|int|\Nette\Database\IRow|ActiveRow|null
   */
  private function getGroup (string $label)
  {
    $group = $this->groupsRepository->getByLabel($label);
    return $group ? $group : $this->groupsRepository->insertData($label);
  }

  /**
   * @param string $label
   * @return bool|int|\Nette\Database\IRow|ActiveRow|null
   */
  private function getTableType (string $label)
  {
    $tableType = $this->tableTypesRepository->findByLabel($label);
    return $tableType ? $tableType : $this->tableTypesRepository->insertData($label);
  }

  /**
   * @param int $tableTypeId
   * @param int $seasonGroupId
   * @return bool|int|\Nette\Database\IRow|ActiveRow|null
   */
  private function getTable(int $tableTypeId, int $seasonGroupId)
  {
    $table = $this->tablesRepository->getByType($tableTypeId, $seasonGroupId);
    return $table ? $table : $this->tablesRepository->insertData($tableTypeId, $seasonGroupId);
  }

}
