<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\FileSystem;
use Nette\IOException;
use Nette\InvalidArgumentException;

class PostsPresenter extends BasePresenter {

    const POST_NOT_FOUND = 'Post not found';
    const IMAGE_NOT_FOUND = 'Image not found';
    const ADD_IMG_FORM = 'addImgForm';

    /** @var ActiveRow */
    private $postRow;

    /** @var ActiveRow */
    private $imgRow;

    public function renderAll() {
        $this->template->posts = $this->postsRepository->findAll()->order('id DESC');

        if ($this->user->isLoggedIn()) {
            $this->getComponent(self::ADD_FORM);
        }
    }

    public function actionView($id) {
        $this->postRow = $this->postsRepository->findById($id);
    }

    public function renderView($id) {
        if (!$this->postRow) {
            throw new BadRequestException(self::POST_NOT_FOUND);
        }

        $this->template->post = $this->postRow;
        $this->template->images = $this->postRow->related('postImages')->order('id DESC');

        if ($this->user->isLoggedIn()) {
            $this->getComponent(self::EDIT_FORM)->setDefaults($this->postRow);
            $this->getComponent(self::REMOVE_FORM);
            $this->getComponent(self::ADD_IMG_FORM);
        }
    }

    public function actionSetImg($post_id, $id) {

        $this->imgRow = $this->postImagesRepository->findById($id);
        $this->postRow = $this->postsRepository->findById($post_id);

        if (!$this->imgRow) {
            throw new BadRequestException(self::IMAGE_NOT_FOUND);
        }
        if (!$this->postRow) {
            throw new BadRequestException(self::POST_NOT_FOUND);
        }

        $this->submittedSetImgForm();
    }

    public function actionRemoveImg($post_id, $id) {
        $this->imgRow = $this->postImagesRepository->findById($id);
        $this->postRow = $this->postsRepository->findById($post_id);

        if (!$this->imgRow) {
            throw new BadRequestException(self::IMAGE_NOT_FOUND);
        }
        if (!$this->postRow) {
            throw new BadRequestException(self::POST_NOT_FOUND);
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
        $form->onSuccess[] = [$this, self::SUBMITTED_ADD_FORM];
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
        $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentRemoveForm() {
        $form = new Form;
        $form->addSubmit('delete', 'Odstrániť')
                ->setAttribute('class', self::BTN_DANGER);
        $form->addSubmit('cancel', 'Zrušiť')
                ->setAttribute('class', self::BTN_WARNING)
                ->setAttribute('data-dismiss', 'modal');
        $form->addProtection(self::CSRF_TOKEN_EXPIRED);
        $form->onSuccess[] = [$this, self::SUBMITTED_REMOVE_FORM];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentAddImgForm() {
        $form = new Form;
        $form->addMultiUpload('images', 'Obrázok:');
        $form->addSubmit('upload', 'Nahrať');
        $form->onSuccess[] = [$this, self::SUBMITTED_ADD_IMG_FORM];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddForm(Form $form, $values) {
        $post = $this->postsRepository->insert($values);
        $this->flashMessage('Príspevok bol pridaný', self::SUCCESS);
        $this->redirect('view', $post);
    }

    public function submittedEditForm(Form $form, $values) {
        $this->postRow->update($values);
        $this->flashMessage('Príspevok bol upravený', self::SUCCESS);
        $this->redirect('view', $this->postRow);
    }

    public function submittedRemoveForm() {
        $imgs = $this->postRow->related('postImages');

        foreach ($imgs as $img) {
            try {
                FileSystem::delete($this->imageDir . $img->name);
                $img->delete();
            } catch (IOException $e) {
                $this->flashMessage('Obrázok bol odstránený', self::DANGER);
            }
        }

        $this->postRow->delete();
        $this->flashMessage('Príspevok bol odstránený', self::SUCCESS);
        $this->redirect('all');
    }

    public function submittedSetImgForm() {
        $values['thumbnail'] = $this->imgRow->name;
        $this->postRow->update($values);
        $this->flashMessage('Miniatúra bola nastavená', self::SUCCESS);
        $this->redirect('view', $this->postRow);
    }

    public function submittedRemoveImgForm() {
        try {
            FileSystem::delete($this->imageDir . $this->imgRow->name);
            $this->imgRow->delete();
            $this->flashMessage('Obrázok bol odstránený', self::SUCCESS);
        } catch (IOException $e) {
            $this->flashMessage('Obrázok sa nepodarilo odstrániť', self::DANGER);
        }
        $this->redirect('view', $this->postRow);
    }

    public function submittedAddImgForm(Form $form, $values) {
        foreach ($values['images'] as $file) {
            $name = strtolower($file->getSanitizedName());

            if (!$file->isOK() || !$file->isImage()) {
                throw new InvalidArgumentException;
            }

            $file->move($this->imageDir . $name);
            $data = array('name' => $name, 'post_id' => $this->postRow);
            $this->postImagesRepository->insert($data);
        }
        $this->flashMessage('Obrázky boli pridané', self::SUCCESS);
    }

}
