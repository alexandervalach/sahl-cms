<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Utils\Arrays;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Utils\FileSystem;
use Nette\Database\Table\ActiveRow;

class PlayerPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $playerRow;

    /** @var ActiveRow */
    private $teamRow;

    /** @var string */
    private $error = "Player not found!";

    public function actionView($id) {
        $this->teamRow = $this->teamsRepository->findById($id);
    }

    public function renderView($id) {
        $team = $this->teamRow;
        $this->template->players = $team->related('players')->order('goals ASC, lname ASC, fname ASC');
        $this->template->team = $team;
    }

    public function actionCreate($id) {
        $this->userIsLogged();
        $this->teamRow = $this->teamsRepository->findById($id);
    }

    public function renderCreate($id) {
        $this->template->team = $this->teamRow;
        $this->getComponent('addPlayerForm');
    }

    public function actionEdit($id) {
        $this->userIsLogged();
        $this->playerRow = $this->playersRepository->findById($id);
    }

    public function renderEdit($id) {
        if (!$this->playerRow) {
            throw new BadRequestException($this->error);
        }
        $this->getComponent('editPlayerForm')->setDefaults($this->playerRow);
    }

    public function actionDelete($id) {
        $this->userIsLogged();
        $this->playerRow = $this->playersRepository->findById($id);
    }

    public function renderDelete($id) {
        if (!$this->playerRow) {
            throw new BadRequestException;
        }
        $this->template->player = $this->playerRow;
        $this->getComponent('deleteForm');
    }

    protected function createComponentAddPlayerForm() {
        $form = new Form;

        $form->addText('fname', 'Meno:')
                ->setRequired("Meno je povinné pole.");

        $form->addText('lname', 'Priezvisko:')
                ->setRequired("Priezvisko je povinné pole.");

        $form->addText('num', 'Číslo:')
                ->setType('number');

        $form->addSubmit('save', 'Uložiť');

        $form->onSuccess[] = $this->submittedAddPlayerForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditPlayerForm() {
        $form = new Form;
        $form->addText('fname', 'Meno:')
                ->setRequired("Meno je povinné pole.");

        $form->addText('lname', 'Priezvisko:')
                ->setRequired("Priezvisko je povinné pole.");

        $form->addText('num', 'Číslo:')
                ->setType('number');
        
        $form->addText('goals', 'Góly:')
                ->setType('number');
        
        $form->addText('born', 'Dátum narodenia:')
                ->setAttribute('placeholder', 'RRRR-MM-DD')
                ->setRequired("Dátum narodenia je povinné pole.");

        $form->addSubmit('save', 'Uložiť');

        $form->onSuccess[] = $this->submittedEditPlayerForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddPlayerForm(Form $form) {
        $values = $form->getValues(TRUE);
        $id = $this->teamRow;
        $values['team_id'] = $id;
        $player = $this->playersRepository->insert($values);
        $this->redirect('view', $id);
    }

    public function submittedEditPlayerForm($form) {
        $values = $form->getValues();
        $player = $this->playerRow;
        $player->update($values);
        $this->redirect('view', $player->team_id);
    }
    
    public function submittedDeleteForm() {
        $player = $this->playerRow;
        $player->delete();
        $this->flashMessage('Hráč odstránený.', 'success');
        $this->redirect('view', $player->team_id );
    }

    public function formCancelled() {
        $this->redirect('default');
    }

}
