<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Application\BadRequestException;

class RepliesPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $topicRow;

    /** @var ActiveRow */
    private $replyRow;

    /** @var string */
    private $error = "Reply not found";

    public function actionAdd($id) {
        $this->topicRow = $this->topicsRepository->findById($id);
    }

    public function renderAdd($id) {
        if (!$this->topicRow) {
            throw new BadRequestException("Topic not found");
        }
        $this->template->topic = $this->topicRow;
        $this->template->replies = $this->topicRow->related('replies');
        $this->getComponent('addForm');
    }

    public function actionDelete($id) {
        $this->replyRow = $this->repliesRepository->findById($id);
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
             ->setAttribute('class', 'url-address')
             ->setOmitted();
        $form->addTextArea('content', 'Text')
             ->setAttribute('class', 'form-control');
        $form->addSubmit('add', 'Reagovať');
        $form->onSuccess[] = [$this, 'submittedAddForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddForm(Form $form, $values) {
        if (isset($_POST['url']) && $_POST['url'] == '') {
            $values['topic_id'] = $this->topicRow;
            $this->repliesRepository->insert($values);
            $this->flashMessage('Ďakujeme, vaša odpoveď bola pridaná.', 'success');
        } else {
            $this->flashMessage('Opa, vyplnili ste aj políčko Nevypĺňať', 'success');
        }
        $this->redirect('add', $this->topicRow);
    }
    
    public function submittedDeleteForm() {
        $topic_id = $this->replyRow->topic_id;
        $this->replyRow->delete();
        $this->redirect('add', $topic_id);
    }

    public function formCancelled() {
        $this->redirect('add', $this->replyRow->topic_id);
    }

}
