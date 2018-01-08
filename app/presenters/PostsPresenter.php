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

    /** @var ActiveRow */
    private $imgRow;

    /** @var string */
    private $error = "Post not found";

    public function renderAll() {
        $this->template->posts = $this->postsRepository->findAll()->order('id DESC');
        $this->template->default = $this->default_img;
        $this->template->imgFolder = $this->imgFolder;

        if ($this->user->isLoggedIn()) {
            $this->getComponent('addForm');
        }
    }

    public function actionView($id) {
        $this->postRow = $this->postsRepository->findById($id);
    }

    public function renderView($id) {
        if (!$this->postRow) {
            throw new BadRequestException($this->error);
        }

        $this->template->post = $this->postRow;
        $this->template->images = $this->postRow->related('postImages')->order('id DESC');
        $this->template->imgFolder = $this->imgFolder;
        $this->template->default_img = $this->default_img;

        if ($this->user->isLoggedIn()) {
            $this->getComponent('editForm')->setDefaults($this->postRow);
            $this->getComponent('removeForm');
            $this->getComponent('addImgForm');
        }
    }

    public function actionSetImg($post_id, $id) {

        $this->imgRow = $this->postImagesRepository->findById($id);
        $this->postRow = $this->postsRepository->findById($post_id);
        
        if (!$this->imgRow) {
            throw new BadRequestException("Image not found");
        } elseif (!$this->postRow) {
            throw new BadRequestException("Post not found");
        }
        
        $this->submittedSetImgForm();
    }

    public function actionRemoveImg($post_id, $id) {
        $this->imgRow = $this->postImagesRepository->findById($id);
        $this->postRow = $this->postsRepository->findById($post_id);

        if (!$this->imgRow) {
            throw new BadRequestException("Image not found");
        } elseif (!$this->postRow) {
            throw new BadRequestException("Post not found");
        }
        
        $this->submittedRemoveImgForm();
    }

    protected function createComponentAddForm() {
        $form = new Form;
        $form->addText('title', 'Názov:')
             ->setRequired("Názov je povinné pole.");
        $form->addTextArea('content', 'Obsah:')
             ->setAttribute('id', 'ckeditor');
        $form->addSubmit('save', 'Uložiť');
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

    protected function createComponentAddImgForm() {
        $form = new Form;
        $form->addMultiUpload('images', 'Obrázok:');
        $form->addSubmit('upload', 'Nahrať');
        $form->onSuccess[] = [$this, 'submittedAddImgForm'];
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
        $imgs = $this->postRow->related('postImages');
        
        foreach ($imgs as $img) {
            FileSystem::delete($this->imgFolder . '/' .$img->name);
            $img->delete();
        }

        $this->postRow->delete();
        $this->flashMessage('Príspevok bol odstránený', 'success');
        $this->redirect('all');
    }

    public function submittedSetImgForm() {
        $values['thumbnail'] = $this->imgRow->name;
        $this->postRow->update($values);
        $this->flashMessage('Miniatúra bola nastavená', 'success');
        $this->redirect('view', $this->postRow);
    }

    public function submittedRemoveImgForm() {
        FileSystem::delete($this->imgFolder . "/" . $this->imgRow->name);
        $this->imgRow->delete();
        $this->flashMessage('Obrázok bol odstránený', 'success');
        $this->redirect('view', $this->postRow);
    }

    public function submittedAddImgForm(Form $form, $values) {
        foreach ($values['images'] as $img) {
            $name = strtolower($img->getSanitizedName());

            if ($img->isOk() AND $img->isImage()) {
                $img->move($this->imgFolder . '/' . $name);
                $data['name'] = $name;
                $data['post_id'] = $this->postRow;
                $this->postImagesRepository->insert($data);
            }
        }
        $this->flashMessage('Obrázky boli pridané', 'success');
        $this->redirect('view', $this->postRow);
    }

}
