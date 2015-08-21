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
    private $error = "Message not found!";

    public function actionAll() {
        
    }

    public function renderAll() {
        $forumSelection = $this->forumRepository->findAll()->order("id DESC");
        
        $visualPaginator = $this->getComponent('visualPaginator');
        $paginator = $visualPaginator->getPaginator();
        $paginator->itemsPerPage = 10;
        $paginator->itemCount = $forumSelection->count();
        $forumSelection->limit($paginator->itemsPerPage, $paginator->offset);
        
        $this->template->forums = $forumSelection;
        $this->template->default = '/images/forum.png';
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

    protected function createComponentAddMessageForm() {
        $form = new Form;

        $form->addText('title', 'Názov novej témy:')
                ->addRule(Form::FILLED, 'Názov je povinné pole.');
        $form->addText('author', 'Meno:')
                ->setRequired("Meno je povinné pole.");
        $form->addTextArea('message', 'Príspevok:')
                ->setAttribute('class', 'form-jqte')
                ->setRequired("Príspevok je povinné pole.");
        $form->addSubmit('add', 'Pridaj novú tému');

        $form->onSuccess[] = $this->submittedAddMessageForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }
    
    protected function createComponentVisualPaginator() {
        $control = new VisualPaginator\Control;
        $control->setTemplateFile('bootstrap.latte');
        $control->disableAjax();
        return $control;
    }

    public function submittedDeleteForm() {
        $this->userIsLogged();
        $this->forumRow->delete();
        $this->flashMessage('Príspevok zmazaný.', 'success');
        $this->redirect('all');
    }

    public function submittedAddMessageForm(Form $form) {
        $values = $form->getValues();
        $values['created_at'] = date('Y-m-d H:i:s');
        $this->forumRepository->insert($values);
        $this->redirect('all');
    }

    public function formCancelled() {
        $this->redirect('all');
    }

}
