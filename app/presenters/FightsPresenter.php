<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Database\Table\ActiveRow;
use Nette\Application\UI\Form;
use Nette\Application\BadRequestException;

class FightsPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $fightRow;

    /** @var ActioveRow */
    private $roundRow;

    /** @var string */
    private $error = "Match not found!";

    public function actionAll() {
        
    }

    public function renderAll() {
        $this->template->fights = $this->fightsRepository->findAll()->order('time');
    }

    protected function actionCreate() {
        $this->userIsLogged();
    }

    public function renderCreate() {
        $this->getComponent('addFightForm');
    }

    public function actionEdit($id) {
        $this->userIsLogged();
        $this->fightRow = $this->fightsRepository->findById($id);
    }

    public function renderEdit($id) {
        if (!$this->fightRow)
            throw new BadRequestException($this->error);

        $this->getComponent('editFightForm')->setDefaults($this->fightRow);
    }

    public function actionDelete($id) {
        $this->userIsLogged();
        $this->fightRow = $this->fightsRepository->findById($id);
    }

    public function renderDelete($id) {
        if (!$this->fightRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->fight = $this->fightRow;
    }

    protected function createComponentAddFightForm() {
        $form = new Form;

        $teams = $this->teamsRepository->getTeams();

        $form->addSelect('team1_id', 'Tím 1', $teams)
                ->setRequired("Názov tímu 1 je povinné pole");

        $form->addSelect('team2_id', 'Tím 2', $teams)
                ->setRequired("Názov tímu 2 je povinné pole");

        $form->addText('score1', 'Skóre 1')
                ->setType('number')
                ->setRequired("Počet bodov je povinné pole");

        $form->addText('score2', 'Skóre 2')
                ->setType('number')
                ->setRequired("Počet bodov je povinné pole");

        $form->addText('time', 'Dátum')
                ->setAttribute('value', date('Y-m-d'));

        $form->addSubmit('save', 'Uložiť');

        $form->onSuccess[] = $this->submittedAddFightForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditFightForm() {
        $form = new Form;

        $teams = $this->teamsRepository->getTeams();

        $form->addSelect('team1_id', 'Tím 1', $teams)
                ->setRequired("Názov tímu 1 je povinné pole");

        $form->addSelect('team2_id', 'Tím 2', $teams)
                ->setRequired("Názov tímu je povinné pole");

        $form->addText('score1', 'Skóre 1')
                ->setType('number')
                ->setRequired("Počet bodov je povinné pole");

        $form->addText('score2', 'Skóre 2')
                ->setType('number')
                ->setRequired("Počet bodov je povinné pole");

        $form->addText('time', 'Dátum');

        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = $this->submittedEditForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddFightForm($form) {
        $values = $form->getValues();

        if ($values->team1_id == $values->team2_id) {
            $form->addError('Zvoľ dva rozdielne tímy.');
            return false;
        }

        $this->fightsRepository->insert($values);
        $this->redirect('all');
    }

    public function submittedEditForm($form) {
        $values = $form->getValues();

        if ($values->team1_id == $values->team2_id) {
            $form->addError('Zvoľ dva rozdielne tímy.');
            return false;
        }

        $this->fightRow->update($values);
        $this->redirect('all');
    }

    public function submittedDeleteForm() {
        $this->userIsLogged();
        $this->fightRow->delete();
        $this->flashMessage('Zápas odstránený.', 'success');
        $this->redirect('all');
    }

    public function formCancelled() {
        $this->redirect('all');
    }

}
