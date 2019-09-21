<?php

namespace App\Presenters;

use App\FormHelper;
use App\Forms\RemoveFormFactory;
use App\Model\FightsRepository;
use App\Model\GroupsRepository;
use App\Model\LinksRepository;
use App\Model\SeasonsGroupsRepository;
use App\Model\SponsorsRepository;
use App\Model\TableEntriesRepository;
use App\Model\TablesRepository;
use App\Model\TeamsRepository;
use App\Model\SeasonsGroupsTeamsRepository;
use Nette\Application\UI\Form;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\ArrayHash;

/**
 * Class FightsPresenter
 * @package App\Presenters
 */
class FightsPresenter extends BasePresenter
{
  const FIGHT_NOT_FOUND = 'Fight not found';

  /** @var ActiveRow */
  private $roundRow;

  /** @var ActiveRow */
  private $fightRow;

  /** @var ActiveRow */
  private $seasonRow;

  /** @var ActiveRow */
  private $team1;

  /** @var ActiveRow */
  private $team2;

  /** @var FightsRepository */
  private $fightsRepository;

  /** @var TablesRepository */
  private $tablesRepository;

  /** @var RemoveFormFactory */
  private $removeFormFactory;

  /** @var TableEntriesRepository */
  private $tableEntriesRepository;

  /**
   * FightsPresenter constructor.
   * @param LinksRepository $linksRepository
   * @param SponsorsRepository $sponsorsRepository
   * @param TeamsRepository $teamsRepository
   * @param FightsRepository $fightsRepository
   * @param TablesRepository $tablesRepository
   * @param RemoveFormFactory $removeFormFactory
   * @param GroupsRepository $groupsRepository
   * @param SeasonsGroupsRepository $seasonsGroupsRepository
   * @param SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository
   * @param TableEntriesRepository $tableEntriesRepository
   */
  public function __construct(
      LinksRepository $linksRepository,
      SponsorsRepository $sponsorsRepository,
      TeamsRepository $teamsRepository,
      FightsRepository $fightsRepository,
      TablesRepository $tablesRepository,
      RemoveFormFactory $removeFormFactory,
      GroupsRepository $groupsRepository,
      SeasonsGroupsRepository $seasonsGroupsRepository,
      SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository,
      TableEntriesRepository $tableEntriesRepository
  )
  {
    parent::__construct($groupsRepository, $linksRepository, $sponsorsRepository, $teamsRepository,
        $seasonsGroupsRepository, $seasonsGroupsTeamsRepository);
    $this->fightsRepository = $fightsRepository;
    $this->tablesRepository = $tablesRepository;
    $this->removeFormFactory = $removeFormFactory;
    $this->tableEntriesRepository = $tableEntriesRepository;
  }

  /**
   * @param int $id
   */
  public function actionEdit(int $id): void
  {
    $this->userIsLogged();
    $this->fightRow = $this->fightsRepository->findById($id);
    $this->roundRow = $this->fightRow->ref('rounds', 'round_id');

    if (!$this->fightRow || !$this->fightRow->is_present) {
      throw new BadRequestException(self::FIGHT_NOT_FOUND);
    }
  }

  /**
   * @param int $id
   */
  public function renderEdit(int $id): void
  {
    $this->template->round = $this->roundRow;
    $this[self::EDIT_FORM]->setDefaults($this->fightRow);
  }

  /**
   * @param int $id
   */
  public function actionRemove(int $id): void
  {
    $this->userIsLogged();
    $this->fightRow = $this->fightsRepository->findById($id);
    $this->roundRow = $this->fightRow->ref('rounds', 'round_id');

    if (!$this->fightRow || !$this->fightRow->is_present) {
      throw new BadRequestException(self::FIGHT_NOT_FOUND);
    }
  }

  /**
   * @param int $id
   */
  public function renderRemove(int $id): void
  {
    $this->template->fight = $this->fightRow;
  }

  /**
   * @param int $id
   * @param $param
   */
  public function actionArchView(int $id, $param): void
  {
    $this->roundRow = $this->roundsRepository->findById($param);
    $this->seasonRow = $this->seasonsRepository->findById($id);

    if (!$this->roundRow || !$this->roundRow->is_present) {
      throw new BadRequestException($this->error);
    }
  }

