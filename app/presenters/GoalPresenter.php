<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;

class GoalPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $goalRow;

    /** @var ActiveRow */
    private $fightRow;

    /** @var ActiveRow */
    private $roundRow;

    /** @var ActiveRow */
    private $team1;

    /** @var ActiveRow */
    private $team2;

    /** @var string */
    private $error = "Player not found!";

    public function actionView($id) {
        $this->userIsLogged();
        $this->fightRow = $this->fightsRepository->findById($id);
    }

    public function renderView($id) {
        $this->template->fight = $this->fightRow;
        $this->template->goals = $this->goalsRepository->findByValue('fight_id', $this->fightRow->id)->order('home DESC');
    }

    public function actionAdd($id) {
        $this->userIsLogged();
        $this->fightRow = $this->fightsRepository->findById($id);
        $this->roundRow = $this->fightsRepository->getForFight($this->fightRow, 'round_id', 'rounds');
        $this->team1 = $this->fightsRepository->getForFight($this->fightRow, 'team1_id');
        $this->team2 = $this->fightsRepository->getForFight($this->fightRow, 'team2_id');
    }

    public function renderAdd($id) {
        if (!$this->fightRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->round = $this->roundRow;
        $this->getComponent('addForm');
    }

    public function actionEdit($id) {
        $this->userIsLogged();
        $this->goalRow = $this->goalsRepository->findById($id);
        $this->fightRow = $this->goalRow->ref('fight_id');
    }

    public function renderEdit($id) {
        if (!$this->goalRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->goal = $this->goalRow;
        $this->getComponent('editForm')->setDefaults($this->goalRow);
    }

    public function actionDelete($id) {
        $this->userIsLogged();
        $this->goalRow = $this->goalsRepository->findById($id);
        $this->fightRow = $this->goalRow->ref('fights', 'fight_id');
    }

    public function renderDelete($id) {
        if (!$this->goalRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->goal = $this->goalRow;
        $this->getComponent('deleteForm');
    }

    protected function createComponentAddForm() {
        $form = new Form;
        $players = $this->fightsRepository->getPlayersForSelect($this->fightRow, 'team1_id', 'team2_id');
        $form->addSelect('player_id', 'Hráči', $players);
        $form->addText('goals', 'Počet gólov')
                ->setDefaultValue(1)
                ->addRule(Form::INTEGER, 'Počet gólov musí byť celé číslo.');
        $form->addCheckbox('home', ' Hráč domáceho tímu');
        $form->addSubmit('save', 'Uložiť');

        $form->onSuccess[] = $this->submittedAddForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditForm() {
        $form = new Form;
        $players = $this->fightsRepository->getPlayersForSelect($this->fightRow, 'team1_id', 'team2_id');
        $form->addSelect('player_id', 'Hráči', $players);
        $form->addText('goals', 'Počet gólov')
                ->addRule(Form::INTEGER, 'Počet gólov musí byť celé číslo.');
        $form->addCheckbox('home', ' Hráč domáceho tímu');
        $form->addSubmit('save', 'Uložiť');

        $form->onSuccess[] = $this->submittedEditForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddForm(Form $form) {
        $values = $form->getValues();
        $values['fight_id'] = $this->fightRow;
        $this->goalsRepository->insert($values);

        $player = $this->playersRepository->findById($values['player_id']);
        $numOfGoals = $player->goals + $values['goals'];
        $goals = array('goals' => $numOfGoals);
        $player->update($goals);

        $this->flashMessage("Záznam o hráčovi $player->lname pridaný.", 'success');
        $this->redirect('add#nav', $this->fightRow);
    }

    public function submittedEditForm(Form $form) {
        $values = $form->getValues();
        $goalDifference =  $values['goals'] - $this->goalRow->goals;
        $this->goalRow->update($values);

        $player = $this->playersRepository->findById($this->goalRow->player_id);
        $numOfGoals = $player->goals + $goalDifference;
        $goals = array('goals' => $numOfGoals);
        $player->update($goals);

        $this->redirect('view#nav', $this->fightRow);
    }

    public function submittedDeleteForm() {
        $player = $this->playersRepository->findById($this->goalRow->player_id);
        $numOfGoals = $player->goals - $this->goalRow->goals;
        $goals = array('goals' => $numOfGoals);
        $player->update($goals);

        $this->goalRow->delete();
        $this->flashMessage("Góly boli hráčovi odpočítané", 'success');
        $this->redirect('view#nav', $this->fightRow->id);
    }

    public function formCancelled() {
        $this->redirect('Fights:all#nav', $this->fightRow->round_id);
    }

}
