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
    private $error = "Rule not found";

    public function actionAll() {
        $this->ruleRow = $this->rulesRepository->findByValue('archive_id', null)->fetch();
    }

    public function renderAll() {
        if (!$this->ruleRow) {
            throw new BadRequestException($this->error);
        }

        $this->template->rule = $this->ruleRow;
        $this['breadCrumb']->addLink("Pravidlá a smernice");
        
        if ($this->user->isLoggedIn()) {
            $this->getComponent('editForm')->setDefaults($this->ruleRow);
        }
    }

    public function actionArchView($id) {
        $this->archRow = $this->archivesRepository->findById($id);
    }

    public function renderArchView($id) {
        $this->template->rules = $this->rulesRepository->findByValue('archive_id', $id);
        $this->template->archive = $this->archRow;
        $this['breadCrumb']->addLink("Archív", $this->link("Archives:all"));
        $this['breadCrumb']->addLink($this->archRow->title, $this->link("Archives:view", $this->archRow));
        $this['breadCrumb']->addLink("Pravidlá a smernice");
    }

    protected function createComponentEditForm() {
        $form = new Form;
        $form->addTextArea('rule', 'Text')
             ->setAttribute('id', 'ckeditor')
             ->setRequired("Text je povinné pole.");
        $form->addSubmit('save', 'Uložiť')
             ->setAttribute('class', 'btn btn-large btn-success');
        $form->onSuccess[] = [$this, 'submittedEditForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedEditForm(Form $form, $values) {
        $this->flashMessage('Pravidlá a smernice boli upravené', 'success');
        $this->ruleRow->update($values);
        $this->redirect('all');
    }

    public function formCancelled() {
        $this->redirect('all');
    }

}
