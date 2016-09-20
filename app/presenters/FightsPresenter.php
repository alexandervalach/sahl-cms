<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;

class FightsPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $roundRow;

    /** @var ActiveRow */
    private $fightRow;

    /** @var string */
    private $error = "Match not found!";

    /** @var ActiveRow */
    private $team1;

    /** @var ActiveRow */
    private $team2;

    public function actionAll($id) {
        $this->roundRow = $this->roundsRepository->findById($id);
    }

    public function renderAll($id) {
        if (!$this->roundRow) {
            throw new BadRequestException("Round not found.");
        }
        $fights = $this->roundRow->related('fights');
        $this->template->round = $this->roundRow;
        $this->template->fights = $fights;
        foreach ($fights as $fight) {
            $goals[] = $fight->realted('goals')->order('goals DESC');
        }
        $this->template->goals = $goals;
    }

    public function actionAdd($id) {
        $this->userIsLogged();
        $this->roundRow = $this->roundsRepository->findById($id);
    }

    public function renderAdd($id) {
        if (!$this->roundRow) {
            throw new BadRequestException("Round not found!");
        }
        $this->template->round = $this->roundRow;
        $this->getComponent('addFightForm');
    }

    public function actionEdit($id) {
        $this->userIsLogged();
        $this->fightRow = $this->fightsRepository->findById($id);
    }

    public function renderEdit($id) {
        if (!$this->fightRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->round = $this->fightRow->ref('rounds', 'round_id');
        $this->getComponent('editFightForm')->setDefaults($this->fightRow);
    }

    public function actionDelete($id) {
        $this->userIsLogged();
        $this->fightRow = $this->fightsRepository->findById($id);
    }

    public function renderDelete($id) {
        if (!$this->fightRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->fight = $this->fightRow;
    }

    public function actionArchView($id, $param) {
        $this->roundRow = $this->roundsRepository->findById($param);
    }

    public function renderArchView($id, $param) {
        if (!$this->roundRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->fights = $this->fightsRepository->findByValue('round_id', $param)->where('archive_id', $id);
        $this->template->round = $this->roundRow;
        $this->template->archive = $this->roundRow->ref('archive', 'archive_id');
    }

    public function actionEditThird($id) {
        $this->userIsLogged();
        $this->fightRow = $this->fightsRepository->findById($id);
        $this->roundRow = $this->fightsRepository->getForFight($this->fightRow, 'round_id', 'rounds');
        $this->team1 = $this->fightsRepository->getForFight($this->fightRow, 'team1_id');
        $this->team2 = $this->fightsRepository->getForFight($this->fightRow, 'team2_id');
    }

    public function renderEditThird($id) {
        if (!$this->fightRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->fight = $this->fightRow;
        $this->template->round = $this->roundRow;
        $this->getComponent('editThirdForm')->setDefaults($this->fightRow);
    }

    protected function createComponentAddFightForm() {
        $form = new Form;
        $teams = $this->teamsRepository->getTeams();

        $form->addSelect('team1_id', 'Tím 1', $teams);
        $form->addText('score1', 'Skóre 1');
        $form->addSelect('team2_id', 'Tím 2', $teams);
        $form->addText('score2', 'Skóre 2');
        $form->addCheckbox('type', ' Play Off');
        $form->addSubmit('save', 'Uložiť');

        $form->onSuccess[] = $this->submittedAddFightForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditFightForm() {
        $form = new Form;

        $teams = $this->teamsRepository->getTeams();

        $form->addSelect('team1_id', 'Tím 1', $teams);
        $form->addText('score1', 'Skóre 1');
        $form->addSelect('team2_id', 'Tím 2', $teams);
        $form->addText('score2', 'Skóre 2');

        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = $this->submittedEditForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditThirdForm() {
        $form = new Form;

        $form->addGroup('Tím ' . $this->team1->name);
        $form->addText('st_third_1', 'Počet gólov v 1. tretine');
        $form->addText('nd_third_1', 'Počet gólov v 2. tretine');
        $form->addText('th_third_1', 'Počet gólov v 3. tretine');
        $form->addText('score1', 'Počet gólov v zápase');

        $form->addGroup('Tím ' . $this->team2->name);
        $form->addText('st_third_2', 'Počet gólov v 1. tretine');
        $form->addText('nd_third_2', 'Počet gólov v 2. tretine');
        $form->addText('th_third_2', 'Počet gólov v 3. tretine');
        $form->addText('score2', 'Počet gólov v zápase');

        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = $this->submittedEditThirdForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddFightForm(Form $form) {
        $values = $form->getValues(TRUE);
        if ($values['team1_id'] == $values['team2_id']) {
            $form->addError('Zvoľ dva rozdielne tímy.');
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
        $this->redirect('all#nav', $fight->ref('rounds', 'round_id'));
    }

    public function submittedEditForm(Form $form) {
        $values = $form->getValues();

        if ($values->team1_id == $values->team2_id) {
            $form->addError('Zvoľ dva rozdielne tímy.');
            return false;
        }

        $this->fightRow->update($values);
        $this->redirect('all#nav', $this->fightRow->ref('rounds', 'round_id'));
    }

    public function submittedEditThirdForm(Form $form) {
        $values = $form->getValues();

        FormHelper::changeEmptyToZero($values);

        $score1 = $values['st_third_1'] + $values['nd_third_1'] + $values['th_third_1'];
        $score2 = $values['st_third_2'] + $values['nd_third_2'] + $values['th_third_2'];

        if ($score1 != $values['score1']) {
            $form->addError("Pre tím " . $this->team1->name . " nesedí súčet gólov v tretinách s celkovým počtom gólov.");
            return false;
        }

        if ($score2 != $values['score2']) {
            $form->addError("Pre tím " . $this->team2->name . " nesedí súčet gólov v tretinách s celkovým počtom gólov.");
            return false;
        }

        $this->fightRow->update($values);
        $this->redirect('all#nav', $this->fightRow->ref('rounds', 'round_id'));
    }

    public function submittedDeleteForm() {
        $this->userIsLogged();
        $id = $this->fightRow->ref('rounds', 'round_id');
        $this->fightRow->delete();
        $this->flashMessage('Zápas odstránený.', 'success');
        $this->redirect('all#nav', $id);
    }

    public function formCancelled() {
        $this->redirect('all#nav', $this->fightRow->ref('rounds', 'round_id'));
    }

    public function updateTableRows($values, $type, $value = 1) {
        if ($values['score1'] > $values['score2']) {
            $this->tablesRepository->incrementTableValue($values['team1_id'], $type, 'win', $value);
            $this->tablesRepository->incrementTableValue($values['team2_id'], $type, 'lost', $value);
        } elseif ($values['score1'] < $values['score2']) {
            $this->tablesRepository->incrementTableValue($values['team2_id'], $type, 'win', $value);
            $this->tablesRepository->incrementTableValue($values['team1_id'], $type, 'lost', $value);
        } else {
            $this->tablesRepository->incrementTableValue($values['team2_id'], $type, 'tram', $value);
            $this->tablesRepository->incrementTableValue($values['team1_id'], $type, 'tram', $value);
        }
        $this->tablesRepository->updateFights($values['team1_id'], $type);
        $this->tablesRepository->updateFights($values['team2_id'], $type);
    }

    public function updateTablePoints($values, $type, $column = 'points') {
        if ($values['score1'] > $values['score2']) {
            $this->tablesRepository->incrementTableValue($values['team1_id'], $type, $column, 2);
            $this->tablesRepository->incrementTableValue($values['team2_id'], $type, $column, 0);
        } elseif ($values['score1'] < $values['score2']) {
            $this->tablesRepository->incrementTableValue($values['team2_id'], $type, $column, 2);
            $this->tablesRepository->incrementTableValue($values['team1_id'], $type, $column, 0);
        } else {
            $this->tablesRepository->incrementTableValue($values['team2_id'], $type, $column, 1);
            $this->tablesRepository->incrementTableValue($values['team1_id'], $type, $column, 1);
        }
    }

    public function updateTableGoals($values, $type) {
        $this->tablesRepository->incrementTableValue($values['team1_id'], $type, 'score1', $values['score1']);
        $this->tablesRepository->incrementTableValue($values['team1_id'], $type, 'score2', $values['score2']);
        $this->tablesRepository->incrementTableValue($values['team2_id'], $type, 'score1', $values['score2']);
        $this->tablesRepository->incrementTableValue($values['team2_id'], $type, 'score2', $values['score1']);
    }

}
