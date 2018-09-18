<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Application\BadRequestException;

class TopicsPresenter extends BasePresenter {

    const ADD_BTN_LABEL = 'Pridať príspevok';

    /** @var ActiveRow */
    private $topicRow;

    public function renderAll() {
        $this->template->topics = $this->topicsRepository->findAll()->order("id DESC");
        $this->template->addBtnLbl = self::ADD_BTN_LABEL;

        if ($this->user->loggedIn) {
            $this->getComponent(self::ADD_FORM);
        }
    }

    public function actionRemove($id) {
        $this->userIsLogged();
        $this->forumRow = $this->topicsRepository->findById($id);
    }

    public function renderRemove($id) {
        if (!$this->topicRow) {
            throw new BadRequestException(self::TOPIC_NOT_FOUND);
        }
        $this->getComponent(self::REMOVE_FORM);
        $this->template->topic = $this->topicRow;
    }

    protected function createComponentAddForm() {
        $form = new Form;
        $form->addText('title', 'Názov novej témy:')
                ->addRule(Form::FILLED, 'Názov je povinné pole.');
        $form->addText('author', 'Meno:')
                ->setRequired("Meno je povinné pole.");
        $form->addText('url', 'Nevypĺňať')
                ->setAttribute('style', 'display: none')
                ->setOmitted();
        $form->addTextArea('message', 'Príspevok:')
                ->setAttribute('class', 'form-control');
        $form->addSubmit('add', self::ADD_BTN_LABEL);
        $form->onSuccess[] = [$this, self::SUBMITTED_ADD_FORM];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedRemoveForm() {
        $this->forumRow->delete();
        $this->flashMessage('Téme bola odstránená', self::SUCCESS);
        $this->redirect('all');
    }

    public function submittedAddForm(Form $form, $values) {
        $url = filter_input(INPUT_POST, 'url');

        if (isset($url) && $url == '') {
            $values['created_at'] = date('Y-m-d H:i:s');
            $this->topicsRepository->insert($values);
            $this->flashMessage('Téme bola vytvorená', self::SUCCESS);
        }
        $this->redirect('all');
    }

    public function formCancelled() {
        $this->redirect('all');
    }

}
