<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Application\BadRequetsException;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\FileSystem;

class TeamsPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $teamRow;

    /** @var ActiveRow */
    private $archRow;

    /** @var string */
    private $error = "Team not found";

    public function actionAll() {

    }

    public function renderAll() {
        $this->template->teams = $this->teamsRepository->findByValue('archive_id', null)->order("name ASC");
        $this['breadCrumb']->addLink("Tímy");

        if ($this->user->isLoggedIn()) {
            $this->getComponent("addForm");
        }
    }

    public function actionDelete($id) {
        $this->userIsLogged();
        $this->teamRow = $this->teamsRepository->findById($id);
    }

    public function renderDelete($id) {
        if (!$this->teamRow) {
            throw new BadRequetsException($this->error);
        }
        $this->template->team = $this->teamRow;
        $this->getComponent('deleteForm');
    }

    public function actionEdit($id) {
        $this->userIsLogged();
        $this->teamRow = $this->teamsRepository->findById($id);
    }

    public function renderEdit($id) {
        if (!$this->teamRow)
            throw new BadRequetsException($this->error);

        $this->getComponent('editTeamForm')->setDefaults($this->teamRow);
        $this->template->team = $this->teamRow;
    }

    public function actionUpload($id) {
        $this->userIsLogged();
        $this->teamRow = $this->teamsRepository->findById($id);
    }

    public function renderUpload($id) {
        $this->template->team = $this->teamRow;
        $this->getComponent('uploadForm');
    }

    public function actionArchView($id) {
        $this->archRow = $this->archiveRepository->findById($id);
    }

    public function renderArchView($id) {
        $this->template->teams = $this->teamsRepository->findByValue('archive_id', $id);
        $this->template->archive = $this->archRow;

        $this['breadCrumb']->addLink("Archív", $this->link("Archive:all"));
        $this['breadCrumb']->addLink($this->archRow->title, $this->link("Archive:view", $this->archRow));
        $this['breadCrumb']->addLink("Tímy");
    }

    protected function createComponentUploadForm() {
        $form = new Form;
        $form->addUpload('image', 'Nahraj obrázok');
        $form->addSubmit('upload', 'Uložiť');
        $form->onSuccess[] = $this->submittedUploadForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentAddForm() {
        $form = new Form;

        $form->addText('name', 'Tím: ')
             ->setRequired('Názov tímu je povinné pole.')
             ->addRule(Form::MAX_LENGTH, "Dĺžka reťazce smie byť len 255 znakov.", 255);
        $form->addSubmit('save', 'Uložiť');

        $form->onSuccess[] = $this->submittedAddForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditTeamForm() {
        $form = new Form;

        $form->addText('name', 'Tím: ')
                ->setRequired('Názov tímu je povinné pole.')
                ->addRule(Form::MAX_LENGTH, "Dĺžka reťazce smie byť len 255 znakov.", 255);

        $form->addSubmit('save', 'Uložiť');

        $form->onSuccess[] = $this->submittedEditTeamForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedUploadForm(Form $form) {
        $values = $form->getValues();
        $img = $values->image;

        if ($img->isOk() AND $img->isImage()) {
            $name = $img->getSanitizedName();
            $img->move($this->storage . $name);
            $data = array('image' => $name);
            $this->teamRow->update($data);
        }
        $this->redirect('Player:view', $this->teamRow);
    }

    public function submittedDeleteForm() {
        $team = $this->teamRow;
        $players = $team->related('players');
        $img = new FileSystem;

        foreach ($players as $player) {
            $player->delete();
        }
        
        $img->delete( $this->storage . $team->image );
        $team->delete();
        $this->flashMessage('Tím bol odstránený aj so všetkými hráčmi.', 'success');
        $this->redirect('all');
    }

    public function submittedAddForm(Form $form) {
        $values = $form->getValues();
        $this->teamsRepository->insert($values);
        $this->redirect('all');
    }

    public function submittedEditTeamForm(Form $form) {
        $values = $form->getValues();
        $this->teamRow->update($values);
        $this->redirect('all');
    }

    public function formCancelled() {
        $this->redirect('all');
    }

}
