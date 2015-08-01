<?php
namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Application\BadRequestException;

class ForumPresenter extends BasePresenter
{
	/** @var ActiveRow */
	private $forumRow;

	/** @var Selection */
	private $forumSelection;

	/** @var string */
	private $error = "Message not found!";

	public function actionAll() {
		$this->forumSelection = $this->forumRepository->findAll();
	}

	public function renderAll() {
		$this->template->forums = $this->forumSelection;
	}

	public function actionDelete( $id ) {
		$this->userIsLogged();
		$this->forumRow = $this->forumRepository->findById( $id );
	}

	public function renderDelete( $id ) {
		if( !$this->forumRow ) {
			throw new BadRequestException( $this->error );
		}
		$this->template->forum = $this->forumRow;
	}

	protected function createComponentAddMessageForm() {
		$form = new Form;

		$form->addText('author','Meno:')
			 ->setRequired("Meno je povinné pole.");

		$form->addText('email','E-mail:')
			 ->setType('email');

		$form->addTextArea('message','Príspevok:')
			 ->setAttribute('class','form-control')
			 ->setRequired("Príspevok je povinné pole.");

		$form->addSubmit('add','Pridaj');

		$form->onSuccess[] = $this->submittedAddMessageForm;
		FormHelper::setBootstrapFormRenderer( $form );
		return $form;	
	}

	public function submittedDeleteForm( $form ) {
		$this->userIsLogged();	
		$this->forumRow->delete();
		$this->flashMessage('Príspevok zmazaný.','success');
		$this->redirect('all');
	}

	public function submittedAddMessageForm($form)
	{
		$values = $form->getValues();
		$this->forumRepository->insert($values);
		$this->redirect('all');
	}

	public function formCancelled() {
		$this->redirect('all');
	}
}