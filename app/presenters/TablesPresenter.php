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

    /** @var ActiveRow */
    private $archRow;

    /** @var string */
    private $error = "Table row not found";

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
    }

    public function actionAddToSidebar($id) {
        $this->userIsLogged();
        $this->tableRow = $this->tablesRepository->findById($id);

        if (!$this->tableRow) {
            throw new BadRequestException($this->error);
        }

        $this->submittedSetVisible();
    }

    public function actionArchAll($id) {
        $this->archRow = $this->archivesRepository->findById($id);
    }

    public function renderArchAll($id) {
        $table_types = $this->tableTypesRepository->findAll();
        $table_rows = array();

        foreach ($table_types as $type) {
            $table_rows[$type->name] = $this->tablesRepository->findByValue('archive_id', $this->archRow)
                                                              ->where('type = ?', $type)
                                                              ->order('points DESC, (score1 - score2) DESC');
        }
        
        $this->template->tables = $table_rows;
        $this->template->table_types = $table_types;
        $this->template->archive = $this->archRow;
    }

    protected function createComponentEditForm() {
        $form = new Form;
        $form->addText('win', 'Výhry');
        $form->addText('tram', 'Remízy');
        $form->addText('lost', 'Prehry');
        $form->addText('score1', 'Skóre 1');
        $form->addText('score2', 'Skóre 2');
        $form->addText('points', 'Body');
        $form->addSubmit('edit', 'Upraviť')
             ->setAttribute('class', 'btn btn-large btn-success');
        $form->onSuccess[] = [$this, 'submittedEditForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedEditForm(Form $form, $values) {
        $values['counter'] = $values['lost'] + $values['tram'] + $values['win'];
        $this->tableRow->update($values);
        $this->flashMessage('Záznam bol upravený', 'success');
        $this->redirect('all');
    }

    public function submittedDeleteForm() {
        $this->tableRow->delete();
        $this->flashMessage('Záznam bol odstránený', 'success');
        $this->redirect('all');
    }

    public function submittedSetVisible() {
        $this->tableRow->update(array('visible' => 1));
        $this->flashMessage('Tabuľka bola pridaná na domovskú stránku', 'success');
        $this->redirect('all');
    }

    public function formCancelled() {
        $this->redirect('all');
    }

}
