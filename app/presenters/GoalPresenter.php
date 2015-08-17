<?php

class GoalPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $fightRow;

    /** @var ActiveRow */
    private $team1;

    /** @var ActiveRow */
    private $team2;

    /** @var string */
    private $error = "Player not found!";

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

    public function submittedAddPlayerGoalsForm(Form $form) {
        $values = $form->getValues();
        $this->goalsRepository->insert($values);
        $this->redirect('addPlayerGoals', $this->fightRow);
    }

    public function formCancelled() {
        $this->redirect('Round:all');
    }
}