  /**
   * @param int $id
   * @param $param
   */
  public function renderArchView(int $id, $param): void
  {
    $this->template->fights = $this->fightsRepository
            ->findByValue('round_id', $param)
            ->where('archive_id', $id);
    $this->template->round = $this->roundRow;
    $this->template->archive = $this->roundRow->ref('archive', 'archive_id');
  }

  /**
   * @return Form
   */
  protected function createComponentEditForm(): Form
  {
    $teams = $this->teamsRepository->getTeams();
    $form = new Form;
    $form->addSelect('team1_id', 'Tím 1', $teams);
    $form->addText('score1', 'Skóre 1');
    $form->addSelect('team2_id', 'Tím 2', $teams);
    $form->addText('score2', 'Skóre 2');
    $form->addHidden('round_id', (string) $this->roundRow->id);
    $form->addSubmit('save', 'Uložiť');
    $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  /**
   * Component for creating a remove form
   * @return Form
   */
  protected function createComponentRemoveForm(): Form
  {
    return $this->removeFormFactory->create(function () {
      $state1 = $state2 = 'tram';

      if ($this->fightRow->score1 > $this->fightRow->score2) {
        $state1 = 'win';
        $state2 = 'lost';
      } else if ($this->fightRow->score2 > $this->fightRow->score1) {
        $state1 = 'lost';
        $state2 = 'win';
      }

      $this->tableEntriesRepository->updateEntry($this->fightRow->table_id, $this->fightRow->team1_id, $state1, -1);
      $this->tableEntriesRepository->updateEntry($this->fightRow->table_id, $this->fightRow->team2_id, $state2, -1);

      $this->updateTablePoints();
      $this->updateScore();

      $this->fightsRepository->remove($this->fightRow->id);
      $this->flashMessage(self::ITEM_REMOVED_SUCCESSFULLY, self::SUCCESS);
      $this->redirect('Rounds:view', $this->roundRow->id);
    }, function () {
      $this->redirect('Rounds:view', $this->roundRow->id);
    });
  }

  /**
   * @param Form $form
   * @param ArrayHash $values
   * @return bool
   */
  public function submittedEditForm(Form $form, ArrayHash $values)
  {
    if ($values->team1_id == $values->team2_id) {
      $form->addError('Zvoľte dva rozdielne tímy.');
      return false;
    }
    $values['round_id'] = $this->roundRow->id;
    $this->fightRow->update($values);
    $this->flashMessage(self::ITEM_UPDATED, self::SUCCESS);
    $this->redirect('Rounds:view', $this->roundRow->id);
  }

  /**
   * Updates points based on fight result
   */
  protected function updateTablePoints(): void
  {
    if ($this->fightRow) {
      if ($this->fightRow->score1 > $this->fightRow->score2) {
        $this->tableEntriesRepository->updatePoints($this->fightRow->table_id, $this->fightRow->team1_id, -2);
      } elseif ($this->fightRow->score2 > $this->fightRow->score1) {
        $this->tableEntriesRepository->updatePoints($this->fightRow->table_id, $this->fightRow->team2_id, -2);
      } else {
        $this->tableEntriesRepository->updatePoints($this->fightRow->table_id, $this->fightRow->team2_id, -1);
        $this->tableEntriesRepository->updatePoints($this->fightRow->table_id, $this->fightRow->team1_id, -1);
      }
    }
  }

  /**
   * Updates score for both teams
   */
  protected function updateScore(): void
  {
    if ($this->fightRow) {
      $this->tableEntriesRepository->updateEntry($this->fightRow->table_id, $this->fightRow->team1_id, 'score1', -$this->fightRow->score1);
      $this->tableEntriesRepository->updateEntry($this->fightRow->table_id, $this->fightRow->team1_id, 'score2', -$this->fightRow->score2);
      $this->tableEntriesRepository->updateEntry($this->fightRow->table_id, $this->fightRow->team2_id, 'score1', -$this->fightRow->score2);
      $this->tableEntriesRepository->updateEntry($this->fightRow->table_id, $this->fightRow->team2_id, 'score2', -$this->fightRow->score1);
    }
  }

}
