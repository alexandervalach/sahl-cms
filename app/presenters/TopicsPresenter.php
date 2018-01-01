<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Application\BadRequestException;

class TopicsPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $topicRow;

    /** @var string */
    private $addBtnLbl = "Pridať príspevok";

    /** @var string */
    private $error = "Topic not found!";

    public function renderAll() {
        $this->template->topics = $this->topicsRepository->findAll()->order("id DESC");
        $this->template->addBtnLbl = $this->addBtnLbl;
        $this['breadCrumb']->addLink("Fórum");

        if ($this->user->loggedIn) {
            $this->getComponent("addForm");
        }
    }

    public function actionDelete($id) {
        $this->userIsLogged();
        $this->forumRow = $this->forumRepository->findById($id);
    }

    public function renderDelete($id) {
        if (!$this->topicRow) {
            throw new BadRequestException($this->error);
        }
        $this->getComponent('deleteForm');
        $this->template->topic = $this->topicRow;
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
        $form->addSubmit('add', $this->addBtnLbl);
        $form->onSuccess[] = [$this, 'submittedAddForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedDeleteForm() {
        $this->forumRow->delete();
        $this->flashMessage('Téme bola odstránená', 'success');
        $this->redirect('all');
    }

    public function submittedAddForm(Form $form, $values) {
        if (isset($_POST['url']) && $_POST['url'] == '') {
            $values['created_at'] = date('Y-m-d H:i:s');
            $this->forumRepository->insert($values);
            $this->flashMessage('Téme bola vytvorená', 'success');
        }
        $this->redirect('all');
    }

    public function formCancelled() {
        $this->redirect('all');
    }

}
