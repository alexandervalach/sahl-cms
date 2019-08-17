<?php

namespace App\Presenters;

use App\FormHelper;
use App\Model\GroupsRepository;
use App\Model\LinksRepository;
use App\Model\SeasonsGroupsRepository;
use App\Model\SponsorsRepository;
use App\Model\TeamsRepository;
use App\Model\TablesRepository;
use App\Model\SeasonsGroupsTeamsRepository;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;

class TablesPresenter extends BasePresenter
{
  const TABLE_NOT_FOUND = 'Table not found';

  /** @var array */
  private $tables;

  /** @var ActiveRow */
  private $tableRow;

  /** @var ActiveRow */
  private $archRow;

  /** @var TablesRepository */
  private $tablesRepository;

  public function __construct(
    LinksRepository $linksRepository,
    SponsorsRepository $sponsorsRepository,
    TeamsRepository $teamsRepository,
    TablesRepository $tablesRepository,
    SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository,
    GroupsRepository $groupsRepository,
    SeasonsGroupsRepository $seasonsGroupsRepository
  )
  {
    parent::__construct($groupsRepository, $linksRepository, $sponsorsRepository, $teamsRepository,
        $seasonsGroupsRepository, $seasonsGroupsTeamsRepository);
    $this->tablesRepository = $tablesRepository;
  }

  public function actionAll(): void
  {
    $tables = $this->tablesRepository->getForSeason();

    foreach ($tables as $table) {
      $this->tables[$table->id]['data'] = $table;
      $this->tables[$table->id]['entries'] = $table->related('table_entries')->order('points DESC, (score1 - score2) DESC');
      $this->tables[$table->id]['type'] = $table->ref('table_types', 'table_type_id');
    }
  }

  public function renderAll(): void
  {
    $this->template->tables = $this->tables;
  }

  public function actionAddToSidebar($id): void
  {
    $this->userIsLogged();
    $this->tableRow = $this->tablesRepository->findById($id);

    if (!$this->tableRow) {
      throw new BadRequestException(self::TABLE_ROW_NOT_FOUND);
    }

    $this->submittedSetVisible();
  }

  public function actionArchAll(int $id): void
  {
    $this->archRow = $this->seasonsRepository->findById($id);
  }

  public function renderArchAll(int $id): void
  {
    $tableTypes = $this->tableTypesRepository->findAll();
    $tableRows = array();

    foreach ($tableTypes as $type) {
      $tableRows[$type->name] = $this->tablesRepository
              ->findByValue('season_id', $this->archRow)
              ->where('table_type = ?', $type)
              ->order('points DESC, (score1 - score2) DESC');
    }

    $this->template->tables = $tableRows;
    $this->template->tableTypes = $tableTypes;
    $this->template->archive = $this->archRow;
  }

  protected function createComponentEditForm(): Form
  {
    $form = new Form;
    $form->addText('win', 'Výhry');
    $form->addText('tram', 'Remízy');
    $form->addText('lost', 'Prehry');
    $form->addText('score1', 'Skóre 1');
    $form->addText('score2', 'Skóre 2');
    $form->addText('points', 'Body');
    $form->addSubmit('edit', 'Upraviť');
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
    $form->addProtection(self::CSRF_TOKEN_EXPIRED);
    $form->onSuccess[] = [$this, self::SUBMITTED_RESET_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  public function submittedEditForm(Form $form, $values): void
  {
    $values['counter'] = $values['lost'] + $values['tram'] + $values['win'];
    $this->tableRow->update($values);
    $this->flashMessage('Záznam bol upravený', self::SUCCESS);
    $this->redirect('all');
  }

  public function submittedRemoveForm(): void
  {
    $this->tableRow->delete();
    $this->flashMessage('Záznam bol odstránený', self::SUCCESS);
    $this->redirect('all');
  }

  public function submittedSetVisible(): void
  {
    $this->tableRow->update(array('is_visible' => 1));
    $this->flashMessage('Tabuľka bola pridaná na domovskú stránku', self::SUCCESS);
    $this->redirect('all');
  }

  public function submittedResetForm(): void
  {
    $rows = $this->tablesRepository->getArchived();

    $values = array(
      'counter' => 0,
      'win' => 0,
      'tram' => 0,
      'lost' => 0,
      'score1' => 0,
      'score2' => 0,
      'points' => 0
    );

    foreach ($rows as $row) {
      $row->update($values);
    }

    $this->redirect('all');
  }

  public function formCancelled(): void
  {
    $this->redirect('all');
  }

}
