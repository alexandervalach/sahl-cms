<?php

namespace App\Presenters;

use App\FormHelper;
use App\Model\TablesRepository;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;

class TablesPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $tableRow;

    /** @var string */
    private $error = "Row not found!";

    public function actionAll() {
        
    }

    public function renderAll() {
        $this->template->basic = $this->tablesRepository->findByValue('type', 2)->where('archive_id', null)->order('points DESC')->order('score1 - score2 DESC');
        $this->template->playoff = $this->tablesRepository->findByValue('type', 1)->where('archive_id', null)->order('points DESC')->order('score1 - score2 DESC');
        $this->template->options = $this->optionsRepository->findByValue('visible', 1);
    }

    public function actionAdd() {
        $this->userIsLogged();
    }

    public function renderAdd() {
        $this->getComponent('addTableRowForm');
    }

    public function actionEdit($id) {
        $this->userIsLogged();
        $this->tableRow = $this->tablesRepository->findById($id);
    }

    public function renderEdit($id) {
        if (!$this->tableRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->team = $this->tableRow;
        $this->getComponent('editTableRowForm')->setDefaults($this->tableRow);
    }

    public function actionDelete($id) {
        $this->userIsLogged();
        $this->tableRow = $this->tablesRepository->findById($id);
    }

    public function renderDelete($id) {
        if (!$this->tableRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->table = $this->tableRow;
        $this->getComponent('deleteForm');
    }

    public function actionAddToSidebar($id) {
        $this->userIsLogged();
        $this->tableRow = $this->tablesRepository->findById($id);
    }

    public function renderAddToSidebar($id) {
        if (!$this->tableRow) {
            throw new BadRequestException($this->error);
        }
        $this->getComponent('addToSidebarForm');
    }

    public function actionArchive($id) {

    }

    public function renderArchive($id) {
        $this->template->basic = $this->tablesRepository->findByValue('type', 2)->where('archive_id', $id)->order('points DESC')->order('score1 - score2 DESC');
        $this->template->playoff = $this->tablesRepository->findByValue('type', 1)->where('archive_id', $id)->order('points DESC')->order('score1 - score2 DESC');
        $this->template->options = $this->optionsRepository->findByValue('visible', 1);
        $this->template->archive = $this->archiveRepository->findById($id);
    }

    protected function createComponentAddTableRowForm() {
        $form = new Form;
        $teams = $this->teamsRepository->getTeams();
        $form->addSelect('team_id', 'Mužstvo', $teams);
        $form->addSelect('type', 'Tabuľka', TablesRepository::$TABLES);
        $form->addSubmit('save', 'Uložiť');

        $form->onSuccess[] = $this->submittedAddTableRowForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditTableRowForm() {
        $form = new Form;

        $form->addText('win', 'Výhry');
        $form->addText('tram', 'Remízy');
        $form->addText('lost', 'Prehry');
        $form->addText('score1', 'Skóre 1');
        $form->addText('score2', 'Skóre 2');
        $form->addText('points', 'Body');
        $form->addSelect('type', 'Tabuľka', TablesRepository::$TABLES);
        $form->addSubmit('save', 'Uložiť');

        $form->onSuccess[] = $this->submittedEditTableRowForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddTableRowForm(Form $form) {
        $values = $form->getValues();
        $this->tablesRepository->insert($values);
        $this->redirect('all#nav');
    }

    public function submittedEditTableRowForm(Form $form) {
        $values = $form->getValues();
        $values['counter'] = $values['lost'] + $values['tram'] + $values['win'];
        $this->tableRow->update($values);
        $this->redirect('all#nav');
    }

    public function submittedDeleteForm() {
        $this->userIsLogged();
        $this->tableRow->delete();
        $this->flashMessage('Záznam zmazaný!', 'success');
        $this->redirect('all#nav');
    }

    public function formCancelled() {
        $this->redirect('all#nav');
    }

}
