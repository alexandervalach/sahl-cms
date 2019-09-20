<?php

declare(strict_types = 1);

namespace App\Forms;

use App\Helpers\FormHelper;
use Nette\SmartObject;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Forms\Controls\SubmitButton;

/**
 * @package App\Forms
 */
class TableTypeEditFormFactory
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
   * Creates and renders table type edit form
   * @param callable $onSave
   * @param callable $onCancel
   * @return Form
   */
  public function create(callable $onSave, callable $onCancel): Form
  {
    $form = $this->formFactory->create();
    $form->addText('label', 'Názov*')
          ->setAttribute('placeholder', 'Play Off')
          ->addRule(Form::MAX_LENGTH, 'Názov môže mať len 255 znakov.', 255)
          ->setRequired();
    $save = $form->addSubmit('save', 'Uložiť');
    $cancel = $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', 'btn btn-large btn-warning');
    FormHelper::setBootstrapFormRenderer($form);

    $save->onClick[] = function (SubmitButton $button, ArrayHash $values) use ($onSave) {
      $onSave($button, $values);
    };

    $cancel->onClick[] = function () use ($onCancel) {
      $onCancel();
    };

    return $form;
  }
}