<?php

namespace App\Presenters;

use App\FormHelper;
use IPub\VisualPaginator\Components as VisualPaginator;
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

    /** @var string */
    private $storage = 'images/';

    public function actionView($id) {
        
    }

    public function renderView($id) {
        $this->albumRow = $this->albumsRepository->findById($id);
        $gallerySelection = $this->albumRow->related('gallery');

        $visualPaginator = $this->getComponent('visualPaginator');
        $paginator = $visualPaginator->getPaginator();
        $paginator->itemsPerPage = 12;
        $paginator->itemCount = $gallerySelection->count();
        $gallerySelection->limit($paginator->itemsPerPage, $paginator->offset);

        $this->template->album = $this->albumRow;
        $this->template->galleryImgs = $gallerySelection;
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
        $form->addCheckbox('setThumbnail', ' Nastaviť ako prezenčný obrázok')
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
        $this->redirect('Album:all#nav');
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
        $this->redirect('view#nav', $this->albumRow);
    }

    protected function createComponentAddImagesForm() {
        $form = new Form;
        $form->addMultiUpload('images', "Nahrať obrátok");
        $form->addSubmit('upload', 'Uložiť');
        $form->onSuccess[] = $this->submittedAddImagesForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    /**
     * Create items paginator
     *
     * @return VisualPaginator\Control
     */
    protected function createComponentVisualPaginator() {
        $control = new VisualPaginator\Control;
        $control->setTemplateFile('bootstrap.latte');
        $control->disableAjax();
        return $control;
    }

    public function submittedDeleteForm() {
        $this->userIsLogged();
        $gallery = $this->galleryRow;
        $img = new FileSystem;
        $img->delete($this->storage . $gallery->name);
        $gallery->delete();
        $this->flashMessage('Obrázok odstránený.', 'success');
        $this->redirect('view#nav', $gallery->album_id);
    }

    public function formCancelled() {
        $this->redirect('view#nav', $this->galleryRow->album_id);
    }

}
