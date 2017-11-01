<?php

namespace App\Presenters;

use App\FormHelper;
use IPub\VisualPaginator\Components as VisualPaginator;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Application\BadRequestException;

class ForumPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $forumRow;

    /** @var string */
    private $error = "Thread not found!";

    public function actionAll() {
        
    }

    public function renderAll() {
        $forumSelection = $this->forumRepository->findAll()->order("id DESC");
        $this->getComponent("addForm");
        $this->template->forums = $forumSelection;
        $this['breadCrumb']->addLink("Fórum");
    }

    public function actionDelete($id) {
        $this->userIsLogged();
        $this->forumRow = $this->forumRepository->findById($id);
    }

    public function renderDelete($id) {
        if (!$this->forumRow) {
            throw new BadRequestException($this->error);
        }
        $this->getComponent('deleteForm');
        $this->template->forum = $this->forumRow;
    }

    protected function createComponentAddForm() {
        $form = new Form;

        $form->addText('title', 'Názov novej témy:')
             ->addRule(Form::FILLED, 'Názov je povinné pole.');
        $form->addText('author', 'Meno:')
             ->setRequired("Meno je povinné pole.");
        $form->addText('url', 'Nevypĺňať')
             ->setOmitted();
        $form->addTextArea('message', 'Príspevok:')
             ->setAttribute('class', 'form-control');
        $form->addSubmit('add', 'Uložiť');

        $form->onSuccess[] = $this->submittedAddForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedDeleteForm() {
        $this->userIsLogged();
        $this->forumRow->delete();
        $this->flashMessage('Príspevok zmazaný.', 'success');
        $this->redirect('all');
    }

    public function submittedAddForm(Form $form) {
        if (isset($_POST['url']) && $_POST['url'] == '') {
            $values = $form->getValues();
            $values['created_at'] = date('Y-m-d H:i:s');
            $this->forumRepository->insert($values);
        }
        $this->redirect('all');
    }

    public function formCancelled() {
        $this->redirect('all');
    }

}
