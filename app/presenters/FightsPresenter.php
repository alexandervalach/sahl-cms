<?php

namespace App\Presenters;

use App\Helpers\FormHelper;
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

    if (!$this->fightRow || !$this->fightRow->is_present) {
      throw new BadRequestException(self::FIGHT_NOT_FOUND);
    }

    $this->roundRow = $this->fightRow->ref('rounds', 'round_id');
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

    if (!$this->fightRow || !$this->fightRow->is_present) {
      throw new BadRequestException(self::FIGHT_NOT_FOUND);
    }

    $this->roundRow = $this->fightRow->ref('rounds', 'round_id');
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
    $form->addProtection('Platnosť formulára vypršala. Obnovte stránku a skúste to znova.');
    $form->addSelect('team1_id', 'Tím 1', $teams);
    $form->addInteger('score1', 'Skóre 1')
      ->setRequired()
      ->addRule(Form::MIN, 'Skóre nemôže byť záporné.', 0);
    $form->addSelect('team2_id', 'Tím 2', $teams);
    $form->addInteger('score2', 'Skóre 2')
      ->setRequired()
      ->addRule(Form::MIN, 'Skóre nemôže byť záporné.', 0);
    $form->addCheckbox('is_overtime', ' Výsledok po predĺžení')
      ->setOption('description', 'Zaškrtnite iba vtedy, ak bol zápas rozhodnutý po predĺžení.');
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
      $this->tableEntriesRepository->getConnection()->transaction(function () {
        $this->tableEntriesRepository->applyFightResult(
          (int) $this->fightRow->table_id,
          (int) $this->fightRow->team1_id,
          (int) $this->fightRow->team2_id,
          (int) $this->fightRow->score1,
          (int) $this->fightRow->score2,
          (bool) $this->fightRow->is_overtime,
          -1
        );

        $this->fightsRepository->remove((int) $this->fightRow->id);
      });

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

    if ((bool) $values->is_overtime && (int) $values->score1 === (int) $values->score2) {
      $form->addError('Zápas po predĺžení nemôže skončiť remízou.');
      return false;
    }

    $tableId = (int) $this->fightRow->table_id;

    // Editing may change the teams as well. Both must already belong to the
    // same standings table, otherwise there is no row that can be updated.
    if (!$this->tableEntriesRepository->getByTableAndTeam((int) $values->team1_id, $tableId)
        || !$this->tableEntriesRepository->getByTableAndTeam((int) $values->team2_id, $tableId)) {
      $form->addError('Zvolené tímy nepatria do tabuľky tohto zápasu.');
      return false;
    }

    $this->tableEntriesRepository->getConnection()->transaction(function () use ($tableId, $values) {
      // First remove the old result from the table.
      $this->tableEntriesRepository->applyFightResult(
        $tableId,
        (int) $this->fightRow->team1_id,
        (int) $this->fightRow->team2_id,
        (int) $this->fightRow->score1,
        (int) $this->fightRow->score2,
        (bool) $this->fightRow->is_overtime,
        -1
      );

      $values['round_id'] = $this->roundRow->id;
      $this->fightRow->update($values);

      // Then apply the corrected result using the new 3/2/1/0 scoring.
      $this->tableEntriesRepository->applyFightResult(
        $tableId,
        (int) $values->team1_id,
        (int) $values->team2_id,
        (int) $values->score1,
        (int) $values->score2,
        (bool) $values->is_overtime
      );
    });

    $this->flashMessage(self::ITEM_UPDATED, self::SUCCESS);
    $this->redirect('Rounds:view', $this->roundRow->id);
  }

}
