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

    /** @var string */
    private $error = "Rule not found!";

    public function actionAll() {

    }

    public function renderAll() {
        $this->template->rules = $this->rulesRepository->findByValue('archive_id', null);

        $this['breadCrumb']->addLink("Pravidlá a smernice");
        
        if ($this->user->isLoggedIn()) {
            $this->getComponent('addRuleForm');
        }
    }

    public function actionDelete($id) {
        $this->userIsLogged();
        $this->ruleRow = $this->rulesRepository->findById($id);
    }

    public function renderDelete($id) {
        if (!$this->ruleRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->rule = $this->ruleRow;
        $this->getComponent('deleteForm');
    }

    public function actionEdit($id) {
        $this->userIsLogged();
        $this->ruleRow = $this->rulesRepository->findById($id);
    }

    public function renderEdit($id) {
        if (!$this->ruleRow) {
            throw new BadRequestException($this->error);
        }
        $this->getComponent('editRuleForm')->setDefaults($this->ruleRow);
    }

    public function actionArchView($id) {
        $this->archRow = $this->archiveRepository->findById($id);
    }

    public function renderArchView($id) {
        $this->template->rules = $this->rulesRepository->findByValue('archive_id', $id);
        $this->template->archive = $this->archRow;
        $this['breadCrumb']->addLink("Archív", $this->link("Archive:all"));
        $this['breadCrumb']->addLink($this->archRow->title, $this->link("Archive:view", $this->archRow));
        $this['breadCrumb']->addLink("Pravidlá a smernice");
    }

    protected function createComponentAddRuleForm() {
        $form = new Form;

        $form->addTextArea('rule', 'Text:')
                ->setAttribute('id', 'ckeditor')
                ->setRequired("Text je povinné pole.");

        $form->addSubmit('save', 'Uložiť');

        $form->onSuccess[] = $this->submittedAddRuleForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditRuleForm() {
        $form = new Form;
        $form->addTextArea('rule', 'Text:')
                ->setAttribute('id', 'ckeditor')
                ->setRequired("Text je povinné pole.");
        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = $this->submittedEditRuleForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedDeleteForm() {
        $this->userIsLogged();
        $this->ruleRow->delete();
        $this->flashMessage('Pravidlo zmazané.', 'success');
        $this->redirect('all');
    }

    public function submittedAddRuleForm(Form $form) {
        $this->userIsLogged();
        $values = $form->getValues();
        $this->rulesRepository->insert($values);
        $this->redirect('all');
    }

    public function submittedEditRuleForm(Form $form) {
        $this->userIsLogged();
        $values = $form->getValues();
        $this->ruleRow->update($values);
        $this->redirect('all');
    }

    public function formCancelled() {
        $this->redirect('all');
    }

}
