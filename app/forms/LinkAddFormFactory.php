<?php

declare(strict_types = 1);

namespace App\Forms;

use App\Helpers\FormHelper;
use Nette\SmartObject;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * @package App\Forms
 */
class LinkAddFormFactory
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
   * Creates and renders add link form
   * @param callable $onSuccess
   * @return Form
   */
  public function create(callable $onSuccess): Form
  {
    $form = $this->formFactory->create();
    $form->addText('label', 'Názov*')
        ->setRequired()
        ->addRule(Form::MAX_LENGTH, 'Názov môže mať maximálne 255 znakov', 255);
    $form->addText('url', 'URL adresa')
        ->addRule(Form::MAX_LENGTH, 'URL adresa môže mať maximálne 255 znakov', 255);
    $form->addSubmit('save', 'Uložiť');
    FormHelper::setBootstrapFormRenderer($form);

    $form->onSuccess[] = function (Form $form, ArrayHash $values) use ($onSuccess) {
      $onSuccess($form, $values);
    };

    return $form;
  }
}