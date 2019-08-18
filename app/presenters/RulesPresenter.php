<?php

namespace App\Presenters;

use App\FormHelper;
use App\Model\GroupsRepository;
use App\Model\LinksRepository;
use App\Model\SeasonsGroupsRepository;
use App\Model\SponsorsRepository;
use App\Model\TeamsRepository;
use App\Model\RulesRepository;
use App\Model\SeasonsGroupsTeamsRepository;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;

/**
 * Class RulesPresenter
 * @package App\Presenters
 */
class RulesPresenter extends BasePresenter {

  /** @var ActiveRow */
  private $ruleRow;

  /** @var ActiveRow */
  private $seasonRow;

  /** @var RulesRepository */
  private $rulesRepository;

  /**
   * RulesPresenter constructor.
   * @param LinksRepository $linksRepository
   * @param SponsorsRepository $sponsorsRepository
   * @param TeamsRepository $teamsRepository
   * @param RulesRepository $rulesRepository
   * @param SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository
   * @param GroupsRepository $groupsRepository
   * @param SeasonsGroupsRepository $seasonsGroupsRepository
   */
  public function __construct(
    LinksRepository $linksRepository,
    SponsorsRepository $sponsorsRepository,
    TeamsRepository $teamsRepository,
    RulesRepository $rulesRepository,
    SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository,
    GroupsRepository $groupsRepository,
    SeasonsGroupsRepository $seasonsGroupsRepository
  )
  {
    parent::__construct($groupsRepository, $linksRepository, $sponsorsRepository, $teamsRepository,
        $seasonsGroupsRepository, $seasonsGroupsTeamsRepository);
    $this->rulesRepository = $rulesRepository;
  }

  /**
   *
   */
  public function actionAll() {
    $this->ruleRow = $this->rulesRepository->getArchived()->order('id DESC')->fetch();

    if (!$this->ruleRow || !$this->ruleRow->is_present) {
      throw new BadRequestException(self::RULE_NOT_FOUND);
    }

    $this->getComponent(self::EDIT_FORM)->setDefaults($this->ruleRow);
  }

  /**
   *
   */
  public function renderAll() {
    $this->template->rule = $this->ruleRow;
  }

  /**
   * @param $id
   */
  public function actionArchView($id) {
    $this->seasonRow = $this->seasonsRepository->findById($id);

    if (!$this->seasonRow || !$this->seasonRow->is_present) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }

    $this->ruleRow = $this->rulesRepository->getArchived($id)->order('id DESC')->fetch();
  }

  /**
   * @param $id
   */
  public function renderArchView($id) {
    $this->template->rule = $this->ruleRow;
    $this->template->season = $this->seasonRow;
  }

  /**
   * @return Form
   */
  protected function createComponentEditForm() {
    $form = new Form;
    $form->addTextArea('content', 'Obsah')
          ->setAttribute('id', 'ckeditor');
    $form->addSubmit('save', 'Uložiť')
          ->setAttribute('class', self::BTN_SUCCESS);
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  /**
   * @param Form $form
   * @param $values
   */
  public function submittedEditForm(Form $form, $values) {
    $this->ruleRow->update($values);
    $this->flashMessage('Pravidlá a smernice boli upravené', self::SUCCESS);
    $this->redirect('all');
  }
}
