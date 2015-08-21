<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\FileSystem;

class PostImagePresenter extends BasePresenter {

    /** @var  ActiveRow */
    private $imgRow;

    /** @var ActiveRow */
    private $postRow;

    /** @var string */
    private $error = "Image not found!";

    /** @var string */
    private $storage = "images/";

    public function actionAdd($id) {
        $this->userIsLogged();
        $this->postRow = $this->postsRepository->findById($id);
    }

    public function renderAdd($id) {
        if (!$this->postRow) {
            throw new BadRequestException("Post not found.");
        }
        $this->getComponent('addImageForm');
        $this->template->post = $this->postRow;
    }

    public function actionThumbnail($id, $id2) {
        $this->userIsLogged();
        $this->postRow = $this->postsRepository->findById($id);
        $this->imgRow = $this->postImageRepository->findById($id2);
    }

    public function renderThumbnail($id, $id2) {
        $this->getComponent('setThumbnailForm');
        $this->template->post = $this->postRow;
    }

    public function actionDelete($id, $id2) {
        $this->userIsLogged();
        $this->imgRow = $this->postImageRepository->findById($id2);
        $this->postRow = $this->postsRepository->findById($id);
    }

    public function renderDelete($id, $id2) {
        if (!$this->imgRow) {
            throw new BadRequestException($this->error);
        }
        $this->getComponent('deleteForm');
        $this->template->img = $this->imgRow;
    }

    protected function createComponentAddImageForm() {
        $form = new Form;

        $form->addUpload('images', 'Obrázok:')
                ->setAttribute('multiple', 'multiple');

        $form->addSubmit('upload', 'Nahrať');

        $form->onSuccess[] = $this->submittedImageForm;

        $form->addProtection();
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentSetThumbnailForm() {
        $form = new Form;
        $form->addCheckbox('thumbnail', ' Nastaviť ako prezenčný obrázok')
                ->setValue(true);
        $form->addSubmit('save', 'Ulož');
        $form->onSuccess[] = $this->submittedSetThumbnailForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedDeleteForm() {
        $this->userIsLogged();
        $img = $this->imgRow;
        $post = $this->postRow;

        if ($post->thumbnail == $img->name) {
            $post->update(array('thumbnail' => 'sahl.jpg'));
        }

        $image = new FileSystem;
        $image->delete($this->storage . $img->name);
        $img->delete();
        $this->flashMessage('Obrázok odstránený.', 'success');
        $this->redirect('Post:show', $img->posts_id);
    }

    public function submittedSetThumbnailForm(Form $form) {
        $values = $form->getValues();
        if ($values['thumbnail'] == true) {
            $values['thumbnail'] = $this->imgRow->name;
            $this->postRow->update($values);
        }
        $this->redirect('Post:show', $this->postRow->id);
    }

    public function submittedImageForm(Form $form) {
        $this->userIsLogged();
        $values = $form->getValues();
        $imgData = array();
        foreach ($values['images'] as $img) {
            $name = strtolower($img->getSanitizedName());

            if ($img->isOk() AND $img->isImage()) {
                $img->move($this->storage . $name);
            }
            $imgData['name'] = $name;
            $imgData['posts_id'] = $this->postRow;
            $this->postImageRepository->insert($imgData);
        }
        $this->redirect('Post:show', $this->postRow);
    }

    public function formCancelled() {
        $id = $this->imgRow->id;
        $this->redirect('Post:show', $id);
    }

}
