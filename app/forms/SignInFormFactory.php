<?php

namespace App\Forms;

use App\FormHelper;
use Nette\SmartObject;
use Nette\Application\UI\Form;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

/**
 * Továrna na přihlašovací formulář.
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
  public function create(callable $onSuccess)
  {
    $form = $this->formFactory->create();
    $form->addText('username', 'Používateľské meno*')
          ->setRequired();
    $form->addPassword('password', 'Heslo*')
          ->setRequired();
    $form->addCheckbox('remember', ' Zapamätať si ma na 7 dní');
    $form->addSubmit('login', 'Prihlásiť');
    // $form->addProtection(self::CSRF_TOKEN_EXPIRED);
    FormHelper::setBootstrapFormRenderer($form);

    $form->onSuccess[] = function (Form $form, ArrayHash $values) use ($onSuccess) {
      if ($values->remember) {
        $this->user->setExpiration(null, 0);
      } else {
        $this->user->setExpiration('30 minutes', 0);
      }
      $onSuccess($form, $values);
    };

    return $form;
  }
}