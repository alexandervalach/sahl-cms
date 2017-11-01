<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\FileSystem;

class GalleryPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $galleryRow;

    /** @var ActiveRow */
    private $albumRow;

    /** @var string */
    private $error = "Image not found!";

    public function actionView($id) {
        
    }

    public function renderView($id) {
        $this->albumRow = $this->albumsRepository->findById($id);
        $this->template->album = $this->albumRow;
        $this->template->galleryImgs = $this->albumRow->related('gallery');
        $this->template->imgFolder = $this->imgFolder;

        $this['breadCrumb']->addLink('Albumy', $this->link('Album:all'));
        $this['breadCrumb']->addLink($this->albumRow->album);
    }

    public function actionAdd($id) {
        $this->userIsLogged();
        $this->albumRow = $this->albumsRepository->findById($id);
    }

    public function renderAdd($id) {
        $this->template->album = $this->albumRow;
        if (!$this->albumRow) {
            throw new BadRequestException($this->error);
        }
        $this->getComponent('addImagesForm');
    }

    public function actionDelete($id) {
        $this->userIsLogged();
        $this->galleryRow = $this->galleryRepository->findById($id);
    }

    public function renderDelete($id) {
        if (!$this->galleryRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->img = $this->galleryRow;
    }

    public function actionThumbnail($id, $id2) {
        $this->userIsLogged();
        $this->albumRow = $this->albumsRepository->findById($id);
        $this->galleryRow = $this->galleryRepository->findById($id2);
    }

    public function renderThumbnail($id, $id2) {
        $this->getComponent('setThumbnailForm');
    }

    protected function createComponentSetThumbnailForm() {
        $form = new Form;
        $form->addCheckbox('setThumbnail', ' Nastaviť ako miniatúru')
             ->setValue(true);
        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = $this->submittedSetThumbnailForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedSetThumbnailForm(Form $form) {
        $values = $form->getValues();

        if ($values['setThumbnail']) {
            $data = array();
            $data['name'] = $this->galleryRow->name;
            $this->albumRow->update($data);
        }

        $this->redirect('Album:all');
    }

    public function submittedAddImagesForm(Form $form) {
        $this->userIsLogged();
        $values = $form->getValues();
        $imgData = array();

        foreach ($values['images'] as $img) {
            $name = strtolower($img->getSanitizedName());

            if ($img->isOk() AND $img->isImage()) {
                $img->move($this->storage . $name);
            }

            $imgData['name'] = $name;
            $imgData['album_id'] = $this->albumRow;
            $this->galleryRepository->insert($imgData);
        }

        $this->redirect('view', $this->albumRow);
    }

    protected function createComponentAddImagesForm() {
        $form = new Form;
        $form->addMultiUpload('images', "Nahrať obrázok");
        $form->addSubmit('upload', 'Nahrať');
        $form->onSuccess[] = $this->submittedAddImagesForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedDeleteForm() {
        $gallery = $this->galleryRow;
        $img = new FileSystem;
        $img->delete($this->storage . $gallery->name);
        $gallery->delete();

        $this->flashMessage('Obrázok odstránený.', 'success');
        $this->redirect('view', $gallery->album_id);
    }

    public function formCancelled() {
        $this->redirect('view', $this->galleryRow->album_id);
    }

}
