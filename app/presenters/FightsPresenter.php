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

    public function actionAll() {
        
    }

    public function renderAll() {
        $this->template->fights = $this->fightsRepository->findAll()->order('time');
    }

    protected function actionAdd($id) {
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

    public function actionEditThird($id) {
        $this->userIsLogged();
        $this->fightRow = $this->fightsRepository->findById($id);
        $this->team1 = $this->fightsRepository->getTeamForFight($this->fightRow, 'team1_id');
        $this->team2 = $this->fightsRepository->getTeamForFight($this->fightRow, 'team2_id');
    }

    public function renderEditThird($id) {
        if (!$this->fightRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->fight = $this->fightRow;
        $this->getComponent('editThirdForm')->setDefaults($this->fightRow);
    }

    public function actionAddPlayerGoals($id) {
        $this->userIsLogged();
        $this->fightRow = $this->fightsRepository->findById($id);
        $this->team1 = $this->fightsRepository->getTeamForFight($this->fightRow, 'team1_id');
        $this->team2 = $this->fightsRepository->getTeamForFight($this->fightRow, 'team2_id');
    }

    public function renderAddPlayerGoals($id) {
        if (!$this->fightRow) {
            throw new BadRequestException($this->error);
        }
        $this->getComponent('addPlayerGoalsForm');
    }

    protected function createComponentAddFightForm() {
        $form = new Form;

        $teams = $this->teamsRepository->getTeams();

        $form->addSelect('team1_id', 'Tím 1', $teams)
                ->setRequired("Názov tímu 1 je povinné pole");

        $form->addSelect('team2_id', 'Tím 2', $teams)
                ->setRequired("Názov tímu 2 je povinné pole");

        $form->addText('score1', 'Skóre 1')
                ->setRequired("Počet bodov je povinné pole");

        $form->addText('score2', 'Skóre 2')
                ->setRequired("Počet bodov je povinné pole");

        $form->addText('time', 'Dátum')
                ->setAttribute('value', date('Y-m-d'));

        $form->addSubmit('save', 'Uložiť');

        $form->onSuccess[] = $this->submittedAddFightForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditFightForm() {
        $form = new Form;

        $teams = $this->teamsRepository->getTeams();

        $form->addSelect('team1_id', 'Tím 1', $teams)
                ->setRequired("Názov tímu 1 je povinné pole");

        $form->addSelect('team2_id', 'Tím 2', $teams)
                ->setRequired("Názov tímu je povinné pole");

        $form->addText('score1', 'Skóre 1')
                ->setRequired("Počet bodov je povinné pole");

        $form->addText('score2', 'Skóre 2')
                ->setRequired("Počet bodov je povinné pole");

        $form->addText('time', 'Dátum');

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

    protected function createComponentAddPlayerGoalsForm() {
        $form = new Form;
        $teamOnePlayers = $this->fightsRepository->getPlayersForTeam($this->fightRow, 'team1_id');
        //$teamTwoPlayers = $this->fightsRepository->getPlayersForTeam($this->fightRow, 'team2_id');

        $form->addSelect('player_id', 'Hráči tímu ' . $this->team1->name, $teamOnePlayers);
        //$form->addSelect('player2_id', 'Hráči tímu ' . $this->team2->name, $teamTwoPlayers);
        $form->addText('goals', 'Počet gólov');
        $form->addSubmit('save', 'Uložiť'); 

        $form->onSuccess[] = $this->submittedAddPlayerGoalsForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddFightForm(Form $form) {
        $values = $form->getValues();

        if ($values->team1_id == $values->team2_id) {
            $form->addError('Zvoľ dva rozdielne tímy.');
            return false;
        }

        $values['round_id'] = $this->roundRow;
        $this->fightsRepository->insert($values);
        $this->redirect('all');
    }

    public function submittedEditForm(Form $form) {
        $values = $form->getValues();

        if ($values->team1_id == $values->team2_id) {
            $form->addError('Zvoľ dva rozdielne tímy.');
            return false;
        }

        $this->fightRow->update($values);
        $this->redirect('all');
    }

    public function submittedEditThirdForm(Form $form) {
        $values = $form->getValues();

        foreach ($values as $value) {
            if (empty($value)) {
                $value = 0;
            }
        }

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
        $this->redirect('Round:all');
    }

    public function submittedAddPlayerGoalsForm(Form $form) {
        $values = $form->getValues();
        $this->goalsRepository->insert($values);
        $this->redirect('addPlayerGoals', $this->fightRow);
    }

    public function submittedDeleteForm() {
        $this->userIsLogged();
        $this->fightRow->delete();
        $this->flashMessage('Zápas odstránený.', 'success');
        $this->redirect('Round:all');
    }

    public function formCancelled() {
        $this->redirect('Round:all');
    }

}
