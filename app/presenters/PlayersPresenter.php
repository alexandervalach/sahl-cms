<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;

class PlayersPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $playerRow;

    /** @var ActiveRow */
    private $teamRow;

    /** @var ActiveRow */
    private $archRow;

    /** @var string */
    private $error = "Player not found";

    public function renderAll() {
        $this->template->stats = $this->playersRepository->findByValue('archive_id', null)
                                      ->where('lname != ?', ' ')
                                      ->order('goals DESC, lname DESC');
        $this->template->i = 0;
        $this->template->j = 0;
        $this->template->current = 0;
        $this->template->previous = 0;

        $this['breadCrumb']->addLink("Hráči");
        $this['breadCrumb']->addLink("Štatistiky");
        
        if ($this->user->isLoggedIn()) {
            $this->getComponent('resetForm');
        }
    }

    public function actionArchAll($id) {
        $this->archRow = $this->archivesRepository->findById($id);
    }

    public function renderArchAll($id) {
        $this->template->stats = $this->playersRepository->findByValue('archive_id', $id)
                                      ->where('lname != ?', ' ')
                                      ->order('goals DESC, lname DESC');
        $this->template->archive = $this->archRow;
        $this->template->i = 0;
        $this->template->j = 0;
        $this->template->current = 0;
        $this->template->previous = 0;

        $this['breadCrumb']->addLink("Archív", $this->link("Archives:all"));
        $this['breadCrumb']->addLink($this->archRow->title, $this->link("Archives:view", $this->archRow));
        $this['breadCrumb']->addLink("Štatistiky");
    }

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
            throw new BadRequestException($this->error);
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
        $form->addText('born', 'Dátum narodenia:');
        $form->addText('num', 'Číslo:');
        $form->addSelect('type_id', 'Typ hráča', $types);
        $form->addCheckbox('trans', ' Prestupový hráč');
        $form->addSubmit('add', 'Pridať');
        $form->onSuccess[] = [$this, 'submittedAddForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditForm() {
        $types = $this->playerTypesRepository->getTypes();
        
        $form = new Form;
        $form->addText('lname', 'Meno a priezvisko:');
        $form->addText('born', 'Dátum narodenia:');
        $form->addText('num', 'Číslo:');
        $form->addText('goals', 'Góly:');
        $form->addCheckbox('trans', ' Prestupový hráč');
        $form->addSelect('type_id', 'Typ hráča', $types);
        $form->addSubmit('edit', 'Upraviť');
        $form->onSuccess[] = [$this, 'submittedEditForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentResetForm() {
        $form = new Form;
        $form->addSubmit('reset', 'Vynulovať')
             ->setAttribute('class', 'btn btn-large btn-danger')
             ->onClick[] = $this->submittedResetForm;
        $form->addSubmit('cancel', 'Zrušiť')
             ->setAttribute('class', 'btn btn-large btn-warning')
             ->setAttribute('data-dismiss', 'modal');
        $form->addProtection();
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }


    public function submittedAddForm(Form $form, $values) {
        $values['team_id'] = $this->teamRow;
        $this->playersRepository->insert($values);
        $this->flashMessage('Hráč bol pridaný', 'success');
        $this->redirect('view', $this->teamRow);
    }

    public function submittedEditForm(Form $form, $values) {
        $this->playerRow->update($values);
        $this->flashMessage('Hráč bol upravený', 'success');
        $this->redirect('view', $this->playerRow->team_id);
    }

    public function submittedDeleteForm() {
        $team = $this->playerRow->team_id;
        $this->playerRow->delete();
        $this->flashMessage('Hráč bol odstránený.', 'success');
        $this->redirect('view', $team);
    }

    public function submittedResetForm() {
        $players = $this->playersRepository
                        ->findByValue('archive_id', null)
                        ->where('goals != ?', 0);
        $values = array('goals' => 0);
        foreach ($players as $player) {
            $player->update($values);
        }
        $this->redirect("all");
    }

    public function formCancelled() {
        $this->redirect('view', $this->playerRow->team_id);
    }

}
