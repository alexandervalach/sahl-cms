<?php

declare(strict_types=1);

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;

class PunishmentsPresenter extends BasePresenter {

  /** @var ActiveRow */
  private $punishmentRow;

  /** @var ActiveRow */
  private $seasonRow;

  /** @var array */
  private $punishments;

  public function actionAll(): void
  {
    $this->punishments = array();
  }

  public function renderAll(): void
  {
    $this->template->punishments = $this->punishmentsRepository->getForSeason();
  }

  public function actionEdit($id): void
  {
    $this->userIsLogged();
    $this->punishmentRow = $this->punishmentsRepository->findById($id);

    if (!$this->punishmentRow || !$this->punishmentRow->is_present) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }

    $this->getComponent(self::EDIT_FORM)->setDefaults($this->punishmentRow);
  }

  public function renderEdit($id): void
  {
    $this->template->player = $this->punishmentRow->ref('players', 'player_id');
  }

  public function actionRemove($id): void
  {
    $this->userIsLogged();
    $this->punishmentRow = $this->punishmentsRepository->findById($id);
  }

  public function renderRemove($id): void
  {
    $this->template->punishment = $this->punishmentRow;

    if (!$this->punishmentRow || !$this->punishmentRow->is_present) {
      throw new BadRequestException(self::ITEM_NOT_FOUND);
    }
  }

  public function actionArchAll($id) {
    $this->seasonRow = $this->seasonsRepository->findById($id);
  }

  public function renderArchAll($id) {
    $this->template->season = $this->seasonRow;
    $this->template->punishments = $this->punishmentsRepository->getArchived($id);
  }

  /**
   * @return Nette\Application\UI\Form;
   */
  protected function createComponentEditForm() {
    $form = new Form;
    $form->addText('text', 'Dôvod')
          ->setAttribute('placeholder', 'Nešportové správanie');
    $form->addText('round', 'Kolá')
          ->setAttribute('placeholder', '3. kolo');
    $form->addCheckbox('condition', ' Podmienka');
    $form->addSubmit('save', 'Uložiť');
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  protected function createComponentAddForm() {
    $players = $this->playersRepository->getNonEmptyPlayers();
    $form = new Form;
    $form->addSelect('player_id', 'Hráč', $players);
    $form->addText('text', 'Dôvod')
          ->setAttribute('placeholder', 'Nešportové správanie');
    $form->addText('round', 'Stop na kolo')
          ->setAttribute('placeholder', '3. kolo');
    $form->addCheckbox('condition', ' Podmienka');
    $form->addSubmit('save', 'Uložiť');
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, self::SUBMITTED_ADD_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  public function submittedEditForm(Form $form, $values) {
    $this->punishmentRow->update($values);
    $this->flashMessage('Trest bol upravený', self::SUCCESS);
    $this->redirect('all');
  }

  public function submittedAddForm(Form $form, $values) {
    $this->punishmentsRepository->insert($values);
    $this->flashMessage('Trest bol pridaný', self::SUCCESS);
    $this->redirect('all');
  }

  public function submittedRemoveForm() {
    $this->punishmentsRepo->delete();
    $this->flashMessage('Trest bol odstránený', self::SUCCESS);
    $this->redirect('all');
  }

  public function formCancelled() {
      $this->redirect('all');
  }

}
