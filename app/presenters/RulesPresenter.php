<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;

class RulesPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $ruleRow;

    /** @var ActiveRow */
    private $archRow;

    public function actionAll() {
        $this->ruleRow = $this->rulesRepository->findByValue('archive_id', null)->fetch();
    }

    public function renderAll() {
        if (!$this->ruleRow) {
            throw new BadRequestException(self::RULE_NOT_FOUND);
        }
        $this->template->rule = $this->ruleRow;
        if ($this->user->isLoggedIn()) {
            $this->getComponent(self::EDIT_FORM)->setDefaults($this->ruleRow);
        }
    }

    public function actionArchView($id) {
        $this->archRow = $this->archivesRepository->findById($id);
    }

    public function renderArchView($id) {
        $this->template->rules = $this->rulesRepository->findByValue('archive_id', $id);
        $this->template->archive = $this->archRow;
    }

    protected function createComponentEditForm() {
        $form = new Form;
        $form->addTextArea('rule', 'Text')
                ->setAttribute('id', 'ckeditor');
        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedEditForm(Form $form, $values) {
        $this->ruleRow->update($values);
        $this->flashMessage('Pravidlá a smernice boli upravené', self::SUCCESS);
        $this->redirect('all');
    }

}
