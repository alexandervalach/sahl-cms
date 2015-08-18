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

    /** @var string */
    private $error = "Reply not found!";

    public function actionAdd($id) {
        $this->userIsLogged();
        $this->forumRow = $this->forumRepository->findById($id);
    }

    public function renderAdd($id) {
        if (!$this->forumRow) {
            throw new BadRequestException("Message not found!");
        }
        $this->template->forum = $this->forumRow;
        $this->getComponent('addForm');
    }

    public function actionDelete($id) {
        
    }

    public function renderDelete($id) {
        
    }

    protected function createComponentAddForm() {
        $form = new Form;
        $form->addText('author', 'Meno')
                ->setRequired("Meno je povinné pole")
                ->addRule(Form::MAX_LENGTH, "Maximálna dĺžka mena je 50 znakov", 50);
        $form->addTextArea('text', 'Text')
                ->setAttribute('class', 'form-jqte');
        $form->addSubmit('add', 'Pridaj');
        $form->onSuccess[] = $this->submittedAddForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddForm(Form $form) {
        $values = $form->getValues();
        $values['forum_id'] = $this->forumRow->id;
        $this->replyRepository->insert($values);
        $this->redirect('Forum:all');
    }

    public function formCancelled() {
        $this->redirect('Forum:all');
    }

}
