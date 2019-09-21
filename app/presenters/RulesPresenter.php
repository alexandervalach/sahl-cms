<?php

namespace App\Presenters;

use App\Forms\RuleFormFactory;
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
use Nette\Utils\ArrayHash;

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
   * @var RuleFormFactory
   */
  private $ruleFormFactory;

  /**
   * RulesPresenter constructor.
   * @param LinksRepository $linksRepository
   * @param SponsorsRepository $sponsorsRepository
   * @param TeamsRepository $teamsRepository
   * @param RulesRepository $rulesRepository
   * @param SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository
   * @param GroupsRepository $groupsRepository
   * @param SeasonsGroupsRepository $seasonsGroupsRepository
   * @param RuleFormFactory $ruleFormFactory
   */
  public function __construct(
      LinksRepository $linksRepository,
      SponsorsRepository $sponsorsRepository,
      TeamsRepository $teamsRepository,
      RulesRepository $rulesRepository,
      SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository,
      GroupsRepository $groupsRepository,
      SeasonsGroupsRepository $seasonsGroupsRepository,
      RuleFormFactory $ruleFormFactory
  )
  {
    parent::__construct($groupsRepository, $linksRepository, $sponsorsRepository, $teamsRepository,
        $seasonsGroupsRepository, $seasonsGroupsTeamsRepository);
    $this->rulesRepository = $rulesRepository;
    $this->ruleFormFactory = $ruleFormFactory;
  }

  /**
   *
   */
  public function actionAll(): void
  {
    $this->ruleRow = $this->rulesRepository->getLatest();

    if (!$this->ruleRow) {
      $this->rulesRepository->insert( array('content' => '') );
      // throw new BadRequestException(self::ITEM_NOT_FOUND);
    }

    $this['ruleForm']->setDefaults($this->ruleRow);
  }

  /**
   *
   */
  public function renderAll(): void
  {
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
  protected function createComponentRuleForm(): Form
  {
    return $this->ruleFormFactory->create(function (Form $form, ArrayHash $values) {
      $this->ruleRow->update($values);
      $this->flashMessage(self::ITEM_UPDATED, self::SUCCESS);
      $this->redirect('all');
    });
  }

}
