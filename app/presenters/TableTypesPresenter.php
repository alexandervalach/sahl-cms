<?php

namespace App\Presenters;

use App\FormHelper;
use App\Forms\TableTypeAddFormFactory;
use App\Forms\RemoveFormFactory;
use App\Model\LinksRepository;
use App\Model\SponsorsRepository;
use App\Model\TeamsRepository;
use App\Model\TableTypesRepository;
use App\Model\SeasonsTeamsRepository;
use Nette\Application\UI\Form;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\ArrayHash;

class TableTypesPresenter extends BasePresenter
{
  const TYPE_NOT_FOUND = 'Type not found';

  /** @var ActiveRow */
  private $tableTypeRow;

  /** @var TableTypesRepository */
  private $tableTypesRepository;

  /** @var TableTypeAddFormFactory */
  private $tableTypeAddFormFactory;

  /** @var RemoveFormFactory */
  private $removeFormFactory;

  public function __construct(
    LinksRepository $linksRepository,
    SponsorsRepository $sponsorsRepository,
    TeamsRepository $teamsRepository,
    TableTypesRepository $tableTypesRepository,
    SeasonsTeamsRepository $seasonsTeamsRepository,
    TableTypeAddFormFactory $tableTypeAddFormFactory,
    RemoveFormFactory $removeFormFactory
  )
  {
    parent::__construct($linksRepository, $sponsorsRepository, $teamsRepository, $seasonsTeamsRepository);
    $this->tableTypesRepository = $tableTypesRepository;
    $this->tableTypeAddFormFactory = $tableTypeAddFormFactory;
    $this->removeFormFactory = $removeFormFactory;
  }

  public function actionAll(): void
  {
    $this->userIsLogged();
  }

  public function renderAll(): void
  {
    $this->template->types = $this->tableTypesRepository->getAll();
  }

  public function actionEdit(int $id): void
  {
    $this->userIsLogged();
    $this->tableTypeRow = $this->tableTypesRepository->findById($id);

    if (!$this->tableTypeRow || !$this->tableTypeRow->is_present) {
      throw new BadRequestException(self::TYPE_NOT_FOUND);
    }

    $this[self::EDIT_FORM]->setDefaults($this->tableTypeRow);
  }

  public function renderEdit(int $id): void
  {
    $this->template->type = $this->tableTypeRow;
  }

  public function actionRemove(int $id): void
  {
    $this->userIsLogged();
    $this->tableTypeRow = $this->tableTypesRepository->findById($id);

    if (!$this->tableTypeRow || !$this->tableTypeRow->is_present) {
      throw new BadRequestException(self::TYPE_NOT_FOUND);
    }
  }

  public function renderRemove(int $id): void
  {
    $this->template->type = $this->tableTypeRow;
  }

  /**
   * Creates add table types form
   * @return Nette\Application\UI\Form
   */
  protected function createComponentAddForm(): Form
  {
    return $this->tableTypeAddFormFactory->create(function (Form $form, ArrayHash $values) {
      $tableType = $this->tableTypesRepository->findByLabel($values->label);

      if (!$tableType) {
        $this->tableTypesRepository->insert($values);
        $this->flashMessage(self::ITEM_ADDED_SUCCESSFULLY, self::SUCCESS);
      } else {
        $this->flashMessage(self::ITEM_ALREADY_EXISTS, self::WARNING);
      }

      $this->redirect('all');
    });
  }


  /**
   * Creates edit table types form
   * @return Nette\Application\UI\Form
   */
  protected function createComponentEditForm(): Form
  {
    $form = new Form;
    $form->addText('label', 'Názov')
          ->addRule(Form::FILLED, 'Ešte treba vyplniť názov')
          ->setAttribute('placeholder', 'Play Off');
    $form->addSubmit('save', 'Uložiť')
          ->setAttribute('class', self::BTN_SUCCESS);
    $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  /**
   * Component for creating a remove form
   * @return Nette\Application\UI\Form
   */
  protected function createComponentRemoveForm(): Form
  {
    return $this->removeFormFactory->create(function () {
      $this->tableTypesRepository->remove($this->tableTypeRow->id);
      $this->flashMessage(self::ITEM_REMOVED_SUCCESSFULLY, self::SUCCESS);
      $this->redirect('all');
    }, function () {
      $this->redirect('all');
    });
  }

  /**
   * Submitting data from add form
   * @param Form $form
   * @param array $values
   */
  public function submittedEditForm(Form $form, ArrayHash $values): void
  {
    $this->tableTypeRow->update($values);
    $this->flashMessage('Záznam bol upravený', self::SUCCESS);
    $this->redirect('all');
  }

  /**
   * @param int $id
   */
  public function actionShow(int $id): void
  {
    $this->userIsLogged();
    $this->tableTypeRow = $this->tableTypesRepository->findById($id);

    if (!$this->tableTypeRow || !$this->tableTypeRow->is_pesent) {
      throw new BadRequestException(self::TYPE_NOT_FOUND);
    }

    $this->submittedShowTable();
  }

  public function submittedShowTable(): void
  {
    /*
    $this->tableTypeRow->update(array('visible' => 1));
    $this->flashMessage('Tabuľka je viditeľná', self::SUCCESS);
    */
    $this->redirect('all');
  }

  /**
   * @param int $id
   */
  public function actionHide(int $id): void
  {
    $this->userIsLogged();
    $this->tableTypeRow = $this->tableTypesRepository->findById($id);

    if (!$this->tableTypeRow || !$this->tableTypeRow->is_pesent) {
      throw new BadRequestException(self::TYPE_NOT_FOUND);
    }

    $this->submittedHideTable();
  }

  public function submittedHideTable(): void
  {
    /*
    $this->tableTypeRow->update(array('visible' => 0));
    $this->flashMessage('Tabuľka je skrytá pre verejnosť', self::SUCCESS);
    */
    $this->redirect('all');
  }
}
