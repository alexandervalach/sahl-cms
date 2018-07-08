<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Application\BadRequetsException;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\FileSystem;
use Nette\IOException;

class TeamsPresenter extends BasePresenter {

    const TEAM_NOT_FOUND = 'Team not found';
    const ADD_PLAYER_FORM = 'addPlayerForm';
    const SUBMITTED_ADD_PLAYER_FORM = 'submittedAddPlayerForm';

    /** @var ActiveRow */
    private $teamRow;

    /** @var ActiveRow */
    private $archRow;

    public function renderAll() {
        $this->template->teams = $this->teamsRepository->findByValue('archive_id', null)
                ->where('logo NOT', null);
        if ($this->user->isLoggedIn()) {
            $this->getComponent(self::ADD_FORM);
        }
    }

    public function actionView($id) {
        $this->teamRow = $this->teamsRepository->findById($id);
    }

    public function renderView($id) {
        if (!$this->teamRow) {
            throw new BadRequetsException(self::TEAM_NOT_FOUND);
        }

        $goalie = $this->playerTypesRepository->findByValue('type', self::GOALIE)->fetch();

        $this->template->players = $this->playersRepository->findByValue('team_id', $id)
                        ->where('type_id != ?', $goalie)->where('archive_id', null)
        ;
        $this->template->goalies = $this->playersRepository->findByValue('team_id', $id)
                        ->where('type_id', $goalie)->where('archive_id', null);
        $this->template->team = $this->teamRow;
        $this->template->i = 0;
        $this->template->j = 0;
        $this->template->goalie_title = self::GOALIE;
        $this->template->addPlayerForm = self::ADD_PLAYER_FORM;

        if ($this->user->isLoggedIn()) {
            $this->getComponent(self::EDIT_FORM)->setDefaults($this->teamRow);
            $this->getComponent(self::UPLOAD_FORM);
            $this->getComponent(self::REMOVE_FORM);
            $this->getComponent(self::ADD_PLAYER_FORM);
        }
    }

    public function actionArchAll($id) {
        $this->archRow = $this->archivesRepository->findById($id);
    }

    public function renderArchAll($id) {
        $this->template->teams = $this->teamsRepository->findByValue('archive_id', $id);
        $this->template->archive = $this->archRow;
    }

    public function actionArchView($id) {
        $this->archRow = $this->archivesRepository->findById($id);
    }

    public function renderArchView($id) {
        $this->template->teams = $this->teamsRepository->findByValue('archive_id', $id);
        $this->template->archive = $this->archRow;
    }

    protected function createComponentUploadForm() {
        $form = new Form;
        $form->addUpload('image', 'Nahrajte obrázok');
        $form->addSubmit('upload', 'Nastaviť obrázok');
        $form->onSuccess[] = [$this, self::SUBMITTED_UPLOAD_FORM];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentAddForm() {
        $form = new Form;
        $form->addText('name', 'Tím: ')
                ->setRequired('Názov tímu je povinné pole.')
                ->addRule(Form::MAX_LENGTH, "Dĺžka reťazce smie byť len 255 znakov.", 255);
        $form->addSubmit('save', 'Pridať');
        $form->onSuccess[] = [$this, self::SUBMITTED_ADD_FORM];
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
        $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentRemoveForm() {
        $form = new Form;
        $form->addSubmit('delete', 'Odstrániť')
                ->setAttribute('class', 'btn btn-large btn-danger');
        $form->addSubmit('cancel', 'Zrušiť')
                ->setAttribute('class', 'btn btn-large btn-warning')
                ->setAttribute('data-dismiss', 'modal');
        $form->addProtection(self::CSRF_TOKEN_EXPIRED);
        $form->onSuccess[] = [$this, self::SUBMITTED_REMOVE_FORM];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentAddPlayerForm() {
        $types = $this->playerTypesRepository->getTypes();
        $form = new Form;
        $form->addText('name', 'Meno a priezvisko');
        $form->addText('num', 'Číslo');
        $form->addSelect('type_id', 'Typ hráča', $types);
        $form->addCheckbox('trans', ' Prestupový hráč');
        $form->addSubmit('add', 'Pridať');
        $form->onSuccess[] = [$this, self::SUBMITTED_ADD_PLAYER_FORM];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedUploadForm(Form $form, $values) {
        $img = $values->image;

        if ($img->isOk() AND $img->isImage()) {
            $img_name = $img->getSanitizedName();
            $img->move(__DIR__ . self::IMG_FOLDER . '/' . $img_name);
            $data = array('image' => $img_name);
            $this->teamRow->update($data);
            $this->flashMessage('Obrázok bol pridaný', self::SUCCESS);
        } else {
            $this->flashMessage('Nastala chyba. Skúste znova', self::DANGER);
        }

        $this->redirect('view', $this->teamRow);
    }

    public function submittedRemoveForm() {
        $players = $this->teamRow->related('players');

        foreach ($players as $player) {
            $player->delete();
        }

        try {
            FileSystem::delete(__DIR__ . self::IMG_FOLDER . '/' . $this->teamRow->image);
            $this->flashMessage('Tím bol odstránený', 'success');
        } catch (IOException $e) {
            $this->flashMessage('Tím bol odstránený', self::SUCCESS);
            $this->flashMessage('Nepodarilo sa odstrániť foto tímu', self::DANGER);
        }

        $this->teamRow->delete();
        $this->redirect('all');
    }

    public function submittedAddForm(Form $form, $values) {
        $team = $this->teamsRepository->insert($values);
        $this->tablesRepository->insert(array('team_id' => $team));
        $this->flashMessage('Tím bol pridaný', self::SUCCESS);
        $this->redirect('all');
    }

    public function submittedEditForm(Form $form, $values) {
        $this->teamRow->update($values);
        $this->flashMessage('Tím bol upravený', self::SUCCESS);
        $this->redirect('view', $this->teamRow);
    }

}
