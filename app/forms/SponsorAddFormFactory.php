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
class SponsorAddFormFactory
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
   * Creates and renders add sponsor form
   * @param callable $onSuccess
   * @return Form
   */
  public function create(callable $onSuccess): Form
  {
    $form = $this->formFactory->create();
    $form->addText('label', 'Názov*')
        ->setAttribute('placeholder', 'SAHL')
        ->setRequired()
        ->addRule(Form::MAX_LENGTH, 'Názov môže mať najviac 255 znakov.', 255);
    $form->addText('url', 'URL adresa*')
        ->setAttribute('placeholder', 'https://sahl.sk')
        ->setRequired()
        ->addRule(Form::MAX_LENGTH, 'URL adresa môže mať najviac 255 znakov.', 255);
    $form->addUpload('image', 'Obrázok*')
        ->setRequired()
        ->addRule(Form::IMAGE, 'Obrázok môže byť len vo formáte JPEG, PNG alebo GIF')
        ->addRule(Form::MAX_FILE_SIZE, 'Obrázok môže mať najviac 10 MB', 10 * 1024 * 1024);
    $form->addSubmit('save', 'Uložiť');
    FormHelper::setBootstrapFormRenderer($form);

    $form->onSuccess[] = function (Form $form, ArrayHash $values) use ($onSuccess) {
      $onSuccess($form, $values);
    };

    return $form;
  }
}