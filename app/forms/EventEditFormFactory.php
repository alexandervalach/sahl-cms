<?php

namespace App\Forms;

use App\FormHelper;
use Nette\SmartObject;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Forms\Controls\SubmitButton;

/**
 * @package App\Forms
 */
class EventEditFormFactory
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
   * Creates and renders event edit form
   * @param callable $onSuccess
   * @param callable $onCancel
   * @return Form
   */
  public function create(callable $onSuccess, callable $onCancel)
  {
    $form = $this->formFactory->create();
    $form->addTextArea('content', 'Obsah*')
        ->setAttribute('id', 'ckeditor');
    $save = $form->addSubmit('save', 'Uložiť')
            ->setAttribute('class', 'btn btn-large btn-success');
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