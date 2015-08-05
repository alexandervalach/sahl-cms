<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Utils\FileSystem;

class PostImagePresenter extends BasePresenter 
{
	/** @var  ActiveRow */
	private $imgRow;

	/** @var Selection */
	private $imgSelection;

	/** @var ActiveRow */
	private $postRow;

	/** @var string */
	private $error = "Image not found!";
        
        /** @var string */
        private $storage = "images/";

	public function actionAdd( $id ) {
		$this->userIsLogged();
		$this->postRow = $this->postsRepository->findById( $id );
	}

	public function renderAdd( $id ) {
		$this->getComponent('addImageForm');
	}

	public function actionDelete( $id, $id2 ) {
		$this->userIsLogged();
		$this->imgRow = $this->postImageRepository->findById( $id2 );
		$this->postRow = $this->postsRepository->findById( $id1 );
	}

	public function actionThumbnail( $id, $id2 ) {
		$this->userIsLogged();
		$this->postRow = $this->postsRepository->findById( $id );
		$this->imgRow = $this->postImageRepository->findById( $id2 );
	}

	public function renderThumbnail( $id, $id2) {
		$this->getComponent( 'setThumbnailForm' );	
	}

	public function renderDelete( $id, $id2 ) {
		if( !$this->imgRow ) {
			throw new BadRequestException( $this->error );
		}
		$this->getComponent('deleteForm');
		$this->template->img = $this->imgRow;
	}

	protected function createComponentAddImageForm() {
		$form = new Form;

		$form->addUpload('image','Obrázok:');

		$form->addSubmit('upload','Nahrať');

		$form->onSuccess[] = $this->submittedImageForm;

		$form->addProtection();
		FormHelper::setBootstrapFormRenderer( $form );
		return $form;
	}

	protected function createComponentSetThumbnailForm() {
		$form = new Form;
		$form->addCheckbox( 'thumbnail', ' Nastaviť ako prezenčný obrázok' );
		$form->addSubmit( 'save', 'Ulož' );
		$form->onSuccess[] = $this->submittedSetThumbnailForm;
		FormHelper::setBootstrapFormRenderer( $form );
		return $form;
	}

	public function submittedDeleteForm() {
		$this->userIsLogged();

		$imgRow = $this->imgRow;

		$img_id = $this->imgRow->id;
		$post_id = $this->postRow->id;

		$img = new FileSystem;
		$img->delete($this->storage . $imgRow->name);

		$imgRow->delete();

		$this->flashMessage('Obrázok zmazaný.','success');
		$this->redirect('Post:show', $post_id);
	}

	public function submittedSetThumbnailForm( $form ) {
		$values = $form->getValues();
		if( $values['thumbnail'] == true ) {
			$values['thumbnail'] = $this->imgRow->name;
			$this->postRow->update( $values );
		}
		$this->redirect('Post:show', $this->postRow->id);
	} 

	public function formCancelled() {
		$id = $this->imgRow->id;
		$this->redirect( 'Post:show' , $id );
	}

	public function submittedImageForm( $form ) {
		$this->userIsLogged();

		$id = $this->postRow->id;
		$data = $form->getValues();
		$image = $data->image;

		if( $image->isOk() && $image->isImage() ) {
			$name = $image->getSanitizedName();
			$image->move($this->storage . $name);

			$values = array(
					'posts_id' => $id,
					'name' => $name
				);

			$postImage = array(
					'thumbnail' => $name
				);

			$this->postImageRepository->insert( $values );
			$this->postRow->update( $postImage );
		} else {
			$form->addError('Obrázok smie byť len vo formáte GIF, PNG, JPG.');
		}
		$this->redirect('add', $id);
	}

}