<?php

declare(strict_types = 1);

namespace App\Forms;

use App\Helpers\FormHelper;
use Nette\Application\UI\Form;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;

/**
 * Form used to archive the current season and prepare the next one.
 */
class ArchiveFormFactory
{
  use SmartObject;

  /** @var FormFactory */
  private $formFactory;

  public function __construct(FormFactory $factory)
  {
    $this->formFactory = $factory;
  }

  /**
   * @param array<int, string> $teams Season-group-team ID => display label.
   * @param callable $onSuccess
   * @param callable|null $onCancel
   * @return Form
   */
  public function create(array $teams, callable $onSuccess, ?callable $onCancel = null): Form
  {
    $form = $this->formFactory->create();

    $form->addText('label', 'Názov archivovanej sezóny*')
      ->setAttribute('placeholder', '2025/2026')
      ->setRequired()
      ->addRule(Form::MAX_LENGTH, 'Názov môže mať maximálne 255 znakov.', 255);

    $form->addCheckboxList('teams', 'Tímy pre novú sezónu', $teams);

    $form->addSubmit('archive', 'Archivovať a vytvoriť novú sezónu')
      ->setAttribute('class', 'btn btn-large btn-danger');

    $form->addSubmit('cancel', 'Zrušiť')
      ->setAttribute('class', 'btn btn-large btn-default')
      ->setAttribute('data-dismiss', 'modal')
      ->setValidationScope([]);

    $form->addProtection('Platnosť formulára vypršala. Načítajte stránku znova.');
    FormHelper::setBootstrapFormRenderer($form);

    $form->onSuccess[] = function (Form $form, ArrayHash $values) use ($onSuccess, $onCancel): void {
      if ($form['cancel']->isSubmittedBy()) {
        if ($onCancel !== null) {
          $onCancel();
        }
        return;
      }

      $onSuccess($form, $values);
    };

    return $form;
  }
}
