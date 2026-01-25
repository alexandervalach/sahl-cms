<?php

declare(strict_types = 1);

namespace App\Forms;

use App\Helpers\FormHelper;
use Nette\SmartObject;
use Nette\Application\UI\Form;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

/**
 * Sign In form factory
 * @package App\Forms
 */
class SignInFormFactory
{
  use SmartObject;

  /** @var FormFactory */
  private $formFactory;

  /** @var User */
  private $user;

  /**
   * @param FormFactory $factory
   * @param User $user
   */
  public function __construct(FormFactory $factory, User $user)
  {
    $this->formFactory = $factory;
    $this->user = $user;
  }

  /**
   * Creates and renders sign in form
   * @param callable $onSuccess
   * @return Form
   */
  public function create(callable $onSuccess): Form
  {
    $form = $this->formFactory->create();
    $form->addText('username', 'Používateľské meno*')
          ->setRequired();
    $form->addPassword('password', 'Heslo*')
          ->setRequired();
    $form->addSubmit('login', 'Prihlásiť');
    FormHelper::setBootstrapFormRenderer($form);

    $form->onSuccess[] = function (Form $form, ArrayHash $values) use ($onSuccess) {
      $this->user->setExpiration('60 minutes');
      $onSuccess($form, $values);
    };

    return $form;
  }
}