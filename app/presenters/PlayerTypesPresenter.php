<?php

namespace App\Presenters;

use App\FormHelper;
use App\Forms\PlayerTypeAddFormFactory;
use App\Forms\PlayerTypeEditFormFactory;
use App\Forms\RemoveFormFactory;
use App\Model\LinksRepository;
use App\Model\SponsorsRepository;
use App\Model\TeamsRepository;
use App\Model\PlayerTypesRepository;
use App\Model\SeasonsTeamsRepository;
use Nette\Application\UI\Form;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\ArrayHash;

class PlayerTypesPresenter extends BasePresenter
{
  /** @var PlayerTypesRepository */
  private $playerTypesRepository;

  /** @var PlayerTypeAddFormFactory */
  private $playerTypeAddFormFactory;

  /** @var PlayerTypeEditFormFactory */
  private $playerTypeEditFormFactory;

  /** @var RemoveFormFactory */
  private $removeFormFactory;

  /** @var ActiveRow */
  private $playerTypeRow;

  public function __construct(
    LinksRepository $linksRepository,
    SponsorsRepository $sponsorsRepository,
    TeamsRepository $teamsRepository,
    PlayerTypesRepository $playerTypesRepository,
    SeasonsTeamsRepository $seasonsTeamsRepository,
    PlayerTypeAddFormFactory $playerTypeAddFormFactory,
    PlayerTypeEditFormFactory $playerTypeEditFormFactory,
    RemoveFormFactory $removeFormFactory
  )
  {
    parent::__construct($linksRepository, $sponsorsRepository, $teamsRepository, $seasonsTeamsRepository);
    $this->playerTypesRepository = $playerTypesRepository;
    $this->playerTypeAddFormFactory = $playerTypeAddFormFactory;
    $this->playerTypeEditFormFactory = $playerTypeEditFormFactory;
    $this->removeFormFactory = $removeFormFactory;
  }

  /**
   * Action all
   */
  public function actionAll(): void
  {
    $this->userIsLogged();
  }

  /**
   * Render all
   */
  public function renderAll(): void
  {
    $this->template->types = $this->playerTypesRepository->getAll();
  }

  /**
   * @param int $id
   */
  public function actionEdit(int $id): void
  {
    $this->userIsLogged();
    $this->playerTypeRow = $this->playerTypesRepository->findById($id);

    if (!$this->playerTypeRow || !$this->playerTypeRow->is_present) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }

    $this[self::EDIT_FORM]->setDefaults($this->playerTypeRow);
  }

  /**
   * @param int $id
   */
  public function renderEdit(int $id): void
  {
    $this->template->type = $this->playerTypeRow;
  }

  /**
   * @param int $id
   */
  public function actionRemove(int $id): void
  {
    $this->userIsLogged();
    $this->playerTypeRow = $this->playerTypesRepository->findById($id);

    if (!$this->playerTypeRow || !$this->playerTypeRow->is_present) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }
  }

  /**
   * @param int $id
   */
  public function renderRemove(int $id): void
  {
    $this->template->type = $this->playerTypeRow;
  }

  /**
   * Generates new add form
   * @return Nette\Application\UI\Form
   */
  protected function createComponentAddForm(): Form
  {
    return $this->playerTypeAddFormFactory->create(function (Form $form, ArrayHash $values) {
      $this->userIsLogged();
      $this->playerTypesRepository->insert($values);
      $this->flashMessage(self::ITEM_ADDED_SUCCESSFULLY, self::SUCCESS);
      $this->redirect('all');
    });
  }

  /**
   * Generates new edit form
   * @return Nette\Application\UI\Form
   */
  protected function createComponentEditForm(): Form
  {
    return $this->playerTypeEditFormFactory->create(function (SubmitButton $button, ArrayHash $values) {
      $this->userIsLogged();
      $this->playerTypeRow->update($values);
      $this->flashMessage(self::CHANGES_SAVED_SUCCESSFULLY, self::SUCCESS);
      $this->redirect('all');
    }, function () {
      $this->formCancelled();
    });
  }

  /**
   * Generates new remove form
   * @return Nette\Application\UI\Form
   */
  protected function createComponentRemoveForm(): Form
  {
    return $this->removeFormFactory->create(function () {
      $this->userIsLogged();
      $this->playerTypesRepository->remove($this->playerTypeRow->id);
      $this->flashMessage(self::ITEM_REMOVED_SUCCESSFULLY, self::SUCCESS);
      $this->redirect('all');
    }, function () {
      $this->formCancelled();
    });
  }
}
