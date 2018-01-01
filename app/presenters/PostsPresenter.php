<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\FileSystem;

class PostsPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $postRow;

    /** @var string */
    private $error = "Post not found";

    public function actionView($id) {
        $this->postRow = $this->postsRepository->findById($id);
    }

    public function renderView($id) {
        if (!$this->postRow) {
            throw new BadRequestException($this->error);
        }

        $this->template->post = $this->postRow;
        $this->template->images = $this->postRow->related('images')->order('id DESC');
        $this->template->imgFolder = $this->imgFolder;
        $this->template->default_img = $this->default_img;
        $this['breadCrumb']->addLink($this->postRow->title);

        if ($this->user->isLoggedIn()) {
            $this->getComponent("editForm")->setDefaults($this->postRow);
            $this->getComponent("removeForm");
        }
    }

    public function actionAdd() {
        $this->userIsLogged();
    }

    public function renderAdd() {
        $this->getComponent('addForm');
    }

    protected function createComponentAddForm() {
        $form = new Form;
        $form->addText('title', 'Názov:')
             ->setRequired("Názov je povinné pole.");
        $form->addTextArea('content', 'Obsah:')
             ->setAttribute('id', 'ckeditor');
        $form->addSubmit('add', 'Pridať');
        $form->onSuccess[] = [$this, 'submittedAddForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditForm() {
        $form = new Form;
        $form->addText('title', 'Názov:')
             ->setRequired("Názov je povinné pole.");
        $form->addTextArea('content', 'Obsah:')
             ->setAttribute('id', 'ckeditor');
        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = [$this, 'submittedEditForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentRemoveForm() {
        $form = new Form;
        $form->addSubmit('delete', 'Odstrániť')
             ->setAttribute('class', 'btn btn-large btn-danger');
        $form->addSubmit('cancel', 'Zrušiť')
             ->setAttribute('class', 'btn btn-large btn-warning')
             ->setAttribute('data-dismiss', 'modal');
        $form->addProtection();
        $form->onSuccess[] = [$this, 'submittedRemoveForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddForm(Form $form, $values) {
        $post = $this->postsRepository->insert($values);
        $this->flashMessage('Príspevok bol pridaný', 'success');
        $this->redirect('view', $post);
    }

    public function submittedEditForm(Form $form, $values) {
        $this->postRow->update($values);
        $this->flashMessage('Príspevok bol upravený', 'success');
        $this->redirect('view', $this->postRow);
    }

    public function submittedRemoveForm() {
        $imgs = $this->postRow->related('images');
        
        foreach ($imgs as $img) {
            FileSystem::delete($this->imgFolder . '/' .$img->name);
            $img->delete();
        }

        $this->postRow->delete();
        $this->flashMessage('Príspevok bol odstránený', 'success');
        $this->redirect('Homepage:');
    }

}
