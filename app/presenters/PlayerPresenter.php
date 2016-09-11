<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
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
        $this->template->players = $team->related('players')->where('NOT type_id', 2);
        $this->template->goalies = $team->related('players')->where('type_id', 2);
        $this->template->team = $team;
        $this->template->imgFolder = $this->imgFolder;
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
        $this->template->team = $this->playerRow->ref('team_id');
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

    public function actionArchive($id) {
        $this->teamRow = $this->teamsRepository->findById($id);
    }

    public function renderArchive($id) {
        if (!$this->teamRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->players = $this->playersRepository->findByValue('team_id', $id)->where('NOT type_id', 2);
        $this->template->goalies = $players = $this->playersRepository->findByValue('team_id', $id)->where('type_id', 2);
        $this->template->imgFolder = $this->imgFolder;
        $this->template->team = $this->teamRow;
    }

    protected function createComponentAddPlayerForm() {
        $form = new Form;
        
        $types = $this->playerTypesRepository->getTypes();
        
        $form->addText('lname', 'Meno a priezvisko:');
        $form->addText('born', 'Dátum narodenia:')
                ->setAttribute('placeholder', 'DD.MM.RRRR');
        $form->addText('num', 'Číslo:');
        $form->addSelect('type_id', 'Typ hráča', $types);
        $form->addCheckbox('trans', ' Prestupový hráč');
        $form->addSubmit('save', 'Uložiť');

        $form->onSuccess[] = $this->submittedAddPlayerForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditPlayerForm() {
        $form = new Form;
        
        $types = $this->playerTypesRepository->getTypes();
        
        $form->addText('lname', 'Meno a priezvisko:');
        $form->addText('born', 'Dátum narodenia:')
                ->setAttribute('placeholder', 'DD.MM.RRRR');
        $form->addText('num', 'Číslo:');
        $form->addText('goals', 'Góly:');
        $form->addCheckbox('trans', ' Prestupový hráč');
        $form->addSelect('type_id', 'Typ hráča', $types);
        $form->addSubmit('save', 'Uložiť');

        $form->onSuccess[] = $this->submittedEditPlayerForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddPlayerForm(Form $form) {
        $values = $form->getValues(TRUE);
        $id = $this->teamRow;
        $values['team_id'] = $id;
        $this->playersRepository->insert($values);
        $this->redirect('view#nav', $id);
    }

    public function submittedEditPlayerForm($form) {
        $values = $form->getValues();
        $player = $this->playerRow;
        $player->update($values);
        $this->redirect('view#nav', $player->team_id);
    }

    public function submittedDeleteForm() {
        $player = $this->playerRow;
        $player->delete();
        $this->flashMessage('Hráč odstránený.', 'success');
        $this->redirect('view#nav', $player->team_id);
    }

    public function formCancelled() {
        $this->redirect('view#nav', $this->playerRow->team_id);
    }

}
