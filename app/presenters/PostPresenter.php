<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\FileSystem;

class PostPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $postRow;

    /** @var string */
    private $error = "Post not found!";

    public function actionView($id) 
    {
        $this->postRow = $this->postsRepository->findById($id);
    }

    public function renderView($id)
    {    
        if (!$this->postRow) 
        {
            throw new BadRequestException($this->error);
        }
        $this->redrawControl('main');
        $this->template->post = $this->postRow;
        $this->template->images = $this->postRow->related('images')->order('id DESC');
        $this->template->imgFolder = $this->imgFolder;
        $this->template->default_img = $this->default_img;
        $this['breadCrumb']->addLink($this->postRow->title);
        
        if ($this->user->isLoggedIn()) 
        {
            $this->getComponent("editForm")->setDefaults($this->postRow);
            $this->getComponent("deleteForm");
        }
    }

    public function actionAdd() 
    {
        $this->userIsLogged();
    }

    public function renderAdd() 
    {
        $this->getComponent('addForm');
    }

    protected function createComponentAddForm() 
    {
        $form = new Form;
        $form->addText('title', 'Názov:')
                ->setRequired("Názov je povinné pole.");
        $form->addTextArea('content', 'Obsah:')
                ->setAttribute('id', 'ckeditor')
                ->setRequired("Obsah príspevku je povinné pole.");
        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = [ $this, 'submittedAddForm' ];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditForm() 
    {
        $form = new Form;
        $form->addText('title', 'Názov:')
                ->setRequired("Názov je povinné pole.");
        $form->addTextArea('content', 'Obsah:')
                ->setAttribute('id', 'ckeditor')
                ->setRequired("Obsah príspevku je povinné pole.");
        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = [$this, 'submittedEditForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentDeleteForm() 
    {
        $form = new Form;
        $form->addSubmit('delete', 'Odstrániť')
             ->setAttribute('class', 'btn btn-large btn-danger');
        $form->addSubmit('cancel', 'Zrušiť')
             ->setAttribute('class', 'btn btn-large btn-warning')
             ->setAttribute('data-dismiss', 'modal');
        $form->addProtection();
        $form->onSuccess[] = [$this, 'submittedDeleteForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddForm(Form $form, $values) 
    {
        $post = $this->postsRepository->insert($values);
        $this->flashMessage('Pridaný nový príspevok', 'success');
        $this->redirect('view', $post);
    }

    public function submittedEditForm(Form $form, $values) 
    {
        $this->postRow->update($values);
        $this->flashMessage('Príspevok upravený', 'success');
        $this->redirect('view', $this->postRow);
    }

    public function submittedDeleteForm() 
    {
        $imgs = $this->postRow->related('images');    
        
        foreach ($imgs as $img) {
            $file = new FileSystem;
            $file->delete($this->imgFolder . '/' . $img->name);
            $img->delete();
        }
        
        $this->postRow->delete();
        $this->flashMessage('Príspevok odstránený aj so všetkými obrázkami.', 'success');
        $this->redirect('Homepage:');
    }

    public function formCancelled() 
    {
        $this->redirect('Homepage:');
    }

}
