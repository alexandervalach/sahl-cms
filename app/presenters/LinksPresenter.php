<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Utils\FileSystem;

class LinksPresenter extends BasePresenter
{
	/** @var ActiveRow */
	private $linkRow;

	/** @var Selection */
	private $linkSelection;

	/** @var string */
	private $error = "Link not found!";

	/** @var string */
	private $storage = 'images/links/';

	public function actionAll() {
		$this->linkSelection = $this->linksRepository->findBy( array( 'image' => ' ' ) );
	}

	public function renderAll() {
		$this->template->txtLinks = $this->linkSelection;
		$this->template->imgLinks = $this->linksRepository->findAll()->where( 'image != ?', ' ' );
	}

	public function actionCreate() {
		$this->userIsLogged();
	}

	public function renderCreate() {
		$this->getComponent('addLinkForm');
	}

	public function actionDelete( $id )
	{
		$this->userIsLogged();
		$this->linkRow = $this->linksRepository->findById( $id );
	}

	public function renderDelete( $id ) {
		if( !$this->linkRow ) {
			throw new BadRequestException( $this->error );
		}
		$this->template->link = $this->linkRow;
	}

	public function actionEdit( $id )
	{
		$this->userIsLogged();
		$this->linkRow = $this->linksRepository->findById( $id );
	}

	public function renderEdit( $id ) {
		if( !$this->linkRow )
			throw new BadRequestException( $this->error );
		$this->getComponent('editLinkForm')->setDefaults( $this->linkRow );
	}

	protected function createComponentAddLinkForm()	{
		$form = new Form;

		$form->addText( 'title' , 'Text:' );

		$form->addText( 'anchor' , 'URL adresa:' )
			 ->setRequired("URL adresa je povinné pole.");

		$form->addUpload( 'image' , 'Obrázok:' );

		$form->addSubmit( 'save' , 'Uložiť' );

		$form->onSuccess[] = $this->submittedAddLinkForm;
		FormHelper::setBootstrapFormRenderer( $form );
		return $form;	
	}

	protected function createComponentEditLinkForm() {
		$form = new Form;
		$form->addText( 'title' , 'Text:' )
			 ->setRequired("Text linku je povinný údaj");

		$form->addText( 'anchor', 'URL adresa:' )
			 ->setRequired("URL adresa je povinné pole.");

		$form->addSubmit( 'save', 'Uložiť' );

		$form->onSuccess[] = $this->submittedEditLinkForm;
		FormHelper::setBootstrapFormRenderer( $form );
		return $form;
	}

	public function submittedDeleteForm() {
		$link = $this->linkRow;

		if( $link->image ) {
			$image = new FileSystem;
	    	$image->delete( $this->storage . $link->image );
	    }

	    $link->delete();
		$this->redirect('all');
	}

	public function submittedAddLinkForm( Form $form ) 
	{
		$values = $form->getValues();
		$img = $values->image;

	   	if( $img->isOk() && $img->isImage() ) {
	    	$name = $img->name;
	    	$img->move( $this->storage . $name);
	    	$values->image = $name;
	    } 

		$this->linksRepository->insert($values);
		$this->redirect('all');
	}

	public function submittedEditLinkForm( Form $form ) {
		$values = $form->getValues();
		$this->linkRow->update( $values );
		$this->redirect('all');
	}

	public function formCancelled() {
		$this->redirect('all');
	}
}