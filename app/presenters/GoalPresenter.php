<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;

class GoalPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $fightRow;

    /** @var ActiveRow */
    private $team1;

    /** @var ActiveRow */
    private $team2;

    /** @var string */
    private $error = "Player not found!";

    public function actionAdd($id) {
        $this->userIsLogged();
        $this->fightRow = $this->fightsRepository->findById($id);
        $this->team1 = $this->fightsRepository->getTeamForFight($this->fightRow, 'team1_id');
        $this->team2 = $this->fightsRepository->getTeamForFight($this->fightRow, 'team2_id');
    }

    public function renderAdd($id) {
        if (!$this->fightRow) {
            throw new BadRequestException($this->error);
        }
        $this->getComponent('addForm');
    }

    protected function createComponentAddForm() {
        $form = new Form;
        $players = $this->fightsRepository->getPlayersForSelect($this->fightRow, 'team1_id', 'team2_id');
        $form->addSelect('player_id', 'Hráči', $players);
        $form->addText('goals', 'Počet gólov');
        $form->addSubmit('save', 'Uložiť');

        $form->onSuccess[] = $this->submittedAddForm;
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
        $this->redirect('add', $this->fightRow);
    }

    public function formCancelled() {
        $this->redirect('Round:all');
    }

}
