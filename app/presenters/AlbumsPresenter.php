<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\FileSystem;
use Nette\IOException;

class AlbumsPresenter extends BasePresenter {

    const ALBUM_NOT_FOUND = 'Album not found';

    /** @var ActiveRow */
    private $albumRow;

    /** @var ActiveRow */
    private $imgRow;

    public function renderAll() {
        $this->template->albums = $this->albumsRepository->findAll();

        if ($this->user->isLoggedIn()) { 
            $this->getComponent(self::ADD_FORM);
        }
    }

    public function actionView($id) {
        $this->albumRow = $this->albumsRepository->findById($id);
    }

    public function renderView($id) {
        if (!$this->albumRow) {
            throw new BadRequestException(self::ALBUM_NOT_FOUND);
        }

        $this->template->album = $this->albumRow;
        $this->template->imgs = $this->albumRow->related('images');

        if ($this->user->isLoggedIn()) { 
            $this->getComponent(self::EDIT_FORM)->setDefaults($this->albumRow);
            $this->getComponent(self::REMOVE_FORM);
        }
    }

    public function actionSetImg($album_id, $id) {
        $this->userIsLogged();
        $this->albumRow = $this->albumsRepository->findById($album_id);
        $this->imgRow = $this->imagesRepository->findById($id);
        $this->submittedSetImg();
    }


    /**
     * @param integer $id
     * @return void
     */
    public function actionRemoveImg($id) {
        $this->userIsLogged();
        $this->imgRow = $this->imagesRepository->findById($id);
        
        if (!$this->imgRow) {
            throw new BadRequestException(self::IMG_NOT_FOUND);
        }
        
        $this->submittedRemoveImg();
    }

    protected function createComponentAddForm() {
        $form = new Form;
        $form->addText('name', 'Názov')
             ->setRequired("Názov je povinné pole.");
        $form->addSubmit('add', 'Pridať');
        $form->addSubmit('cancel', 'Zrušiť')
             ->setAttribute('class', self::BTN_WARNING)
             ->setAttribute('data-dismiss', 'modal');
        $form->onSuccess[] = [$this, self::SUBMITTED_ADD_FORM];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditForm() {
        $form = new Form;
        $form->addText('name', 'Názov')
             ->setRequired("Názov je povinné pole");
        $form->addSubmit('edit', 'Upraviť')
             ->setAttribute('class', self::BTN_SUCCESS);
        $form->addSubmit('cancel', 'Zrušiť')
             ->setAttribute('class', self::BTN_WARNING)
             ->setAttribute('data-dismiss', 'modal');
        $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentRemoveForm() {
        $form = new Form;
        $form->addSubmit('remove', 'Odstrániť')
             ->setAttribute('class', self::BTN_DANGER);
        $form->addSubmit('cancel', 'Zrušiť')
             ->setAttribute('class', self::BTN_WARNING)
             ->setAttribute('data-dismiss', 'modal');
        $form->addProtection();
        $form->onSuccess[] = [$this, self::SUBMITTED_REMOVE_FORM];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentAddImgForm() {
        $form = new Form;
        $form->addMultiUpload('images', "Nahrať obrázok");
        $form->addSubmit('upload', 'Nahrať');
        $form->onSuccess[] = [$this, self::SUBMITTED_ADD_IMG_FORM];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddForm(Form $form, $values) {
        $this->albumsRepository->insert($values);
        $this->flashMessage('Album bol pridaný', self::SUCCESS);
        $this->redirect('all');
    }

    public function submittedEditForm(Form $form, $values) {
        $this->albumRow->update($values);
        $this->flashMessage('Album bol upravený', self::SUCCESS);
        $this->redirect('view', $this->albumRow);
    }

    public function submittedRemoveForm() {
        $imgs = $this->albumRow->related('images');
        
        foreach ($imgs as $img) {
            try {
                FileSystem::delete($this->imageDir . $img->name);
            } catch(IOException $e) {
                $this->flashMessage('Nastala chyba, skúste znovu', self::DANGER);
                $this->redirect('all');       
            }
            $img->delete();
        }

        $this->albumRow->delete();
        $this->flashMessage('Album bol odstránený', self::SUCCESS);
        $this->redirect('all');
    }

    public function submittedRemoveImg() {
        $album = $this->imgRow->album_id;
        try {
            FileSystem::delete($this->imageDir . $this->imgRow->name);
            $this->imgRow->delete();
            $this->flashMessage('Obrázok bol odstránený', self::SUCCESS);
        } catch (IOException $e) {
            $this->flashMessage('Obrázok sa nepodarilo odstrániť', self::DANGER);
        }
        $this->redirect('Albums:view', $album);
    }

    public function submittedSetImg() {
        $data['thumbnail'] = $this->imgRow->name;
        $this->albumRow->update($data);
        $this->flashMessage('Miniatúra bola nastavená', self::SUCCESS);
        $this->redirect('all');
    }

    /**
     * @param Form $form
     * @param array $values
     * @throws Nette\IOException
     */
    public function submittedAddImgForm(Form $form, $values) {
        $data = array();

        foreach ($values['images'] as $img) {
            $name = strtolower($img->getSanitizedName());
            $data['name'] = $name;
            $data['album_id'] = $this->albumRow;

            try {
                if ($img->isOk() AND $img->isImage()) {
                    $img->move($this->imageDir . $name);
                }
                $this->imagesRepository->insert($data);
            } catch (IOException $e) {
                $this->flashMessage($e->getMessage(), self::DANGER);
            }
        }

        $this->flashMessage('Obrázky boli pridané', self::SUCCESS);
        $this->redirect('Albums:view', $this->albumRow);
    }

}
