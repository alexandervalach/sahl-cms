<?php

declare(strict_types = 1);

namespace App\Forms;

use Nette\Application\UI\Form;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;

class AttendanceFormFactory
{
  use SmartObject;

  /** @var FormFactory */
  private $formFactory;

  public function __construct(FormFactory $formFactory)
  {
    $this->formFactory = $formFactory;
  }

  /**
   * @param array $team1Players
   * @param array $team2Players
   * @param callable $onSuccess
   * @param callable $onCancel
   * @return Form
   */
  public function create(
      array $team1Players,
      array $team2Players,
      callable $onSuccess,
      callable $onCancel): Form
  {
    $form = $this->formFactory->create();
    $form->addProtection('Platnosť formulára vypršala. Obnovte stránku a skúste to znova.');
    $form->addCheckboxList('team1_players', '', $team1Players);
    $form->addCheckboxList('team2_players', '', $team2Players);
    $form->addSubmit('save', 'Uložiť dochádzku')
      ->setAttribute('class', 'btn btn-primary');
    $form->addSubmit('cancel', 'Zrušiť')
      ->setValidationScope([])
      ->setAttribute('class', 'btn btn-warning');

    $form->onSuccess[] = function (Form $form, ArrayHash $values) use ($onSuccess, $onCancel): void {
      if ($form['cancel']->isSubmittedBy()) {
        $onCancel();
        return;
      }

      $onSuccess($form, $values);
    };

    return $form;
  }
}
