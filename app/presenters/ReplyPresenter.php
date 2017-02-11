<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Application\BadRequestException;

class ReplyPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $forumRow;

    /** @var ActiveRow */
    private $replyRow;
    
    /** @var Nette\Database\Table\Selection */
    private $reply;

    /** @var string */
    private $error = "Reply not found!";

    public function actionAdd($id) {
        $this->forumRow = $this->forumRepository->findById($id);
        $this->reply = $this->forumRow->related('reply');
    }

    public function renderAdd($id) {
        if (!$this->forumRow) {
            throw new BadRequestException("Message not found!");
        }
        $this->template->forum = $this->forumRow;
        $this->template->replies = $this->reply;
        $this->getComponent('addForm');
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
        $form->addText('email', 'Nevypĺňať')
             ->setAttribute('class', 'sender-email-address')
             ->setOmitted();
        $form->addTextArea('text', 'Text')
             ->setAttribute('id', 'ckeditor');
        $form->addSubmit('add', 'Pridaj');
        $form->onSuccess[] = $this->submittedAddForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddForm(Form $form) {
        if (isset($_POST['url']) && $_POST['url'] == '') {
            $id = $this->forumRow->id;
            $values = $form->getValues();
            $values['forum_id'] = $id;
            $this->replyRepository->insert($values);
            $this->redirect('add#nav', $id);
        }
        return false;
    }
    
    public function submittedDeleteForm() {
        $this->userIsLogged();
        $this->replyRow->delete();
        $this->redirect('add#nav', $this->replyRow->forum_id);
    }

    public function formCancelled() {
        $this->redirect('add#nav', $this->replyRow->forum_id);
    }

}
