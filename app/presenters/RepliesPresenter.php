<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Application\BadRequestException;

class RepliesPresenter extends BasePresenter {

    const REPLY_NOT_FOUND = 'Reply not found';

    /** @var ActiveRow */
    private $topicRow;

    /** @var ActiveRow */
    private $replyRow;

    public function actionAdd($id) {
        $this->topicRow = $this->topicsRepository->findById($id);
    }

    public function renderAdd($id) {
        if (!$this->topicRow) {
            throw new BadRequestException(self::TOPIC_NOT_FOUND);
        }
        $this->template->topic = $this->topicRow;
        $this->template->replies = $this->topicRow->related('replies');
        $this->getComponent(self::ADD_FORM);
    }

    public function actionRemove($id) {
        $this->replyRow = $this->repliesRepository->findById($id);
    }

    public function renderRemove($id) {
        if (!$this->replyRow) {
            throw new BadRequestException(self::REPLY_NOT_FOUND);
        }
        $this->getComponent(self::REMOVE_FORM);
        $this->template->reply = $this->replyRow;
    }

    protected function createComponentAddForm() {
        $form = new Form;
        $form->addText('author', 'Meno')
             ->setRequired("Meno je povinné pole")
             ->addRule(Form::MAX_LENGTH, "Maximálna dĺžka mena je 50 znakov", 50);
        $form->addText('url', 'Nevypĺňať')
             ->setAttribute('style', 'display: none')
             ->setOmitted();
        $form->addTextArea('text', 'Text')
             ->setAttribute('class', 'form-control');
        $form->addSubmit('add', 'Reagovať');
        $form->onSuccess[] = [$this, self::SUBMITTED_ADD_FORM];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddForm(Form $form, $values) {
        $url = filter_input(INPUT_POST, 'url');

        if (isset($url) && $url == '') {
            $values['topic_id'] = $this->topicRow;
            $this->repliesRepository->insert($values);
            $this->flashMessage('Ďakujeme, vaša odpoveď bola pridaná.', self::SUCCESS);
        } else {
            $this->flashMessage('Opa, vyplnili ste aj políčko Nevypĺňať', self::SUCCESS);
        }
        $this->redirect('add', $this->topicRow);
    }
    
    public function submittedRemoveForm() {
        $topic_id = $this->replyRow->topic_id;
        $this->replyRow->delete();
        $this->redirect('add', $topic_id);
    }

    public function formCancelled() {
        $this->redirect('add', $this->replyRow->topic_id);
    }

}
