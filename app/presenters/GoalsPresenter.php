<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;

class GoalsPresenter extends BasePresenter {

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

    public function actionView($id) {
        $this->fightRow = $this->fightsRepository->findById($id);
        $this->roundRow = $this->roundsRepository->findById($this->fightRow->round_id);
    }

    public function renderView($id) {
        $this->template->fight = $this->fightRow;
        $this->template->goals = $this->goalsRepository->findByValue('fight_id', $this->fightRow)
                ->order('home DESC, goals DESC');
        $this->template->team1 = $this->fightRow->ref('team1_id');
        $this->template->team2 = $this->fightRow->ref('team2_id');

        if ($this->user->isloggedIn()) {
            $this->getComponent(self::ADD_FORM);
        }
    }

    public function actionEdit($id) {
        $this->userIsLogged();
        $this->goalRow = $this->goalsRepository->findById($id);
        $this->fightRow = $this->goalRow->ref('fight_id');
    }

    public function renderEdit($id) {
        if (!$this->goalRow) {
            throw new BadRequestException(self::PLAYER_NOT_FOUND);
        }
        $this->template->goal = $this->goalRow;
        $this->template->player = $this->goalRow->ref('players', 'player_id');
        $this->getComponent(self::EDIT_FORM)->setDefaults($this->goalRow);
    }

    public function actionRemove($id) {
        $this->userIsLogged();
        $this->goalRow = $this->goalsRepository->findById($id);
        $this->fightRow = $this->goalRow->ref('fights', 'fight_id');
        $this->submittedRemove();
    }

    /**
     * @return Nette\Aplication\UI\Form
     */
    protected function createComponentAddForm() {
        $players = $this->teamPlayersHelper($this->fightRow);
        $form = new Form;
        $form->addSelect('player_id', 'Hráči', $players);
        $form->addText('goals', 'Počet gólov')
             ->setDefaultValue(1)
             ->setAttribute('placeholder', 0)
             ->addRule(Form::FILLED, 'Ešte treba vyplniť počet gólov')
             ->addRule(Form::INTEGER, 'Počet gólov musí byť celé číslo.');
        $form->addCheckbox('home', ' Hráč domáceho tímu');
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
        $form->addText('goals', 'Počet gólov')
             ->setAttribute('placeholder', 0)
             ->addRule(Form::FILLED, 'Ešte treba vyplniť počet gólov.')
             ->addRule(Form::INTEGER, 'Počet gólov musí byť celé číslo.');
        $form->addCheckbox('home', ' Hráč domáceho tímu');
        $form->addSubmit('save', 'Uložiť');
        $form->addSubmit('cancel', 'Zrušiť')
             ->setAttribute('class', self::BTN_WARNING)
             ->setAttribute('data-dismiss', 'modal');
        $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddForm(Form $form, $values) {
        $values['fight_id'] = $this->fightRow;
        $values['player_id'] = $values['player_id'];
        $this->goalsRepository->insert($values);

        $player = $this->playersRepository->findById($values['player_id']);
        $numOfGoals = $player->goals + $values['goals'];
        $goals = array('goals' => $numOfGoals);
        $player->update($goals);

        $this->flashMessage("Góly boli pridané", self::SUCCESS);
        $this->redirect('view', $this->fightRow);
    }

    public function submittedEditForm(Form $form, $values) {
        $goalDifference = $values['goals'] - $this->goalRow->goals;
        $this->goalRow->update($values);

        $player = $this->playersRepository->findById($this->goalRow->player_id);
        $numOfGoals = $player->goals + $goalDifference;
        $goals = array('goals' => $numOfGoals);
        $player->update($goals);

        $this->flashMessage('Góly boli upravené', self::SUCCESS);
        $this->redirect('view', $this->fightRow);
    }

    public function submittedRemove() {
        $player = $this->playersRepository->findById($this->goalRow->player_id);
        $numOfGoals = $player->goals - $this->goalRow->goals;
        $goals = array('goals' => $numOfGoals);
        $player->update($goals);

        $this->goalRow->delete();
        $this->flashMessage('Góly boli odpočítané', self::SUCCESS);
        $this->redirect('view', $this->fightRow);
    }

    public function formCancelled() {
        $this->redirect('view', $this->goalRow->fight_id);
    }

    protected function teamPlayersHelper(ActiveRow $row) {
        $team1 = $this->teamsRepository->findById($row->team1_id);
        $team2 = $this->teamsRepository->findById($row->team2_id);
        $players1 = $this->teamsRepository->getPlayersForTeam($team1->id);
        $players2 = $this->teamsRepository->getPlayersForTeam($team2->id);
        $players = array_replace($players1, $players2);
        return $players;
    }

}
