<?php

declare(strict_types = 1);

namespace App\Forms;

use App\FormHelper;
use App\Model\PlayersRepository;
use Nette\SmartObject;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

/**
 * @package App\Forms
 */
class PunishmentAddFormFactory
{
  use SmartObject;

  /** @var FormFactory */
  private $formFactory;

  /**
   * @var PlayersRepository
   */
  private $playersRepository;

  /**
   * @param FormFactory $factory
   * @param PlayersRepository $playersRepository
   */
  public function __construct(FormFactory $factory, PlayersRepository $playersRepository)
  {
    $this->formFactory = $factory;
    $this->playersRepository = $playersRepository;
  }

  /**
   * Creates and renders sign in form
   * @param callable $onSuccess
   * @param int $seasonGroupId
   * @return Form
   */
  public function create(callable $onSuccess, int $seasonGroupId): Form
  {
    $players = $this->playersRepository->fetchForSeasonGroup($seasonGroupId);
    $form = $this->formFactory->create();
    $form->addSelect('player_id', 'Hráč*', $players)
        ->setRequired();
    $form->addText('text', 'Dôvod')
        ->setAttribute('placeholder', 'Nešportové správanie')
        ->addRule(Form::MAX_LENGTH, 'Dôvod môže mať najviac 255 znakov.', 255);
    $form->addText('round', 'Stop na kolo')
        ->setAttribute('placeholder', '3. kolo')
        ->addRule(Form::MAX_LENGTH, 'Políčko \'Stop na kolo\' môže mať najviac 255 znakov.', 255);
    $form->addCheckbox('condition', ' Podmienka');
    $form->addSubmit('save', 'Uložiť');
    $form->addSubmit('cancel', 'Zrušiť')
        ->setAttribute('class', 'btn btn-warning btn-large')
        ->setAttribute('data-dismiss', 'modal');
    FormHelper::setBootstrapFormRenderer($form);

    $form->onSuccess[] = function (Form $form, ArrayHash $values) use ($onSuccess) {
      $onSuccess($form, $values);
    };

    return $form;
  }
}