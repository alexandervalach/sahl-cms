<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;

class SeasonsPresenter extends BasePresenter {

  const ARCHIVE_NOT_FOUND = 'Season not found';
  const ARCHIVE_FORM = 'archiveForm';
  const SUBMITTED_ARCHIVE_FORM = 'submittedArchiveForm';

  /** @var ActiveRow */
  private $seasonRow;

  public function renderAll() {
    $this->template->seasons = $this->seasonsRepository->getAll();
  }

  public function actionView($id) {
    $this->seasonRow = $this->seasonsRepository->findById($id);

    if (!$this->seasonRow || !$this->seasonRow->is_present) {
      throw new BadRequestException(self::ARCHIVE_NOT_FOUND);
    }

    if ($this->user->isLoggedIn()) {
      $this->getComponent(self::EDIT_FORM)->setDefaults($this->seasonRow);
    }
  }

  public function renderView($id) {
    $this->template->season = $this->seasonRow;
  }

  protected function createComponentAddForm() {
    $form = new Form;
    $form->addText('label', 'Názov')
          ->setAttribute('placeholder', 'Archív 2018')
          ->addRule(Form::FILLED, 'Opa, názov ešte nie je vyplnený.');
    $form->addSubmit('save', 'Uložiť');
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, self::SUBMITTED_ADD_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  protected function createComponentEditForm() {
    $form = new Form;
    $form->addText('label', 'Názov')
          ->setAttribute('placeholder', 'Archív 2018')
          ->addRule(Form::FILLED, 'Opa, názov ešte nie je vyplnený.');
    $form->addSubmit('edit', 'Upraviť')
          ->setAttribute('class', self::BTN_SUCCESS);
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  protected function createComponentRemoveForm() {
    $form = new Form;
    $form->addSubmit('remove', 'Odstrániť')
          ->setAttribute('class', self::BTN_DANGER);
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->onSuccess[] = [$this, self::SUBMITTED_REMOVE_FORM];
    $form->addProtection(self::CSRF_TOKEN_EXPIRED);
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  protected function createComponentArchiveForm() {
    $form = new Form;
    $form->addSubmit('archive', 'Archivovať')
          ->setAttribute('class', self::BTN_DEFAULT);
    $form->addSubmit('cancel', 'Zrušiť')
          ->setAttribute('class', self::BTN_WARNING)
          ->setAttribute('data-dismiss', 'modal');
    $form->addProtection(self::CSRF_TOKEN_EXPIRED);
    $form->onSuccess[] = [$this, self::SUBMITTED_ARCHIVE_FORM];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  public function submittedAddForm(Form $form, $values) {
    $this->seasonsRepository->insert($values);
    $this->flashMessage('Archív bol pridaný', self::SUCCESS);
    $this->redirect('all');
  }

  public function submittedEditForm(Form $form, $values) {
    $this->seasonRow->update($values);
    $this->flashMessage('Archív bol upravený', self::SUCCESS);
    $this->redirect('view', $this->seasonRow);
  }

  public function submittedRemoveForm() {
    $this->redirect('all');
  }

  public function submittedArchiveForm() {
    $team_id = array();
    $player_id = array();
    $arch_id = array('season_id' => $this->seasonRow->id);

    $this->roundsRepository->archive($this->seasonRow->id);
    $this->flashMessage('Kolá boli archivované', self::SUCCESS);
    $this->eventsRepository->archive($this->seasonRow->id);
    $this->flashMessage('Rozpis zápasov bol archivovaný', self::SUCCESS);
    $this->rulesRepository->archive($this->seasonRow->id);
    $this->flashMessage('Pravidlá a smernice boli archivované', self::SUCCESS);

    // Vytvoríme duplicitné záznamy tímov s novým archive id
    $teams = $this->teamsRepository->getAsArray($this->seasonRow->id);

    if ($teams != null) {
      foreach ($teams as $team) {
        $data = array(
            'name' => $team->name,
            'image' => $team->image,
            'season_id' => $this->seasonRow->id
        );

        $id = $this->teamsRepository->insert($data);

        if ($id == null) {
            $this->flashMessage('Nastala chyba počas archivácie tímov', self::DANGER);
            $this->redirect('all');
        } else {
            $team_id[$team->id] = $id;
        }
      }
      $this->flashMessage('Tímy boli archivované', self::SUCCESS);
    }

    // Vytvoríme duplicitné záznamy o hráčoch s novým archive_id
    $data = array();
    $players = $this->playersRepository->getAsArray($this->seasonRow->id);
    if ($players != null) {

        foreach ($players as $player) {
            if (isset($team_id[$player->team_id])) {
                $data['team_id'] = $team_id[$player->team_id];
                $data['type_id'] = $player->type_id;
                $data['name'] = $player->name;
                $data['num'] = $player->num;
                $data['born'] = $player->born;
                $data['goals'] = $player->goals;
                $data['trans'] = $player->trans;
                $this->playersRepository->insert($data);
                $player_id[$player->id] = $id;
            } else {
                $this->flashMessage('Nastala chyba počas archivácie hráčov', self::DANGER);
                break;
            }
        }
        $this->flashMessage('Hráči boli archivovaní', self::SUCCESS);
    }

    $tables = $this->tablesRepository->findByValue('archive_id', null);

    if ($tables->count()) {

        $data = array(
            'team_id' => null,
            'archive_id' => $this->archiveRow->id
        );

        foreach ($tables as $table) {
            if (isset($team_id[$table->team_id])) {
                $data['team_id'] = $team_id[$table->team_id];
                $table->update($data);
            } else {
                $this->flashMessage('Nastala chyba počas archivácie tabuliek', self::DANGER);
                break;
            }
        }

        $this->flashMessage('Tabuľky boli archivované', self::SUCCESS);
    }

    $puns = $this->punishmentsRepository->findByValue('archive_id', null);

    if ($puns->count()) {

        foreach ($puns as $pun) {

            $data = array(
                'player_id' => null,
                'archive_id' => $this->archiveRow->id
            );

            if (isset($player_id[$pun->player_id])) {
                $data['player_id'] = $player_id[$pun->player_id];
                $pun->update($data);
            } else {
                $this->flashMessage('Nastala chyba počas archivácie trestov hráčov', self::DANGER);
                break;
            }
        }

        $this->flashMessage('Tresty boli archivované', self::SUCCESS);
    }

    $fights = $this->fightsRepository->findByValue('archive_id', null);

    if ($fights->count()) {

        $data = array(
            'team1_id' => null,
            'team2_id' => null,
            'archive_id' => $this->archiveRow->id
        );

        foreach ($fights as $fight) {
            if (isset($team_id[$fight->team1_id]) && isset($team_id[$fight->team2_id])) {
                $data['team1_id'] = $team_id[$fight->team1_id];
                $data['team2_id'] = $team_id[$fight->team2_id];
                $fight->update($data);
            } else {
                $this->flashMessage('Počas archivácie výsledkov zápasov nastala chyba', self::DANGER);
                break;
            }
        }

        $this->flashMessage('Zápasy boli archivované', self::SUCCESS);
    }

    $goals = $this->goalsRepository->findByValue('archive_id', null);

    if ($goals->count()) {

        $data = array(
            'player_id' => null,
            'archive_id' => $this->archiveRow->id
        );

        foreach ($goals as $goal) {
            if (isset($player_id[$goal->player_id])) {
                $data['player_id'] = $player_id[$goal->player_id];
                $id = $goal->update($data);
            } else {
                $this->flashMessage('Nastala chyba počas archivácie gólov', self::DANGER);
                break;
            }
        }

        $this->flashMessage('Góly boli archivované', self::SUCCESS);
    }

    $this->redirect('view', $this->archiveRow);
  }

}
