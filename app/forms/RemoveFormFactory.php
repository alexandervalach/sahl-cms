<?php

namespace App\Forms;

use App\FormHelper;
use Nette\SmartObject;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * @package App\Forms
 */
class RemoveFormFactory
{
  use SmartObject;

  /** @var FormFactory */
  private $formFactory;

  /**
   * @param FormFactory $factory
   */
  public function __construct(FormFactory $factory)
  {
    $this->formFactory = $factory;
  }

  /**
   * Creates and renders remove form
   * @param callable $onRemove
   * @param callable $onCancel
   * @return Form
   */
  public function create(callable $onRemove, callable $onCancel)
  {
    $form = $this->formFactory->create();
    $remove = $form->addSubmit('remove', 'Odstrániť')
          ->setAttribute('class', 'btn btn-large btn-danger');
    $cancel = $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', 'btn btn-large btn-warning');
    FormHelper::setBootstrapFormRenderer($form);

    $remove->onClick[] = function () use ($onRemove) {
      $onRemove();
    };

    $cancel->onClick[] = function () use ($onCancel) {
      $onCancel();
    };

    return $form;
  }
}