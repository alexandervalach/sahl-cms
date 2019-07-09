<?php

declare(strict_types = 1);

namespace App\Forms;

use App\FormHelper;
use App\Model\PlayerTypesRepository;
use Nette\SmartObject;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * @package App\Forms
 */
class PlayerAddFormFactory
{
  use SmartObject;

  /** @var FormFactory */
  private $formFactory;

  /** @var PlayerTypesRepository */
  private $playerTypesRepository;

  /**
   * @param FormFactory $factory
   */
  public function __construct(FormFactory $factory, PlayerTypesRepository $playerTypesRepository)
  {
    $this->formFactory = $factory;
    $this->playerTypesRepository = $playerTypesRepository;
  }

  /**
   * Creates and renders add player form
   * @param callable $onSuccess
   * @return Form
   */
  public function create(callable $onSuccess): Form
  {
    $types = $this->playerTypesRepository->getTypes();
    $form = $this->formFactory->create();
    $form->addText('name', 'Meno a priezvisko*')
          ->setAttribute('placeholder', 'Zdeno Chára')
          ->setRequired();
    $form->addText('number', 'Číslo*')
          ->setAttribute('placeholder', 14)
          ->setRequired();
    $form->addSelect('player_type_id', 'Typ hráča*', $types);
    $form->addCheckbox('is_transfer', ' Prestupový hráč');
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