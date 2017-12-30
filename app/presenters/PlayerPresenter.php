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

    public function actionAdd($id) {
        $this->userIsLogged();
        $this->teamRow = $this->teamsRepository->findById($id);
    }

    public function renderAdd($id) {
        $this->template->team = $this->teamRow;
        $this->getComponent('addForm');
    }

    public function actionEdit($id) {
        $this->userIsLogged();
        $this->playerRow = $this->playersRepository->findById($id);
    }

    public function renderEdit($id) {
        if (!$this->playerRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->player = $this->playerRow;
        $this->getComponent('editForm')->setDefaults($this->playerRow);
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

    public function actionArchView($id, $param) {
        $this->teamRow = $this->teamsRepository->findById($param);
    }

    public function renderArchView($id, $param) {
        if (!$this->teamRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->players = $this->playersRepository->findByValue('team_id', $param)->where('archive_id', $id)->where('NOT type_id', 2);
        $this->template->goalies = $players = $this->playersRepository->findByValue('team_id', $param)->where('archive_id', $id)->where('type_id', 2);
        $this->template->imgFolder = $this->imgFolder;
        $this->template->team = $this->teamRow;
        $this->template->archive = $this->teamRow->ref('archive', 'archive_id');
    }

    protected function createComponentAddForm() {
        $types = $this->playerTypesRepository->getTypes();
        
        $form = new Form;
        $form->addText('lname', 'Meno a priezvisko:');
        $form->addText('born', 'Dátum narodenia:')
                ->setAttribute('placeholder', 'DD.MM.RRRR');
        $form->addText('num', 'Číslo:');
        $form->addSelect('type_id', 'Typ hráča', $types);
        $form->addCheckbox('trans', ' Prestupový hráč');
        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = [$this, 'submittedAddForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditForm() {
        $types = $this->playerTypesRepository->getTypes();
        
        $form = new Form;
        $form->addText('lname', 'Meno a priezvisko:');
        $form->addText('born', 'Dátum narodenia:')
                ->setAttribute('placeholder', 'DD.MM.RRRR');
        $form->addText('num', 'Číslo:');
        $form->addText('goals', 'Góly:');
        $form->addCheckbox('trans', ' Prestupový hráč');
        $form->addSelect('type_id', 'Typ hráča', $types);
        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = [$this, 'submittedEditForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddForm(Form $form) {
        $values = $form->getValues(TRUE);
        $values['team_id'] = $this->teamRow;
        $this->playersRepository->insert($values);
        $this->flashMessage('Hráč pridaný', 'success');
        $this->redirect('view', $this->teamRow);
    }

    public function submittedEditForm(Form $form, $values) {
        $this->playerRow->update($values);
        $this->flashMessage('Hráč upravený', 'success');
        $this->redirect('view', $this->playerRow->team_id);
    }

    public function submittedDeleteForm() {
        $team = $this->playerRow->team_id;
        $this->playerRow->delete();
        $this->flashMessage('Hráč odstránený.', 'success');
        $this->redirect('view', $team);
    }

    public function formCancelled() {
        $this->redirect('view', $this->playerRow->team_id);
    }

}
