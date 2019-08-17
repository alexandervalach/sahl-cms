<?php

namespace App\Presenters;

use App\FormHelper;
use App\Forms\ArchiveFormFactory;
use App\Forms\SeasonFormFactory;
use App\Model\GroupsRepository;
use App\Model\LinksRepository;
use App\Model\SeasonsGroupsRepository;
use App\Model\SponsorsRepository;
use App\Model\TeamsRepository;
use App\Model\SeasonsRepository;
use App\Model\SeasonsGroupsTeamsRepository;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\ArrayHash;

class SeasonsPresenter extends BasePresenter
{
  /** @var ActiveRow */
  private $seasonRow;

  /** @var SeasonsRepository */
  private $seasonsRepository;

  /** @var ArchiveFormFactory */
  private $archiveFormFactory;

  /** @var SeasonFormFactory */
  private $seasonFormFactory;

  public function __construct(
      LinksRepository $linksRepository,
      SponsorsRepository $sponsorsRepository,
      TeamsRepository $teamsRepository,
      SeasonsRepository $seasonsRepository,
      SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository,
      ArchiveFormFactory $archiveFormFactory,
      SeasonFormFactory $seasonFormFactory,
      SeasonsGroupsRepository $seasonsGroupsRepository,
      GroupsRepository $groupsRepository
  )
  {
    parent::__construct($groupsRepository, $linksRepository, $sponsorsRepository, $teamsRepository,
        $seasonsGroupsRepository, $seasonsGroupsTeamsRepository);
    $this->seasonsRepository = $seasonsRepository;
    $this->archiveFormFactory = $archiveFormFactory;
    $this->seasonFormFactory = $seasonFormFactory;
  }

  /**
   * Prepare data for season render
   */
  public function renderAll(): void
  {
    $this->template->seasons = $this->seasonsRepository->getAll();
  }

  /**
   * @param int $id
   */
  public function actionView(int $id): void
  {
    $this->seasonRow = $this->seasonsRepository->findById($id);

    if (!$this->seasonRow || !$this->seasonRow->is_present) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }

