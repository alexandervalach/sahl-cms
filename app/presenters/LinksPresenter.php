<?php

namespace App\Presenters;

use App\Forms\LinkAddFormFactory;
use App\Forms\LinkEditFormFactory;
use App\Forms\RemoveFormFactory;
use App\Model\GroupsRepository;
use App\Model\LinksRepository;
use App\Model\SeasonsGroupsRepository;
use App\Model\SeasonsGroupsTeamsRepository;
use App\Model\SponsorsRepository;
use App\Model\TeamsRepository;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\ArrayHash;

class LinksPresenter extends BasePresenter
{
  const LINK_NOT_FOUND = 'Link not found';

  /** @var ActiveRow */
  private $linkRow;

  /** @var LinkAddFormFactory */
  private $linkAddFormFactory;

  /** @var LinkEditFormFactory */
  private $linkEditFormFactory;

  /**
   * @var RemoveFormFactory
   */
  private $removeFormFactory;

  public function __construct(
      GroupsRepository $groupsRepository,
      LinksRepository $linksRepository,
      SponsorsRepository $sponsorsRepository,
      TeamsRepository $teamsRepository,
      SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository,
      SeasonsGroupsRepository $seasonsGroupsRepository,
      LinkAddFormFactory $linkAddFormFactory,
      LinkEditFormFactory $linkEditFormFactory,
      RemoveFormFactory $removeFormFactory
  )
  {
    parent::__construct($groupsRepository, $linksRepository, $sponsorsRepository, $teamsRepository,
        $seasonsGroupsRepository, $seasonsGroupsTeamsRepository);
    $this->linkAddFormFactory = $linkAddFormFactory;
    $this->linkEditFormFactory = $linkEditFormFactory;
    $this->removeFormFactory = $removeFormFactory;
  }


  public function actionAll(): void
  {
    $this->userIsLogged();
  }

  public function renderAll(): void
  {
    $this->template->links = $this->linksRepository->getAll();
  }

  public function actionRemove($id): void
  {
    $this->userIsLogged();
    $this->linkRow = $this->linksRepository->findById($id);
    if (!$this->linkRow) {
      throw new BadRequestException(self::LINK_NOT_FOUND);
    }
  }

  public function renderRemove($id): void
  {
    $this->template->link = $this->linkRow;
  }

  public function actionEdit($id): void
  {
    $this->userIsLogged();
    $this->linkRow = $this->linksRepository->findById($id);
    if (!$this->linkRow) {
      throw new BadRequestException(self::LINK_NOT_FOUND);
    }
    $this[self::EDIT_FORM]->setDefaults($this->linkRow);
  }

  public function renderEdit($id): void
  {
    $this->template->link = $this->linkRow;
  }

  /**
   * Creates a component for rendering add form
   * @return Form
   */
  protected function createComponentAddForm(): Form
  {
    return $this->linkAddFormFactory->create( function (Form $form, ArrayHash $values) {
      $this->userIsLogged();
      $this->linksRepository->insert($values);
      $this->flashMessage(self::ITEM_ADDED_SUCCESSFULLY, self::SUCCESS);
      $this->redirect('all');
    });
  }

  /**
   * Creates a component for rendering edit form
   * @return Form
   */
  protected function createComponentEditForm(): Form
  {
    return $this->linkEditFormFactory->create( function (SubmitButton $button, ArrayHash $values) {
      $this->userIsLogged();
      $this->linkRow->update($values);
      $this->flashMessage(self::ITEM_UPDATED, self::SUCCESS);
      $this->redirect('all');
    }, function () {
      $this->redirect('all');
    });
  }

  /**
   * Creates a component for rendering remove form
   * @return Form
   */
  protected function createComponentRemoveForm(): Form
  {
    return $this->removeFormFactory->create( function () {
      $this->userIsLogged();
      $this->linksRepository->remove($this->linkRow->id);
      $this->flashMessage(self::ITEM_REMOVED_SUCCESSFULLY, self::SUCCESS);
      $this->redirect('all');
    }, function () {
      $this->redirect('all');
    });
  }

}
