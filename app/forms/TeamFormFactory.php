<?php

declare(strict_types = 1);

namespace App\Forms;

use App\Helpers\FormHelper;
use App\Model\GroupsRepository;
use Nette\SmartObject;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * @package App\Forms
 */
class TeamFormFactory
{
  use SmartObject;

  /** @var FormFactory */
  private $formFactory;

  /** @var GroupsRepository */
  private $groupsRepository;

  /**
   * @param FormFactory $factory
   * @param GroupsRepository $groupsRepository
   */
  public function __construct(FormFactory $factory, GroupsRepository $groupsRepository)
  {
    $this->formFactory = $factory;
    $this->groupsRepository = $groupsRepository;
  }

  /**
   * Creates and renders sign in form
   * @param callable $onSuccess
   * @return Form
   */
  public function create(callable $onSuccess): Form
  {
    $form = $this->formFactory->create();
    $form->addText('name', 'Názov*')
          ->setAttribute('placeholder', 'SKV Aligators')
          ->setRequired()
          ->addRule(Form::MAX_LENGTH, 'Dĺžka názvu smie byť len 255 znakov.', 255);
    $form->addSubmit('save', 'Uložiť');
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', 'btn btn-large btn-warning')
          ->setAttribute('data-dismiss', 'modal');
    FormHelper::setBootstrapFormRenderer($form);

    $form->onSuccess[] = function (Form $form, ArrayHash $values) use ($onSuccess) {
      $onSuccess($form, $values);
    };

    return $form;
  }
}