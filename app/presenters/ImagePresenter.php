<?php

namespace App\Presenters;

use Nette;

class ImagePresenter extends BasePresenter
{
	private $database;

	public function __construct(Nette\Database\Context $database)
	{
	    $this->database = $database;
	}

	public function actionAdd($albumId)
	{
	    if(!$this->user->isLoggedIn() )
	    {
	        $this->redirect('Sign:in');
	    }

	    $this->template->album = $this->database->table('albums')->get($albumId);
	}

	//Funkcia skontroluje, či existuje hľadaný záznam
	public function actionDelete($imgId, $albumId)
	{
		if(!$this->user->isLoggedIn() )
	    {
	        $this->redirect('Sign:in');
	    }	

	    $this->template->img = $this->database->table('gallery')->get($imgId);

	    if(!$this->template->img) {
	    	$this->error('Záznam neexistuje.');
	    }
	}

	protected function createComponentGalleryForm()
  	{
	    $form = new Nette\Application\UI\Form;

	    $form->addUpload("file", 'Obrázok:');

	    $form->addTextArea('description','Popis:')
	    	 ->setAttribute('class','form-control');

	    $form->addSubmit("save", "Uložiť")
	    	 ->setAttribute('class','btn btn-large btn-primary');

	    $form->onSuccess[] = $this->galleryFormSuceeded;
	    return $form;
  	}

  	public function deleteFormSucceeded()
  	{
	    $albumId = $this->getParameter('albumId');
	    $imgId = $this->getParameter('imgId');

	    $onDelete = $this->database->table('gallery')->get($imgId);

	    $file = new Nette\Utils\FileSystem;
	    $file->delete('images/' . $onDelete->name);
	      
	    $onDelete->delete();

	    $this->flashMessage('Obrázok zmazaný.','success');
	    $this->redirect('show', $albumId);
 	}

 	public function formCancelled()
 	{
 		$albumId = $this->getParameter('albumId');
 		$this->redirect('show', $albumId);
 	}

 	public function galleryFormSuceeded($form)
  	{
	    $values = $form->getValues();
	    $file = $values->file;
	    $albumId = $this->getParameter('albumId');
	    $description = $values->description;

	    if( $file->isOk() && $file->isImage() ) 
	    {
		    $file_name = $file->getSanitizedName();
		    $file->move('images/' . $file_name);

		    $data = array(
		        'album_id' => $albumId,
		        'name' => $file_name,
		        'description' => $description
		    );

		    $albumData = array(
		    	'name' => $file_name
		    );

		    $this->database->table('gallery')->insert($data);
		    $this->database->table('albums')->get($albumId)->update($albumData);
		      
		    $this->flashMessage('Obrázok pridaný do galérie.','success');
	    	$this->redirect('add', $albumId);
	    } else {

	    	$this->error('Obrázok smie byť len vo formátoch GIF, PNG, JPG.');

	    }
  	}

  	public function renderShow($albumId = 1)
  	{	
	    $album = $this->database->table('albums')->get($albumId);

	    if(!$album)
	    {
	       	$this->error('Stránka nenájdená.');
	    }

	    $this->template->album = $album;
	    $this->template->gallery = $album->related('gallery')->order('id DESC');
	}
}