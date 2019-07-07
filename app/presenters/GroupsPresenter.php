<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\FormHelper;
use App\Model\LinksRepository;
use App\Model\SponsorsRepository;
use App\Model\TeamsRepository;
use App\Model\GroupsRepository;
use App\Model\SeasonsTeamsRepository;
use Nette\Application\UI\Form;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\ArrayHash;

class GroupsPresenter extends BasePresenter
{
  const TYPE_NOT_FOUND = 'Player type not found';

  /** @var ActiveRow */
  private $groupRow;

  /** @var GroupsRepository */
  private $groupsRepository;

  public function __construct(
    LinksRepository $linksRepository,
    SponsorsRepository $sponsorsRepository,
    TeamsRepository $teamsRepository,
    GroupsRepository $groupsRepository,
    SeasonsTeamsRepository $seasonsTeamsRepository
  )
  {
    parent::__construct($linksRepository, $sponsorsRepository, $teamsRepository, $seasonsTeamsRepository);
    $this->groupsRepository = $groupsRepository;
  }

  public function actionAll(): void
  {
    $this->userIsLogged();
  }

  public function renderAll(): void
  {
    $this->template->groups = $this->groupsRepository->getAll();
  }

  public function actionEdit(int $id): void
  {
    $this->userIsLogged();
    $this->groupRow = $this->groupsRepository->findById($id);

    if (!$this->groupRow || !$this->groupRow->is_present) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }

    $this->getComponent(self::EDIT_FORM)->setDefaults($this->groupRow);
  }

  public function renderEdit(int $id): void
  {
    $this->template->group = $this->groupRow;
  }

  public function actionRemove(int $id): void
  {
    $this->userIsLogged();
    $this->groupRow = $this->groupsRepository->findById($id);

    if (!$this->groupRow || !$this->groupRow->is_present) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }
  }

  public function renderRemove(int $id): void
  {
    $this->template->group = $this->groupRow;
  }

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
   * @return Nette\Application\UI\Form
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
    $this->groupsRepository->insert($values);
    $this->flashMessage('Skupina bol pridaná', self::SUCCESS);
    $this->redirect('all');
  }

  public function submittedEditForm(SubmitButton $button, ArrayHash $values): void
  {
    $this->userIsLogged();
    $this->groupRow->update($values);
    $this->flashMessage('Skupina bola upravená', self::SUCCESS);
    $this->redirect('all');
  }

  public function submittedRemoveForm(): void
  {
    $this->userIsLogged();
    $this->groupsRepository->remove($this->groupRow->id);
    $this->flashMessage('Skupina bola odstránená', self::SUCCESS);
    $this->redirect('all');
  }
}
