<?php

namespace App\Forms;

use App\FormHelper;
use Nette\SmartObject;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * Továrna na přihlašovací formulář.
 * @package App\Forms
 */
class PostFormFactory
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
   * Creates and renders sign in form
   * @param callable $onSuccess
   * @return Form
   */
  public function create(callable $onSuccess)
  {
    $form = $this->formFactory->create();
    $form->addText('title', 'Názov*')
          ->setAttribute('placeholder', 'Novinka SAHL 2018')
          ->setRequired();
    $form->addTextArea('content', 'Obsah')
          ->setAttribute('id', 'ckeditor');
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