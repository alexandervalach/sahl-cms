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

    public function actionView($id) {
        
    }

    public function renderView($id) {
        $this->albumRow = $this->albumsRepository->findById($id);
        $this->template->album = $this->albumRow;
        $this->template->galleryImgs = $this->albumRow->related('gallery');
        $this->template->imgFolder = $this->imgFolder;
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
        $img = $this->galleryRepository->insert($values);
        $this->redirect('view', $img->album_id);
    }

    protected function createComponentAddImagesForm() {
        $form = new Form;
        $form->addText('album', 'Názov albumu')
                ->setRequired();
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
