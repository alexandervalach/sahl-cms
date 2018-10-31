<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;

class TableTypesPresenter extends BasePresenter {

    const TABLE_TYPE_NOT_FOUND = '';

    /** @var ActiveRow */
    private $tableTypeRow;

    public function renderAll() {
        $this->userIsLogged();
        $this->template->types = $this->tableTypesRepository->findAll();
        $this->getComponent(self::ADD_FORM);
    }

    /**
     * @param integer $id
     */
    public function actionShow($id) {
        $this->userIsLogged();
        $this->tableTypeRow = $this->tableTypesRepository->findById($id);

        if (!$this->tableTypeRow) {
            throw BadRequestException(self::TABLE_TYPE_NOT_FOUND);
        }

        $this->submittedShowTable();
    }

    public function submittedShowTable() {
        $this->tableTypeRow->update(array('visible' => 1));
        $this->flashMessage('Tabuľka je viditeľná', self::SUCCESS);
        $this->redirect('all');
    }

    /**
     * @param integer $id
     */
    public function actionHide($id) {
        $this->userIsLogged();
        $this->tableTypeRow = $this->tableTypesRepository->findById($id);

        if (!$this->tableTypeRow) {
            throw BadRequestException(self::TABLE_TYPE_NOT_FOUND);
        }

        $this->submittedHideTable();
    }

    public function submittedHideTable() {
        $this->tableTypeRow->update(array('visible' => 0));
        $this->flashMessage('Tabuľka je skrytá pre verejnosť', self::SUCCESS);
        $this->redirect('all');
    }

    /**
     * Creates add table types form
     * @return Nette\Application\UI\Form
     */
    protected function createComponentAddForm() {
        $form = new Form;
        $form->addText('name', 'Názov')
             ->addRule(Form::FILLED, 'Ešte treba vyplniť názov')
             ->setAttribute('placeholder', 'Play Off');
        $form->addCheckbox('visible', ' Chcem, aby sa tabuľka zobrazila na stránke');
        $form->addSubmit('save', 'Uložiť');
        $form->addSubmit('cancel', 'Zrušiť')
             ->setAttribute('class', self::BTN_WARNING)
             ->setAttribute('data-dismiss', 'modal');
        $form->onSuccess[] = [$this, self::SUBMITTED_ADD_FORM];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    /**
     * Submitting data from add form
     * @param Form $form
     * @param array $values
     */
    public function submittedAddForm(Form $form, $values) {
        $this->tableTypesRepository->insert($values);
        $this->flashMessage('Typ tabuľky bol pridaný', self::SUCCESS);
        $this->redirect('all');
    }

}
