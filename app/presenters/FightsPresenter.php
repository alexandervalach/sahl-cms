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

    /** @var ActiveRow */
    private $archRow;

    /** @var string */
    private $error = "Match not found!";

    /** @var ActiveRow */
    private $team1;

    /** @var ActiveRow */
    private $team2;

    public function actionAll($id) {
        $this->roundRow = $this->roundsRepository->findById($id);
        $this['breadCrumb']->addLink("Kolá", $this->link("Round:all"));
        $this['breadCrumb']->addLink($this->roundRow->name);
    }

    public function renderAll($id) {

        if (!$this->roundRow) {
            throw new BadRequestException("Round not found.");
        }

        $i = 0;
        $fight_data = array();
        $fights = $this->roundRow->related('fights');

        foreach ($fights as $fight) {
            $fight_data[$i]['goals'] = $fight->related('goals')->order('goals DESC');
            $fight_data[$i]['team_1'] = $fight->ref('teams', 'team1_id');
            $fight_data[$i]['team_2'] = $fight->ref('teams', 'team2_id');
            $fight_data[$i]['home_goals'] = $fight->related('goals')->where('home', 1)->order('goals DESC');
            $fight_data[$i]['guest_goals'] = $fight->related('goals')->where('home', 0)->order('goals DESC');

            if ($fight->score1 > $fight->score2) {
                $fight_data[$i]['state_1'] = 'text-success';
                $fight_data[$i]['state_2'] = 'text-danger';
            } else if ($fight->score1 < $fight->score2) {
                $fight_data[$i]['state_1'] = 'text-danger';
                $fight_data[$i]['state_2'] = 'text-success';
            } else {
                $fight_data[$i]['state_1'] = $fight_data[$i]['state_2'] = '';
            } 
            $i++;
        }

        $this->template->fights = $fights;
        $this->template->fight_data = $fight_data;
        $this->template->i = 0;
        $this['breadCrumb']->addLink("Kolá", $this->link("Round:all"));
        $this['breadCrumb']->addLink($this->roundRow->name);

        if ($this->user->isLoggedIn()) {
            $this->getComponent('addForm');
        }
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
        $this['breadCrumb']->addLink('Archív', $this->link("Archive:all"));
        $this['breadCrumb']->addLink($this->archRow->title, $this->link("Archive:view", $this->archRow));
        $this['breadCrumb']->addLink("Kolá", $this->link("Round:archView", $this->archRow));
        $this['breadCrumb']->addLink($this->roundRow->name);
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

    protected function createComponentAddForm() {
        $teams = $this->teamsRepository->getTeams();
        $form = new Form;
        $form->addSelect('team1_id', 'Tím 1', $teams);
        $form->addText('score1', 'Skóre 1');
        $form->addSelect('team2_id', 'Tím 2', $teams);
        $form->addText('score2', 'Skóre 2');
        $form->addCheckbox('type', ' Označiť zápas ako Play Off');
        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = [$this, 'submittedAddForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditFightForm() {
        $teams = $this->teamsRepository->getTeams();
        $form = new Form;
        $form->addSelect('team1_id', 'Tím 1', $teams);
        $form->addText('score1', 'Skóre 1');
        $form->addSelect('team2_id', 'Tím 2', $teams);
        $form->addText('score2', 'Skóre 2');
        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = [$this, 'submittedEditForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddForm(Form $form) {
        $values = $form->getValues(TRUE);
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
        $this->flashMessage('Zápas pridaný', 'success');
        $this->redirect('all', $fight->ref('rounds', 'round_id'));
    }

    public function submittedEditForm(Form $form, $values) {
        if ($values->team1_id == $values->team2_id) {
            $form->addError('Zvoľte dva rozdielne tímy.');
            return false;
        }
        $this->fightRow->update($values);
        $this->redirect('all', $this->fightRow->ref('rounds', 'round_id'));
    }

    public function submittedDeleteForm() {
        $id = $this->fightRow->ref('rounds', 'round_id');
        $this->fightRow->delete();
        $this->flashMessage('Zápas odstránený', 'success');
        $this->redirect('all', $id);
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
        $this->redirect('all', $this->fightRow->ref('rounds', 'round_id'));
    }

}
