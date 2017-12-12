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

    public function renderView($id) {
        $this->redrawControl('main');
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

    public function actionThumbnail($id, $img_id) {
        $this->userIsLogged();
        $this->albumRow = $this->albumsRepository->findById($id);
        $this->galleryRow = $this->galleryRepository->findById($img_id);
        $this->submittedSetThumbnailForm();
    }

    protected function submittedSetThumbnailForm() {
        if ($this->galleryRow != NULL) {
            $data['name'] = $this->galleryRow->name;
            $this->albumRow->update($data);
            $this->flashMessage("Nová miniatúra bola nastavená", "success");
            $this->redirect('Album:all');
        } else {
            $this->flashMessage("Miniatúru sa nepodarilo nastaviť", "danger");
            $this->redirect('Gallery:view', $this->albumRow);
        }
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
