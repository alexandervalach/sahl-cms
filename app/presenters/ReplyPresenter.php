<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Application\BadRequestException;
use Nette\Database\Table\Selection;

class ReplyPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $forumRow;

    /** @var ActiveRow */
    private $replyRow;
    
    /** @var Selection */
    private $reply;

    /** @var string */
    private $error = "Reply not found!";

    public function actionAdd($id) {
        $this->forumRow = $this->forumRepository->findById($id);
        $this->reply = $this->forumRow->related('reply');
    }

    public function renderAdd($id) {
        if (!$this->forumRow) {
            throw new BadRequestException("Thread not found!");
        }
        $this->template->forum = $this->forumRow;
        $this->template->replies = $this->reply;
        $this->getComponent('addForm');
        $this['breadCrumb']->addLink("Fórum", $this->link("Forum:all"));
        $this['breadCrumb']->addLink($this->forumRow->title);
    }

    public function actionDelete($id) {
        $this->replyRow = $this->replyRepository->findById($id);
    }

    public function renderDelete($id) {
        if (!$this->replyRow) {
            throw new BadRequestException($this->error);
        }
        $this->getComponent('deleteForm');
        $this->template->reply = $this->replyRow;
    }

    protected function createComponentAddForm() {
        $form = new Form;
        $form->addText('author', 'Meno')
             ->setRequired("Meno je povinné pole")
             ->addRule(Form::MAX_LENGTH, "Maximálna dĺžka mena je 50 znakov", 50);
        $form->addText('url', 'Nevypĺňať')
             ->setAttribute('class', 'url_address')
             ->setOmitted();
        $form->addTextArea('text', 'Text')
             ->setAttribute('class', 'form-control');
        $form->addSubmit('save', 'Pridať');

        $form->onSuccess[] = $this->submittedAddForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddForm(Form $form) {
        if (isset($_POST['url']) && $_POST['url'] == '') {
            $values = $form->getValues();
            $values['forum_id'] = $this->forumRow;
            $this->replyRepository->insert($values);
        }
        $this->redirect('add', $this->forumRow);
    }
    
    public function submittedDeleteForm() {
        $this->userIsLogged();
        $this->replyRow->delete();
        $this->redirect('add', $this->replyRow->forum_id);
    }

    public function formCancelled() {
        $this->redirect('add', $this->replyRow->forum_id);
    }

}
