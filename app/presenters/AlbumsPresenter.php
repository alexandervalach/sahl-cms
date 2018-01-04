<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\FileSystem;
use Nette\IOException;

class AlbumsPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $albumRow;

    /** @var ActiveRow */
    private $imgRow;

    /** @var string */
    private $error = "Album not found";

    public function renderAll() {
        $this->template->albums = $this->albumsRepository->findAll();
        $this->template->default_img = $this->default_img;
        $this->template->imgFolder = $this->imgFolder;

        $this['breadCrumb']->addLink("Galéria");

        if ($this->user->isLoggedIn()) { 
            $this->getComponent('addForm');
        }
    }

    public function actionView($id) {
        $this->albumRow = $this->albumsRepository->findById($id);
    }

    public function renderView($id) {
        if (!$this->albumRow) {
            throw new BadRequestException($this->error);
        }

        $this->template->album = $this->albumRow;
        $this->template->imgs = $this->albumRow->related('images');
        $this->template->imgFolder = $this->imgFolder;

        $this['breadCrumb']->addLink('Galéria', $this->link('Albums:all'));
        $this['breadCrumb']->addLink($this->albumRow->name);

        if ($this->user->isLoggedIn()) { 
            $this->getComponent('editForm')->setDefaults($this->albumRow);
            $this->getComponent('removeForm');
        }
    }

    public function actionSetImg($album_id, $id) {
        $this->userIsLogged();
        $this->albumRow = $this->albumsRepository->findById($album_id);
        $this->imgRow = $this->imagesRepository->findById($id);
        $this->submittedSetImg();
    }

    public function actionRemoveImg($id) {
        $this->userIsLogged();
        $this->imgRow = $this->imagesRepository->findById($id);
        if (!$this->imgRow) {
            throw new BadRequestException("Image not found");
        }
        $this->submittedRemoveImg();
    }

    protected function createComponentAddForm() {
        $form = new Form;
        $form->addText('name', 'Názov')
             ->setRequired("Názov je povinné pole.");
        $form->addSubmit('add', 'Pridať');
        $form->addSubmit('cancel', 'Zrušiť')
             ->setAttribute('class', 'btn btn-large btn-warning')
             ->setAttribute('data-dismiss', 'modal');
        $form->onSuccess[] = [$this, 'submittedAddForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditForm() {
        $form = new Form;
        $form->addText('name', 'Názov')
             ->setRequired("Názov je povinné pole");
        $form->addSubmit('edit', 'Upraviť')
             ->setAttribute('class', 'btn btn-large btn-success');
        $form->addSubmit('cancel', 'Zrušiť')
             ->setAttribute('class', 'btn btn-large btn-warning')
             ->setAttribute('data-dismiss', 'modal');
        $form->onSuccess[] = [$this, 'submittedEditForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentRemoveForm() {
        $form = new Form;
        $form->addSubmit('remove', 'Odstrániť')
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
        $form->addMultiUpload('images', "Nahrať obrázok");
        $form->addSubmit('upload', 'Nahrať');
        $form->onSuccess[] = [$this, 'submittedAddImgForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddForm(Form $form, $values) {
        $this->albumsRepository->insert($values);
        $this->flashMessage('Album bol pridaný', 'success');
        $this->redirect('all');
    }

    public function submittedEditForm(Form $form, $values) {
        $this->albumRow->update($values);
        $this->flashMessage('Album bol upravený', 'success');
        $this->redirect('view', $this->albumRow);
    }

    public function submittedRemoveForm() {
        $imgs = $this->albumRow->related('images');
        
        foreach ($imgs as $img) {
            try {
                FileSystem::delete($this->imgFolder . '/' . $img->name);
            } catch(IOException $e) {
                $this->flashMessage('Nastala chyba, skúste znovu', 'danger');
                $this->redirect('all');       
            }
            $img->delete();
        }

        $this->albumRow->delete();
        $this->flashMessage('Album bol odstránený', 'success');
        $this->redirect('all');
    }

    public function submittedRemoveImg() {
        $album = $this->imgRow->album_id;
        try {
            FileSystem::delete($this->imgFolder . '/' . $this->imgRow->name);
            $this->imgRow->delete();
            $this->flashMessage('Obrázok bol odstránený', 'success');
        } catch (IOException $e) {
            $this->flashMessage('Nastala chyba, skúste znovu', 'danger');
        }
        $this->redirect('Albums:view', $album);
    }

    public function submittedSetImg() {
        $data['thumbnail'] = $this->imgRow->name;
        $this->albumRow->update($data);
        $this->flashMessage('Miniatúra bola nastavená', 'success');
        $this->redirect('all');
    }

    public function submittedAddImgForm(Form $form, $values) {
        $data = array();

        foreach ($values['images'] as $img) {
            $name = strtolower($img->getSanitizedName());
            $data['name'] = $name;
            $data['album_id'] = $this->albumRow;

            try {
                if ($img->isOk() AND $img->isImage()) {
                    $img->move($this->imgFolder . '/' . $name);
                }
                $this->imagesRepository->insert($data);
            } catch (IOException $e) {
                $this->flashMessage('Obrázok ' . $name . ' sa nepodarilo nahrať', 'danger');
            }
        }

        $this->flashMessage('Obrázky boli pridané', 'success');
        $this->redirect('Albums:view', $this->albumRow);
    }

}
