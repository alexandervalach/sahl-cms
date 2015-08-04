<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;

class GalleryPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $galleryRow;

    /** @var ActiveRow */
    private $albumRow;

    /** @var string */
    private $error = "Image not found!";

    /** @var string */
    private $storage = 'images/';

    public function actionView($id) {
        
    }

    public function renderView($id) {
        $this->albumRow = $this->albumsRepository->findById($id);
        $this->template->album = $this->albumRow;
        $this->template->galleryImgs = $this->albumRow->related('gallery');
        $this->template->imgFolder = $this->imgFolder;
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
        $form->addMultipleFileUpload('images', "Nahrať obrázok", 5)
                ->addRule('MultipleFileUpload\MultipleFileUpload::validateFilled', "Musíš nahrať aspoň jeden obrázok");
        //->addRule('MultipleFileUpload\MultipleFileUpload::validateFileSize', "Súbory, ktoré si vybral sú príliš veľké", 10240);
        $form->addSubmit('upload', 'Uložiť');
        $form->onSuccess[] = $this->submittedAddImagesForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedDeleteForm() {
        $this->galleryRow->delete();
        $this->flashMessage('Obrázok zmazaný.', 'success');
        $this->redirect('view', $this->galleryRow->album_id);
    }

    public function formCancelled() {
        $this->redirect('view', $this->galleryRow->album_id);
    }

}
