<?php

declare(strict_types = 1);

namespace App\Forms;

use App\Helpers\FormHelper;
use App\Model\TableTypesRepository;
use App\Model\TeamsRepository;
use Nette\SmartObject;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * @package App\Forms
 */
class FightEditFormFactory
{
  use SmartObject;

  /** @var FormFactory */
  private $formFactory;

  /** @var TableTypesRepository */
  private $tableTypesRepository;

  /**
   * @var TeamsRepository
   */
  private $teamsRepository;

  /**
   * @param FormFactory $factory
   * @param TableTypesRepository $tableTypesRepository
   * @param TeamsRepository $teamsRepository
   */
  public function __construct(
      FormFactory $factory,
      TableTypesRepository $tableTypesRepository,
      TeamsRepository $teamsRepository)
  {
    $this->formFactory = $factory;
    $this->tableTypesRepository = $tableTypesRepository;
    $this->teamsRepository = $teamsRepository;
  }

  /**
   * Creates and renders teams form
   * @param callable $onSuccess
   * @param callable $onCancel
   * @return Form
   */
  public function create(callable $onSuccess, callable $onCancel): Form
  {
    $teams = $this->teamsRepository->fetchForSeason();
    $tableTypes = $this->tableTypesRepository->fetchForSeason();

    $form = $this->formFactory->create();
    $form->addSelect('team1_id', 'Tím 1*', $teams);
    $form->addText('score1', 'Skóre tímu 1*')
          ->setHtmlType('number')
          ->setAttribute('placeholder', '1');
    $form->addSelect('team2_id', 'Tím 2*', $teams);
    $form->addText('score2', 'Skóre tímu 2*')
          ->setHtmlType('number')
          ->setAttribute('placeholder', '0');
    $form->addSelect('table_type_id', 'Tabuľka*', $tableTypes);
    $save = $form->addSubmit('save', 'Uložiť');
    $cancel = $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', 'btn btn-large btn-warning');
    FormHelper::setBootstrapFormRenderer($form);

    $save->onClick[] = function (SubmitButton $button, ArrayHash $values) use ($onSuccess) {
      $onSuccess($button, $values);
    };

    $cancel->onClick[] = function () use ($onCancel) {
      $onCancel();
    };

    return $form;
  }
}