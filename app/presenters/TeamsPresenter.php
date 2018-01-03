<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Application\BadRequetsException;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\FileSystem;
use Nette\IOException;

class TeamsPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $teamRow;

    /** @var ActiveRow */
    private $archRow;

    /** @var string */
    private $error = "Team not found";

    public function renderAll() {
        $this->template->teams = $this->teamsRepository->findByValue('archive_id', null)->order('name'); 
        $this['breadCrumb']->addLink('Hráči');
        $this['breadCrumb']->addLink('Tímy');

        if ($this->user->isLoggedIn()) {
            $this->getComponent("addForm");
        }
    }

    public function actionView($id) {
        $this->teamRow = $this->teamsRepository->findById($id);
    }

    public function renderView($id) {
        if (!$this->teamRow) {
            throw new BadRequetsException($this->error);
        }

        $this->template->players = $this->playersRepository->findByValue('team_id', $id)
                                        ->where('type_id != ?', 2)->where('archive_id', null);
        $this->template->goalies = $this->playersRepository->findByValue('team_id', $id)
                                        ->where('type_id', 2)->where('archive_id', null);
        $this->template->team = $this->teamRow;
        $this->template->imgFolder = $this->imgFolder;
        $this->template->i = 0;
        $this->template->j = 0;
        $this['breadCrumb']->addLink('Hráči');
        $this['breadCrumb']->addLink("Tímy", $this->link("Teams:all"));
        $this['breadCrumb']->addLink($this->teamRow->name);

        if ($this->user->isLoggedIn()) {
            $this->getComponent('editForm')->setDefaults($this->teamRow);
            $this->getComponent('uploadForm');
            $this->getComponent('deleteForm');
        }   
    }

    public function actionArchAll($id) {
        $this->archRow = $this->archivesRepository->findById($id);
    }

    public function renderArchAll($id) {
        $this->template->teams = $this->teamsRepository->findByValue('archive_id', $id);
        $this->template->archive = $this->archRow;

        $this['breadCrumb']->addLink("Archív", $this->link("Archives:all"));
        $this['breadCrumb']->addLink($this->archRow->title, $this->link("Archives:view", $this->archRow));
        $this['breadCrumb']->addLink("Tímy");
    }

    public function actionArchView($id) {
        $this->archRow = $this->archivesRepository->findById($id);
    }

    public function renderArchView($id) {
        $this->template->teams = $this->teamsRepository->findByValue('archive_id', $id);
        $this->template->archive = $this->archRow;

        $this['breadCrumb']->addLink("Archív", $this->link("Archives:all"));
        $this['breadCrumb']->addLink($this->archRow->title, $this->link("Archives:view", $this->archRow));
        $this['breadCrumb']->addLink("Tímy");
    }

    protected function createComponentUploadForm() {
        $form = new Form;
        $form->addUpload('image', 'Nahrajte obrázok');
        $form->addSubmit('upload', 'Nastaviť obrázok');
        $form->onSuccess[] = [$this, 'submittedUploadForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentAddForm() {
        $form = new Form;
        $form->addText('name', 'Tím: ')
             ->setRequired('Názov tímu je povinné pole.')
             ->addRule(Form::MAX_LENGTH, "Dĺžka reťazce smie byť len 255 znakov.", 255);
        $form->addSubmit('save', 'Pridať');
        $form->onSuccess[] = [$this, 'submittedAddForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditForm() {
        $form = new Form;
        $form->addText('name', 'Tím: ')
             ->setRequired('Názov tímu je povinné pole.')
             ->addRule(Form::MAX_LENGTH, "Dĺžka reťazce smie byť len 255 znakov.", 255);
        $form->addSubmit('save', 'Upraviť')
             ->setAttribute('class', 'btn btn-large btn-success');
        $form->addSubmit('cancel', 'Zrušiť')
             ->setAttribute('class', 'btn btn-large btn-warning')
             ->setAttribute('data-dismiss', 'modal');
        $form->onSuccess[] = [$this, 'submittedEditForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentDeleteForm() {
        $form = new Form;
        $form->addSubmit('delete', 'Odstrániť')
             ->setAttribute('class', 'btn btn-large btn-danger');
        $form->addSubmit('cancel', 'Zrušiť')
             ->setAttribute('class', 'btn btn-large btn-warning')
             ->setAttribute('data-dismiss', 'modal');
        $form->addProtection();
        $form->onSuccess[] = [$this, 'submittedDeleteForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedUploadForm(Form $form, $values) {
        $img = $values->image;

        if ($img->isOk() AND $img->isImage()) {
            $img_name = $img->getSanitizedName();
            $img->move($this->imgFolder . '/' . $img_name);
            $data = array('image' => $img_name);
            $this->teamRow->update($data);
            $this->flashMessage('Obrázok bol pridaný', 'success');
        } else {
            $this->flashMessage('Nastala chyba. Skúste znova', 'danger');
        }

        $this->redirect('view', $this->teamRow);
    }

    public function submittedDeleteForm() {
        $players = $this->teamRow->related('players');
        foreach ($players as $player) {
            $player->delete();
        }

        try {
            FileSystem::delete($this->imgFolder . '/' . $this->teamRow->image);
            $this->flashMessage('Tím bol odstránený', 'success');
        } catch(IOException $e) {
            $this->flashMessage('Tím bol odstránený, ale nepodarilo sa odtrániť obrázok tímu', 'danger');
        }
        
        $this->teamRow->delete();
        $this->redirect('all');
    }

    public function submittedAddForm(Form $form, $values) {
        $this->teamsRepository->insert($values);
        $this->flashMessage('Tím bol pridaný', 'success');
        $this->redirect('all');
    }

    public function submittedEditForm(Form $form, $values) {
        $this->teamRow->update($values);
        $this->flashMessage('Tím bol upravený', 'success');
        $this->redirect('view', $this->teamRow);
    }

}