    if ($this->user->isLoggedIn()) {
      $this['seasonForm']->setDefaults($this->seasonRow);
    }
  }

  /**
   * @param int $id
   */
  public function renderView(int $id): void
  {
    $this->template->season = $this->seasonRow;
  }

  /**
   * Renders season form
   * @return Form
   */
  protected function createComponentSeasonForm(): Form
  {
    return $this->seasonFormFactory->create(function (Form $form, ArrayHash $values) {
      $id = $this->getParameter('id');

      if ($id) {
        $this->seasonRow->update($values);
        $this->flashMessage(self::CHANGES_SAVED_SUCCESSFULLY, self::SUCCESS);
      } else {
        $this->seasonsRepository->insert($values);
        $this->flashMessage(self::ITEM_ADDED_SUCCESSFULLY, self::SUCCESS);
      }

      $this->redirect('all');
    });
  }

  /**
   * @return Form
   */
  protected function createComponentArchiveForm(): Form
  {
    return $this->archiveFormFactory->create(function (Form $form, ArrayHash $values) {
      $this->submittedArchiveForm();
    });
  }

  /**
   * Submitted remove form
   */
  public function submittedRemoveForm(): void
  {
    $this->redirect('all');
  }

  /**
   * Submitted remove form
   */
  public function submittedArchiveForm(): void
  {
    /*
    $team_id = array();
    $player_id = array();
    $arch_id = array('season_id' => $this->seasonRow->id);

    $this->roundsRepository->archive($this->seasonRow->id);
    $this->flashMessage('Kolá boli archivované', self::SUCCESS);
    $this->eventsRepository->archive($this->seasonRow->id);
    $this->flashMessage('Rozpis zápasov bol archivovaný', self::SUCCESS);
    $this->rulesRepository->archive($this->seasonRow->id);
    $this->flashMessage('Pravidlá a smernice boli archivované', self::SUCCESS);

    // Vytvoríme duplicitné záznamy tímov s novým archive id
    $teams = $this->teamsRepository->getAsArray($this->seasonRow->id);

    if ($teams != null) {
      foreach ($teams as $team) {
        $data = array(
            'name' => $team->name,
            'image' => $team->image,
            'season_id' => $this->seasonRow->id
        );

        $id = $this->teamsRepository->insert($data);

        if ($id == null) {
            $this->flashMessage('Nastala chyba počas archivácie tímov', self::DANGER);
            $this->redirect('all');
        } else {
            $team_id[$team->id] = $id;
        }
      }
      $this->flashMessage('Tímy boli archivované', self::SUCCESS);
    }

    // Vytvoríme duplicitné záznamy o hráčoch s novým archive_id
    $data = array();
    $players = $this->playersRepository->getAsArray($this->seasonRow->id);
    if ($players != null) {

        foreach ($players as $player) {
            if (isset($team_id[$player->team_id])) {
                $data['team_id'] = $team_id[$player->team_id];
                $data['type_id'] = $player->type_id;
                $data['name'] = $player->name;
                $data['num'] = $player->num;
                $data['born'] = $player->born;
                $data['goals'] = $player->goals;
                $data['trans'] = $player->trans;
                $this->playersRepository->insert($data);
                $player_id[$player->id] = $id;
            } else {
                $this->flashMessage('Nastala chyba počas archivácie hráčov', self::DANGER);
                break;
            }
        }
        $this->flashMessage('Hráči boli archivovaní', self::SUCCESS);
    }

    $tables = $this->tablesRepository->findByValue('archive_id', null);

    if ($tables->count()) {

        $data = array(
            'team_id' => null,
            'archive_id' => $this->archiveRow->id
        );

        foreach ($tables as $table) {
            if (isset($team_id[$table->team_id])) {
                $data['team_id'] = $team_id[$table->team_id];
                $table->update($data);
            } else {
                $this->flashMessage('Nastala chyba počas archivácie tabuliek', self::DANGER);
                break;
            }
        }

        $this->flashMessage('Tabuľky boli archivované', self::SUCCESS);
    }

    $puns = $this->punishmentsRepository->findByValue('archive_id', null);

    if ($puns->count()) {

        foreach ($puns as $pun) {

            $data = array(
                'player_id' => null,
                'archive_id' => $this->archiveRow->id
            );

            if (isset($player_id[$pun->player_id])) {
                $data['player_id'] = $player_id[$pun->player_id];
                $pun->update($data);
            } else {
                $this->flashMessage('Nastala chyba počas archivácie trestov hráčov', self::DANGER);
                break;
            }
        }

        $this->flashMessage('Tresty boli archivované', self::SUCCESS);
    }

    $fights = $this->fightsRepository->findByValue('archive_id', null);

    if ($fights->count()) {

        $data = array(
            'team1_id' => null,
            'team2_id' => null,
            'archive_id' => $this->archiveRow->id
        );

        foreach ($fights as $fight) {
            if (isset($team_id[$fight->team1_id]) && isset($team_id[$fight->team2_id])) {
                $data['team1_id'] = $team_id[$fight->team1_id];
                $data['team2_id'] = $team_id[$fight->team2_id];
                $fight->update($data);
            } else {
                $this->flashMessage('Počas archivácie výsledkov zápasov nastala chyba', self::DANGER);
                break;
            }
        }

        $this->flashMessage('Zápasy boli archivované', self::SUCCESS);
    }

    $goals = $this->goalsRepository->findByValue('archive_id', null);

    if ($goals->count()) {

        $data = array(
            'player_id' => null,
            'archive_id' => $this->archiveRow->id
        );

        foreach ($goals as $goal) {
            if (isset($player_id[$goal->player_id])) {
                $data['player_id'] = $player_id[$goal->player_id];
                $id = $goal->update($data);
            } else {
                $this->flashMessage('Nastala chyba počas archivácie gólov', self::DANGER);
                break;
            }
        }

        $this->flashMessage('Góly boli archivované', self::SUCCESS);
    }
    */

    $this->redirect('view', $this->seasonRow->id);
  }

}
