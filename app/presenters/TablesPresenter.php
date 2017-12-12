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
        $table_types = $this->tableTypesRepository->findByValue('visible', 1);
        $table_rows = array();

        foreach ($table_types as $type) {
            $table_rows[$type->name] = $this->tablesRepository->findByValue('archive_id', null)
                                                              ->where('type = ?', $type)
                                                              ->order('points DESC');
        }
        
        $this->template->tables = $table_rows;
        $this->template->table_types = $table_types;
        $this['breadCrumb']->addLink("Tabuľky");

        if ($this->user->isLoggedIn()) {
            $this->getComponent("addForm");
        }
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

    public function actionArchView($id) {

    }

    public function renderArchView($id) {
        $this->template->basic = $this->tablesRepository->findByValue('type', 2)->where('archive_id', $id)->order('points DESC')->order('score1 - score2 DESC');
        $this->template->playoff = $this->tablesRepository->findByValue('type', 1)->where('archive_id', $id)->order('points DESC')->order('score1 - score2 DESC');
        $this->template->options = $this->optionsRepository->findByValue('visible', 1);
        $this->template->archive = $this->archiveRepository->findById($id);
    }

    protected function createComponentAddForm() {
        $form = new Form;
        $teams = $this->teamsRepository->getTeams();
        $table_types = $this->tableTypesRepository->getTypes();

        $form->addSelect('team_id', 'Mužstvo', $teams);
        $form->addSelect('type', 'Tabuľka', $table_types);
        $form->addSubmit('save', 'Uložiť');

        $form->onSuccess[] = $this->submittedAddForm;
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
        $form->addSubmit('save', 'Uložiť');

        $form->onSuccess[] = $this->submittedEditTableRowForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddForm(Form $form) {
        $values = $form->getValues();
        $this->tablesRepository->insert($values);
        $this->flashMessage('Záznam pridaný do tabuliek', 'success');
        $this->redirect('all');
    }

    public function submittedEditTableRowForm(Form $form) {
        $values = $form->getValues();
        $values['counter'] = $values['lost'] + $values['tram'] + $values['win'];
        $this->tableRow->update($values);
        $this->redirect('all');
    }

    public function submittedDeleteForm() {
        $this->userIsLogged();
        $this->tableRow->delete();
        $this->flashMessage('Záznam odstránený z tabuľky', 'success');
        $this->redirect('all');
    }

    public function formCancelled() {
        $this->redirect('all');
    }

}
