<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\FileSystem;

class LinksPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $linkRow;

    /** @var string */
    private $error = "Link not found!";

    /** @var string */
    private $storage = 'images/';

    public function actionAll() {
        
    }

    public function renderAll() {
        $this->template->sponsors = $this->linksRepository->findAll()->where('sponsor', '1');
        $this->template->txtLinks = $this->linksRepository->findBy(array('image' => ' '))->where('sponsor', '0');
        $this->template->imgLinks = $this->linksRepository->findAll()->where('image != ?', ' ')->where('sponsor', '0');
        $this->template->imgFolder = $this->imgFolder;
    }

    public function actionCreate() {
        $this->userIsLogged();
    }

    public function renderCreate() {
        $this->getComponent('addLinkForm');
    }

    public function actionDelete($id) {
        $this->userIsLogged();
        $this->linkRow = $this->linksRepository->findById($id);
    }

    public function renderDelete($id) {
        if (!$this->linkRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->link = $this->linkRow;
        $this->getComponent('deleteForm');
    }

    public function actionEdit($id) {
        $this->userIsLogged();
        $this->linkRow = $this->linksRepository->findById($id);
    }

    public function renderEdit($id) {
        if (!$this->linkRow) {
            throw new BadRequestException($this->error);
        }
        $this->getComponent('editLinkForm')->setDefaults($this->linkRow);
    }

    protected function createComponentAddLinkForm() {
        $form = new Form;
        $form->addText('title', 'Text:');
        $form->addText('anchor', 'URL adresa:')
                ->setRequired("URL adresa je povinné pole.");
        $form->addUpload('image', 'Obrázok:');
        $form->addCheckbox('sponsor', 'Sponzor');
        $form->addSubmit('save', 'Uložiť');

        $form->onSuccess[] = $this->submittedAddLinkForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditLinkForm() {
        $form = new Form;
        $form->addText('title', 'Text:')
                ->setRequired("Text linku je povinný údaj");
        $form->addText('anchor', 'URL adresa:')
                ->setRequired("URL adresa je povinné pole.");
        $form->addCheckbox('sponsor', ' Sponzor');
        $form->addSubmit('save', 'Uložiť');

        $form->onSuccess[] = $this->submittedEditLinkForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedDeleteForm() {
        $link = $this->linkRow;

        if ($link->image) {
            $image = new FileSystem;
            $image->delete($this->storage . $link->image);
        }

        $link->delete();
        $this->redirect('all#nav');
    }

    public function submittedAddLinkForm(Form $form) {
        $values = $form->getValues();
        $img = $values->image;

        if ($img->isOk() && $img->isImage()) {
            $name = $img->getSanitizedName();
            $img->move($this->storage . $name);
            $values->image = $name;
        }

        $this->linksRepository->insert($values);
        $this->redirect('all#nav');
    }

    public function submittedEditLinkForm(Form $form) {
        $values = $form->getValues();
        $this->linkRow->update($values);
        $this->redirect('all#nav');
    }

    public function formCancelled() {
        $this->redirect('all#nav');
    }

}
