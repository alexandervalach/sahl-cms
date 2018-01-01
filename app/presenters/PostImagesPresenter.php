<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\FileSystem;

class PostImagesPresenter extends BasePresenter {

    /** @var  ActiveRow */
    private $imgRow;

    /** @var ActiveRow */
    private $postRow;

    /** @var string */
    private $error = "Image not found!";

    public function actionAdd($id) {
        $this->userIsLogged();
        $this->postRow = $this->postsRepository->findById($id);
    }

    public function renderAdd($id) {
        if (!$this->postRow) {
            throw new BadRequestException("Post not found");
        }
        
        $this['breadCrumb']->addLink($this->postRow->title, $this->link("Posts:view", $this->postRow));
        $this['breadCrumb']->addLink("Pridať obrázky");
        $this->getComponent('addImageForm');
        $this->template->post = $this->postRow;
    }

    public function actionThumbnail($id, $img_id) {
        $this->userIsLogged();
        $this->postRow = $this->postsRepository->findById($id);
        $this->imgRow = $this->postImageRepository->findById($img_id);
        $this->submittedSetThumbnailForm();
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
        $form->addMultiUpload('images', 'Obrázok:');
        $form->addSubmit('upload', 'Nahrať');
        $form->onSuccess[] = $this->submittedImageForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedDeleteForm() {
        if ($this->postRow->thumbnail == $this->imgRow->name) {
            $postRow->update(array('thumbnail' => 'sahl.jpg'));
        }

        FileSystem::delete($this->imgFolder . '/' . $this->imgRow->name);
        $this->imgRow->delete();
        $this->flashMessage('Obrázok bol odstránený.', 'success');
        $this->redirect('Posts:view', $this->postRow);
    }

    public function submittedSetThumbnailForm() {
        if ($this->imgRow != NULL) {
            $values['thumbnail'] = $this->imgRow->name;
            $this->postRow->update($values);
            $this->flashMessage("Nová miniatúra bola nastavená", "success");
        } else {
            $this->flashMessage("Miniatúru sa nepodarilo nastaviť", "danger");
        }
        $this->redirect('Posts:view', $this->postRow);
    }

    public function submittedImageForm(Form $form) {
        $values = $form->getValues();
        $imgData = array();
        foreach ($values['images'] as $img) {
            $name = strtolower($img->getSanitizedName());

            if ($img->isOk() AND $img->isImage()) {
                $img->move($this->imgFolder . '/' . $name);
            }
            $imgData['name'] = $name;
            $imgData['posts_id'] = $this->postRow;
            $this->postImageRepository->insert($imgData);
        }
        $this->redirect('Posts:view', $this->postRow);
    }

    public function formCancelled() {
        $this->redirect('Posts:view', $this->postRow);
    }

}
