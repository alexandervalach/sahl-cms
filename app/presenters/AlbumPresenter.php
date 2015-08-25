<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\FileSystem;

class AlbumPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $albumRow;

    /** @var string */
    private $error = "Album not found!";

    /** @var string */
    private $storage = 'images/';

    public function actionAll() {
        
    }

    public function renderAll() {
        $this->template->albums = $this->albumsRepository->findAll();
        $this->template->default = $this->imgFolder . "sahl.jpg";
        $this->template->imgFolder = $this->imgFolder;
    }

    public function actionCreate() {
        $this->userIsLogged();
    }

    public function renderCreate() {
        $this->getComponent('addAlbumForm');
    }

    public function actionEdit($id) {
        $this->userIsLogged();
        $this->albumRow = $this->albumsRepository->findById($id);
    }

    public function renderEdit($id) {
        if (!$this->albumRow) {
            throw new BadRequestException($this->error);
        }
        $this->getComponent('editAlbumForm')->setDefaults($this->albumRow);
    }

    public function actionDelete($id) {
        $this->userIsLogged();
        $this->albumRow = $this->albumsRepository->findById($id);
    }

    public function renderDelete($id) {
        if (!$this->albumRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->album = $this->albumRow;
        $this->getComponent('deleteForm');
    }

    protected function createComponentAddAlbumForm() {
        $form = new Form;
        $form->addText('album', 'Názov')
                ->setRequired("Názov je povinné pole.");
        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = $this->submittedAddAlbumForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditAlbumForm() {
        $form = new Form;
        $form->addText('album', 'Názov')
                ->setRequired("Názov je povinné pole");
        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = $this->submittedEditAlbumForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddAlbumForm(Form $form) {
        $values = $form->getValues();
        $values['created_at'] = date('Y.m.d H:i:s');
        $this->albumsRepository->insert($values);
        $this->redirect('all#nav');
    }

    public function submittedEditAlbumForm(Form $form) {
        $values = $form->getValues();
        $this->albumRow->update($values);
        $this->redirect('all#nav');
    }

    public function submittedDeleteForm() {
        $this->userIsLogged();
        $imgs = $this->albumRow->related('gallery');

        foreach ($imgs as $img) {
            $file = new FileSystem;
            $file->delete($this->storage . $img->name);
            $img->delete();
        }

        $this->albumRow->delete();
        $this->flashMessage('Album odstránený aj so všetkými obrázkami.', 'success');
        $this->redirect('all#nav');
    }

    public function formCancelled() {
        $this->redirect('all#nav');
    }

}
