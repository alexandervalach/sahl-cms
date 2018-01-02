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
    private $error = "Link not found";

    public function renderAll() {
        $this->template->all_links = $this->linksRepository->findAll();
    }

    public function actionAdd() {
        $this->userIsLogged();
    }

    public function renderAdd() {
        $this->getComponent('addForm');
    }

    public function actionDelete($id) {
        $this->userIsLogged();
        $this->linkRow = $this->linksRepository->findById($id);
    }

    public function renderDelete($id) {
        if (!$this->linkRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->delete_link = $this->linkRow;
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
        $this->getComponent('editForm')->setDefaults($this->linkRow);
    }

    protected function createComponentAddForm() {
        $form = new Form;
        $form->addText('title', 'Text:');
        $form->addText('anchor', 'URL adresa:')
                ->setRequired("URL adresa je povinné pole.");
        $form->addUpload('image', 'Obrázok:');
        $form->addCheckbox('sponsor', ' Sponzor');
        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = [$this, 'submittedAddForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditForm() {
        $form = new Form;
        $form->addText('title', 'Text:')
                ->setRequired("Text linku je povinný údaj");
        $form->addText('anchor', 'URL adresa:')
                ->setRequired("URL adresa je povinné pole.");
        $form->addCheckbox('sponsor', ' Sponzor');
        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = [$this, 'submittedEditForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedDeleteForm() {
        if ($this->linkRow->image) {
            FileSystem::delete($this->imgFolder . '/' . $this->linkRow->image);
        }
        $this->linkRow->delete();
        $this->redirect('all');
    }

    public function submittedAddForm(Form $form, $values) {
        $img = $values->image;

        if ($img->isOk() && $img->isImage()) {
            $name = $img->getSanitizedName();
            $img->move($this->imgFolder . '/' . $name);
            $values['image'] = $name;
        }

        $this->linksRepository->insert($values);
        $this->flashMessage('Link bol pridaný', 'success');
        $this->redirect('all');
    }

    public function submittedEditForm(Form $form, $values) {
        $this->linkRow->update($values);
        $this->flashMessage('Link bol upravený', 'success');
        $this->redirect('all');
    }

    public function formCancelled() {
        $this->redirect('all');
    }

}
