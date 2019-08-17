<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\FormHelper;
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

class GroupsPresenter extends BasePresenter
{

  /** @var ActiveRow */
  private $groupRow;

  /**
   * GroupsPresenter constructor.
   * @param GroupsRepository $groupsRepository
   * @param LinksRepository $linksRepository
   * @param SponsorsRepository $sponsorsRepository
   * @param TeamsRepository $teamsRepository
   * @param SeasonsGroupsRepository $seasonsGroupsRepository
   * @param SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository
   */
  public function __construct(
    GroupsRepository $groupsRepository,
    LinksRepository $linksRepository,
    SponsorsRepository $sponsorsRepository,
    TeamsRepository $teamsRepository,
    SeasonsGroupsRepository $seasonsGroupsRepository,
    SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository
  )
  {
    parent::__construct($groupsRepository, $linksRepository, $sponsorsRepository, $teamsRepository,
        $seasonsGroupsRepository, $seasonsGroupsTeamsRepository);
    $this->groupsRepository = $groupsRepository;
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
    $this->template->groups = $this->groups;
  }

  /**
   * @param int $id
   */
  public function actionEdit(int $id): void
  {
    $this->userIsLogged();
    $this->groupRow = $this->groupsRepository->findById($id);

    if (!$this->groupRow || !$this->groupRow->is_present) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }

    $this[self::EDIT_FORM]->setDefaults($this->groupRow);
  }

  /**
   * @param int $id
   */
  public function renderEdit(int $id): void
  {
    $this->template->group = $this->groupRow;
  }

  /**
   * @param int $id
   */
  public function actionRemove(int $id): void
  {
    $this->userIsLogged();
    $this->groupRow = $this->groupsRepository->findById($id);

    if (!$this->groupRow || !$this->groupRow->is_present) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }
  }

  /**
   * @param int $id
   */
  public function renderRemove(int $id): void
  {
    $this->template->group = $this->groupRow;
  }

  /**
   * @return Form
   */
  protected function createComponentAddForm(): Form
  {
    $form = new Form;
    $form->addText('label', 'Názov')
          ->setAttribute('placeholder', 'Skupina A')
          ->addRule(Form::FILLED, 'Ešte vyplňte názov')
          ->addRule(Form::MAX_LENGTH, 'Názov môže mať len 50 znakov.', 50);
    $form->addSubmit('save', 'Uložiť');
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    FormHelper::setBootstrapFormRenderer($form);
    $form->onSuccess[] = [$this, self::SUBMITTED_ADD_FORM];
    return $form;
  }

  /**
   * @return Form
   */
  protected function createComponentEditForm(): Form
  {
    $form = new Form;
    $form->addText('label', 'Názov')
        ->setAttribute('placeholder', 'Skupina A')
        ->addRule(Form::FILLED, 'Ešte vyplňte názov')
        ->addRule(Form::MAX_LENGTH, 'Názov môže mať len 50 znakov.', 50);
    $form->addSubmit('save', 'Uložiť')
          ->setAttribute('class', self::BTN_SUCCESS)
          ->onClick[] = [$this, self::SUBMITTED_EDIT_FORM];
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->onClick[] = [$this, self::FORM_CANCELLED];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  /**
   * Component for creating a remove form
   * @return Form
   */
  protected function createComponentRemoveForm(): Form
  {
    $form = new Form;
    $form->addSubmit('save', 'Odstrániť')
          ->setAttribute('class', self::BTN_DANGER)
          ->onClick[] = [$this, self::SUBMITTED_REMOVE_FORM];
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->onClick[] = [$this, self::FORM_CANCELLED];
    $form->addProtection(self::CSRF_TOKEN_EXPIRED);
    $form->onSuccess[] = [$this, self::SUBMITTED_REMOVE_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  public function submittedAddForm(Form $form, ArrayHash $values): void
  {
    $this->userIsLogged();
    $this->groupRow = $this->groupsRepository->getByLabel($values->label);

    if (!$this->groupRow) {
      $this->groupRow = $this->groupsRepository->insert($values);
    }

    $this->seasonsGroupsRepository->insert( array('group_id' => $this->groupRow->id) );

    $this->flashMessage(self::ITEM_ADDED_SUCCESSFULLY, self::SUCCESS);
    $this->redirect('all');
  }

  public function submittedEditForm(SubmitButton $button, ArrayHash $values): void
  {
    $this->userIsLogged();
    $this->groupRow->update($values);
    $this->flashMessage(self::ITEM_UPDATED, self::SUCCESS);
    $this->redirect('all');
  }

  public function submittedRemoveForm(): void
  {
    $this->userIsLogged();
    $seasonGroup = $this->seasonsGroupsRepository->getSeasonGroup($this->groupRow->id);

    if ($seasonGroup) {
      $this->seasonsGroupsRepository->remove($seasonGroup->id);
    }

    $this->groupsRepository->remove($this->groupRow->id);
    $this->flashMessage(self::ITEM_REMOVED_SUCCESSFULLY, self::SUCCESS);
    $this->redirect('all');
  }
}
