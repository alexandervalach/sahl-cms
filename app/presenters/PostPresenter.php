<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;

class PostPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $postRow;

    /** @var string */
    private $error = "Post not found!";

    public function actionShow($id) {
        $this->postRow = $this->postsRepository->findById($id);
    }

    public function renderShow($id) {
        if (!$this->postRow) {
            throw new BadRequestException($this->error);
        }
        $post = $this->postRow;
        $this->template->post = $post;
        $this->template->images = $post->related('images')->order('id DESC');
        $this->template->imgFolder = $this->imgFolder;
    }

    public function actionCreate() {
        $this->userIsLogged();
    }

    public function renderCreate() {
        $this->getComponent('addPostForm');
    }

    public function actionEdit($id) {
        $this->userIsLogged();
        $this->postRow = $this->postsRepository->findById($id);
    }

    public function renderEdit($id) {
        $post = $this->postRow;
        if (!$post) {
            throw new BadRequestException($this->error);
        }
        $this->template->post = $post;
        $this->getComponent('editPostForm')->setDefaults($post);
    }

    public function actionDelete($id) {
        $this->userIsLogged();
        $this->postRow = $this->postsRepository->findById($id);
    }

    public function renderDelete($id) {
        if (!$this->postRow) {
            throw new BadRequestException($this->error);
        }
        $this->getComponent('deleteForm');
        $this->template->post = $this->postRow;
    }

    protected function createComponentAddPostForm() {
        $form = new Form;

        $form->addText('title', 'Názov:')
                ->setRequired("Názov je povinné pole.");

        $form->addTextArea('content', 'Obsah:')
                ->setAttribute('class', 'form-jqte')
                ->setRequired("Obsah príspevku je povinné pole.");

        $form->addSubmit('save', 'Uložiť');

        $form->onSuccess[] = $this->submittedAddPostForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditPostForm() {
        $form = new Form;

        $form->addText('title', 'Názov:')
                ->setRequired("Názov je povinné pole.");

        $form->addTextArea('content', 'Obsah:')
                ->setAttribute('class', 'form-jqte')
                ->setRequired("Obsah príspevku je povinné pole.");

        $form->addSubmit('save', 'Uložiť');

        $form->onSuccess[] = $this->submittedEditPostForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddPostForm(Form $form) {
        $this->userIsLogged();
        $post = $this->postsRepository;
        $values = $form->getValues();
        $id = $post->insert($values);
        $this->redirect('show', $id);
    }

    public function submittedEditPostForm(Form $form) {
        $this->userIsLogged();
        $post = $this->postRow;
        $values = $form->getValues();
        $post->update($values);
        $this->redirect('show', $post->id);
    }

    public function submittedDeleteForm() {
        $this->userIsLogged();
        $this->postRow->delete();
        $this->redirect('Homepage:');
    }

    public function formCancelled() {
        $this->redirect('Homepage:');
    }

}
