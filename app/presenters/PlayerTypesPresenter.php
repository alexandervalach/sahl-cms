<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;

class PlayerTypesPresenter extends BasePresenter {

    const PLAYER_TYPE_NOT_FOUND = 'Player type not found';

    /** @var ActiveRow */
    private $playerTypeRow;

    public function actionAll() {
        $this->userIsLogged();
    }

    public function renderAll() {
        $types = $this->playerTypesRepository->findAll();
        $this->template->types = $types;
        $this->template->typesCount = $types->count();
        $this->getComponent(self::ADD_FORM);
    }

    public function actionEdit($id) {
        $this->userIsLogged();
        $this->playerTypeRow = $this->playerTypesRepository->findById($id);
    }

    public function renderEdit($id) {
        if (!$this->playerTypeRow) {
            throw new BadRequestException(self::PLAYER_TYPE_NOT_FOUND);
        }
        $this->getComponent(self::EDIT_FORM)->setDefaults($this->playerTypeRow);
        $this->template->type = $this->playerTypeRow;
    }

    public function actionRemove($id) {
        $this->userIsLogged();
        $this->playerTypeRow = $this->playerTypesRepository->findById($id);
    }

    public function renderRemove($id) {
        if (!$this->playerTypeRow) {
            throw new BadRequestException(self::PLAYER_TYPE_NOT_FOUND);
        }
        $this->template->type = $this->playerTypeRow;
        $this->getComponent(self::REMOVE_FORM);
    }

    protected function createComponentAddForm() {
        $form = new Form;
        $form->addText('type', 'Typ hráča')
                ->addRule(Form::FILLED, 'Ešte vyplńte názov')
                ->addRule(Form::MAX_LENGTH, 'Názov môže mať len 50 znakov.', 50);
        $form->addText('abbr', 'Skratka');
        $form->addSubmit('save', 'Uložiť');
        FormHelper::setBootstrapFormRenderer($form);
        $form->onSuccess[] = [$this, self::SUBMITTED_ADD_FORM];
        return $form;
    }

    protected function createComponentEditForm() {
        $form = new Form;
        $form->addText('type', 'Typ hráča')
                ->addRule(Form::FILLED, 'Ešte vyplńte názov')
                ->addRule(Form::MAX_LENGTH, 'Názov môže mať len 50 znakov.', 50);
        $form->addText('abbr', 'Skratka');
        $form->addSubmit('save', 'Uložiť');
        FormHelper::setBootstrapFormRenderer($form);
        $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
        return $form;
    }

    protected function createComponentRemoveForm() {
        $form = new Form;
        $form->addSubmit('save', 'Odstrániť')
                        ->setAttribute('class', self::BTN_DANGER)
                ->onClick[] = [$this, self::SUBMITTED_REMOVE_FORM];
        $form->addSubmit('cancel', 'Zrušiť')
                        ->setAttribute('class', self::BTN_WARNING)
                ->onClick[] = [$this, 'formCancelled'];
        return $form;
    }

    public function submittedAddForm(Form $form, $values) {
        $this->playerTypesRepository->insert($values);
        $this->flashMessage('Typ hráča bol pridaný', self::SUCCESS);
        $this->redirect('all');
    }

    public function submittedEditForm(Form $form, $values) {
        $this->playerTypeRow->update($values);
        $this->flashMessage('Typ hráča bol upravený', self::SUCCESS);
        $this->redirect('all');
    }

    public function submittedRemoveForm() {
        $players = $this->playerTypeRow->related('player');
        $data = array('type_id' => 1);

        foreach ($players as $player) {
            $player->update($data);
        }

        $this->playerTypeRow->delete();
        $this->flashMessage('Typ hráča bol odstránený', self::SUCCESS);
        $this->redirect('all');
    }

    public function formCancelled() {
        $this->redirect('all');
    }

}
