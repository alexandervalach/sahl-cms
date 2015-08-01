<?php
namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;

class EventsPresenter extends BasePresenter
{
	/** @var ActiveRow */
	private $eventRow;

	/** @var Selection */
	private $eventSelection;

	/** @var string */
	private $error = "Event not found!";
	
	public function actionAll() {
		$this->eventSelection = $this->eventsRepository->findAll()->order('starts_at DESC');
	}

	public function renderAll(){
		$this->template->events = $this->eventSelection;
	}

	public function actionEdit( $id )
	{
		$this->userIsLogged();

		$this->eventRow = $this->eventsRepository->findById( $id );
	}

	public function renderEdit( $id ) {
		if( !$this->eventRow )
			throw new BadRequestException( $this->error );

		$this->getComponent('editEventForm')->setDefaults( $this->eventRow );
	}

	public function actionDelete( $id )
	{
		$this->userIsLogged();
		$this->eventRow = $this->eventsRepository->findById( $id );
		$this->template->event = $this->eventRow;
	}

	public function renderDelete( $id ) {
		if( !$this->eventRow ) {
			throw new BadRequestException( $this->error );
		}
		$this->getComponent('deleteForm');
	}

	public function actionCreate() {
		$this->userIsLogged();
	}

	public function renderCreate() {
		$this->getComponent('addEventForm');
	}

	protected function createComponentAddEventForm(){
		$form = new Form;
		$form->addText( 'starts_at' , 'Dátum' )
			 ->setAttribute( 'value' , date('Y-m-d') )
			 ->setRequired( "Dátume je povinné pole." );

		$form->addTextArea( 'event' , 'Udalosť:' )
			 ->setRequired( "Názov udalosti je povinné pole." );

		$form->addTextArea( 'note' , 'Poznámka:' )
			 ->setAttribute( 'class', 'form-control' );

		$form->addSubmit( 'save' , 'Uložiť' );
		
		$form->onSuccess[] = $this->submittedAddEventForm;

		FormHelper::setBootstrapFormRenderer( $form );
		return $form;	
	}

	protected function createComponentEditEventForm(){
		$form = new Form;
		$form->addText( 'starts_at', 'Dátum' )
			 ->setAttribute( 'value' , date('Y-m-d') )
			 ->setRequired( 'Dátum je povinné pole ');
		
		$form->addTextArea( 'event', 'Udalosť' )
			 ->setRequired( 'Názov udalosti je povinné pole.' );

		$form->addTextArea( 'note', 'Poznámka' )
			 ->setAttribute( 'class', 'form-control' );

		$form->addSubmit( 'save', 'Uložiť' );

		$form->onSuccess[] = $this->submittedEditEventForm;

		FormHelper::setBootstrapFormRenderer( $form );
		return $form;
	}

	public function submittedAddEventForm( $form )
	{
		$values = $form->getValues();
		$this->eventsRepository->insert($values);
		$this->redirect('Events:all');
	}

	public function submittedEditEventForm( $form ) {
		$values = $form->getValues();
		$this->eventRow->update( $values );
		$this->redirect('all');
	}

	public function submittedDeleteForm()
	{
		$this->userIsLogged();
		$this->eventRow->delete();
		$this->flashMessage('Udalosť zmazaná!','muted');
		$this->redirect('all');
	}

	public function formCancelled()
	{
		$this->redirect('all');
	}
}