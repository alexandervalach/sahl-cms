<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;

class TablesPresenter extends BasePresenter {

    const TABLE_NOT_FOUND = 'Table not found';

    /** @var ActiveRow */
    private $tableRow;

    /** @var ActiveRow */
    private $archRow;

    public function renderAll() {
        $tableTypes = $this->tableTypesRepository->findByValue('visible', 1);
        $tableRows = array();

        foreach ($tableTypes as $type) {
            $tableRows[$type->name] = $this->tablesRepository->getArchived()->order('points DESC, (score1 - score2) DESC');
        }

        $this->template->tables = $tableRows;
        $this->template->tableTypes = $tableTypes;
    }

    public function actionAddToSidebar($id) {
        $this->userIsLogged();
        $this->tableRow = $this->tablesRepository->findById($id);

        if (!$this->tableRow) {
            throw new BadRequestException(self::TABLE_ROW_NOT_FOUND);
        }

        $this->submittedSetVisible();
    }

    public function actionArchAll($id) {
        $this->archRow = $this->seasonsRepository->findById($id);
    }

    public function renderArchAll($id) {
        $tableTypes = $this->tableTypesRepository->findAll();
        $tableRows = array();

        foreach ($tableTypes as $type) {
            $tableRows[$type->name] = $this->tablesRepository
                    ->findByValue('archive_id', $this->archRow)
                    ->where('type = ?', $type)
                    ->order('points DESC, (score1 - score2) DESC');
        }

        $this->template->tables = $tableRows;
        $this->template->tableTypes = $tableTypes;
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
        $form->addSubmit('edit', 'Upraviť');
        $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentResetForm() {
        $form = new Form;
        $form->addSubmit('reset', 'Vynulovať')
             ->setAttribute('class', self::BTN_DANGER)
             ->onClick[] = $this->submittedResetForm;
        $form->addSubmit('cancel', 'Zrušiť')
             ->setAttribute('class', self::BTN_WARNING)
             ->setAttribute('data-dismiss', 'modal');
        $form->addProtection();
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedEditForm(Form $form, $values) {
        $values['counter'] = $values['lost'] + $values['tram'] + $values['win'];
        $this->tableRow->update($values);
        $this->flashMessage('Záznam bol upravený', self::SUCCESS);
        $this->redirect('all');
    }

    public function submittedRemoveForm() {
        $this->tableRow->delete();
        $this->flashMessage('Záznam bol odstránený', self::SUCCESS);
        $this->redirect('all');
    }

    public function submittedSetVisible() {
        $this->tableRow->update(array('visible' => 1));
        $this->flashMessage('Tabuľka bola pridaná na domovskú stránku', self::SUCCESS);
        $this->redirect('all');
    }

    public function submittedResetForm() {
        $rows = $this->tablesRepository->getArchived();

        $values = array(
            'counter' => 0,
            'win' => 0,
            'tram' => 0,
            'lost' => 0,
            'score1' => 0,
            'score2' => 0,
            'points' => 0
        );

        foreach ($rows as $row) {
            $row->update($values);
        }

        $this->redirect('all');
    }

    public function formCancelled() {
        $this->redirect('all');
    }

}
