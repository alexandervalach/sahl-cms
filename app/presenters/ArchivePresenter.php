<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;

class ArchivePresenter extends BasePresenter {

	/** @var ArchivePresenter */
	private $archiveRow;

	/** @var string */
	private $error = "Archive not found.";

	public function renderAll() {
		$this->template->archive = $this->archiveRepository->findAll();
	}

	public function actionAdd() {
		$this->userIsLogged();
	}

	public function renderAdd() {
		$this->getComponent('addForm');
	}

	public function actionEdit($id) {
		$this->userIsLogged();
		$this->archiveRow = $this->archiveRepository->findById($id);
	}

	public function renderEdit($id) {
		if(!$this->archiveRow) {
			throw new BadRequestException($this->error);
		}
		$this->template->archive = $this->archiveRow;
		$this->getComponent('editForm')->setDefaults($this->archiveRow);
	}

	public function actionView($id) {
		$this->archiveRow = $this->archiveRepository->findById($id);
	}

	public function renderView($id) {
		if(!$this->archiveRow) {
			throw new BadRequestException($this->error);
		}
		$this->template->archive = $this->archiveRow;
	}

	protected function createComponentAddForm() {
		$form = new Form;
		$form->addText('title', 'Názov')
		     ->addRule(Form::FILLED, 'Opa, názov ešte nie je vyplnený.');
		$form->addSubmit('save', 'Uložiť');

		$form->onSuccess[] = $this->submittedAddForm;

		FormHelper::setBootstrapFormRenderer($form);
		return $form;
	}

	protected function createComponentEditForm() {
		$form = new Form;
		$form->addText('title', 'Názov')
		     ->addRule(Form::FILLED, 'Opa, názov ešte nie je vyplnený.');
		$form->addSubmit('save', 'Uložiť');

		$form->onSuccess[] = $this->submittedEditForm;

		FormHelper::setBootstrapFormRenderer($form);
		return $form;
	}

	public function submittedAddForm(Form $form) {
		$values = $form->getValues();
		$this->archiveRepository->insert($values);
		$this->redirect('all#nav');
	}

	public function submittedEditForm(Form $form) {
		$values = $form->getValues();
		$this->archiveRow->update($values);
		$this->redirect('all#nav');
	}

}