<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;

class FightsPresenter extends BasePresenter {

    const FIGHT_NOT_FOUND = 'Fight not found';

    /** @var ActiveRow */
    private $roundRow;

    /** @var ActiveRow */
    private $fightRow;

    /** @var ActiveRow */
    private $archRow;

    /** @var ActiveRow */
    private $team1;

    /** @var ActiveRow */
    private $team2;

    public function actionEdit($id) {
        $this->userIsLogged();
        $this->fightRow = $this->fightsRepository->findById($id);
        $this->roundRow = $this->fightRow->ref('rounds', 'round_id');
    }

    public function renderEdit($id) {
        if (!$this->fightRow) {
            throw new BadRequestException(self::FIGHT_NOT_FOUND);
        }
        $this->template->round = $this->roundRow;
        $this->getComponent('editForm')->setDefaults($this->fightRow);
    }

    public function actionRemove($id) {
        $this->userIsLogged();
        $this->fightRow = $this->fightsRepository->findById($id);
        $this->roundRow = $this->fightRow->ref('rounds', 'round_id');
    }

    public function renderRemove($id) {
        if (!$this->fightRow) {
            throw new BadRequestException(self::FIGHT_NOT_FOUND);
        }
        $this->template->fight = $this->fightRow;
    }

    public function actionArchView($id, $param) {
        $this->roundRow = $this->roundsRepository->findById($param);
        $this->archRow = $this->archiveRepository->findById($id);
    }

    public function renderArchView($id, $param) {
        if (!$this->roundRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->fights = $this->fightsRepository
                ->findByValue('round_id', $param)
                ->where('archive_id', $id);
        $this->template->round = $this->roundRow;
        $this->template->archive = $this->roundRow->ref('archive', 'archive_id');
    }

    protected function createComponentAddForm() {
        $teams = $this->teamsRepository->getTeams();
        $form = new Form;
        $form->addSelect('team1_id', 'Tím 1', $teams);
        $form->addText('score1', 'Skóre 1');
        $form->addSelect('team2_id', 'Tím 2', $teams);
        $form->addText('score2', 'Skóre 2');
        $form->addCheckbox('type', ' Označiť zápas ako Play Off');
        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = [$this, self::SUBMITTED_ADD_FORM];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditForm() {
        $teams = $this->teamsRepository->getTeams();
        $form = new Form;
        $form->addSelect('team1_id', 'Tím 1', $teams);
        $form->addText('score1', 'Skóre 1');
        $form->addSelect('team2_id', 'Tím 2', $teams);
        $form->addText('score2', 'Skóre 2');
        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddForm(Form $form, $values) {
        if ($values['team1_id'] == $values['team2_id']) {
            $form->addError('Zvoľte dva rozdielne tímy.');
            return false;
        }
        $values['round_id'] = $this->roundRow;

        if ($values['type']) {
            $type = 1;
        } else {
            $type = 2;
        }
        unset($values['type']);

        $fight = $this->fightsRepository->insert($values);
        $this->updateTableRows($values, $type);
        $this->updateTablePoints($values, $type);
        $this->updateTableGoals($values, $type);
        $this->flashMessage('Zápas bol pridaný', 'success');
        $this->redirect('Rounds:view', $fight->ref('rounds', 'round_id'));
    }

    public function submittedEditForm(Form $form, $values) {
        if ($values->team1_id == $values->team2_id) {
            $form->addError('Zvoľte dva rozdielne tímy.');
            return false;
        }
        $this->fightRow->update($values);
        $this->flashMessage('Zápas bol upravený', 'success');
        $this->redirect('Rounds:view', $this->roundRow);
    }

    public function submittedRemoveForm() {
        $this->fightRow->delete();
        $this->flashMessage('Zápas bol odstránený', 'success');
        $this->redirect('Rounds:view', $this->roundRow);
    }

    public function updateTableRows($values, $type, $value = 1) {
        $state1 = 'tram';
        $state2 = 'tram';

        if ($values['score1'] > $values['score2']) {
            $state1 = 'win';
            $state2 = 'lost';
        } elseif ($values['score1'] < $values['score2']) {
            $state1 = 'lost';
            $state2 = 'win';
        }
        $this->tablesRepository->incTabVal($values['team1_id'], $type, $state1, $value);
        $this->tablesRepository->incTabVal($values['team2_id'], $type, $state2, $value);
        $this->tablesRepository->updateFights($values['team1_id'], $type);
        $this->tablesRepository->updateFights($values['team2_id'], $type);
    }

    public function updateTablePoints($values, $type, $column = 'points') {
        if ($values['score1'] > $values['score2']) {
            $this->tablesRepository->incTabVal($values['team1_id'], $type, $column, 2);
            $this->tablesRepository->incTabVal($values['team2_id'], $type, $column, 0);
        } elseif ($values['score1'] < $values['score2']) {
            $this->tablesRepository->incTabVal($values['team2_id'], $type, $column, 2);
            $this->tablesRepository->incTabVal($values['team1_id'], $type, $column, 0);
        } else {
            $this->tablesRepository->incTabVal($values['team2_id'], $type, $column, 1);
            $this->tablesRepository->incTabVal($values['team1_id'], $type, $column, 1);
        }
    }

    public function updateTableGoals($values, $type) {
        $this->tablesRepository->incTabVal($values['team1_id'], $type, 'score1', $values['score1']);
        $this->tablesRepository->incTabVal($values['team1_id'], $type, 'score2', $values['score2']);
        $this->tablesRepository->incTabVal($values['team2_id'], $type, 'score1', $values['score2']);
        $this->tablesRepository->incTabVal($values['team2_id'], $type, 'score2', $values['score1']);
    }

    public function formCancelled() {
        $this->redirect('Rounds:view', $this->roundRow);
    }

}
