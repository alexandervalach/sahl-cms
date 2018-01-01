<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;

class RoundsPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $roundRow;

    /** @var ActiveRow */
    private $archRow;

    /** @var string */
    private $error = "Round not found!";

    public function renderAll() {
        $this->template->rounds = $this->roundsRepository->findByValue('archive_id', null);
        
        $this['breadCrumb']->addLink("Kolá");

        if ($this->user->isLoggedIn()) {
            $this->getComponent('addRoundForm');
        }
    }

    public function actionView($id) {
        $this->roundRow = $this->roundsRepository->findById($id);
    }

    public function renderView($id) {

        if (!$this->roundRow) {
            throw new BadRequestException("Round not found.");
        }

        $i = 0;
        $fight_data = array();
        $fights = $this->roundRow->related('fights');

        foreach ($fights as $fight) {
            $fight_data[$i]['goals'] = $fight->related('goals')->order('goals DESC');
            $fight_data[$i]['team_1'] = $fight->ref('teams', 'team1_id');
            $fight_data[$i]['team_2'] = $fight->ref('teams', 'team2_id');
            $fight_data[$i]['home_goals'] = $fight->related('goals')->where('home', 1)->order('goals DESC');
            $fight_data[$i]['guest_goals'] = $fight->related('goals')->where('home', 0)->order('goals DESC');

            if ($fight->score1 > $fight->score2) {
                $fight_data[$i]['state_1'] = 'text-success';
                $fight_data[$i]['state_2'] = 'text-danger';
            } else if ($fight->score1 < $fight->score2) {
                $fight_data[$i]['state_1'] = 'text-danger';
                $fight_data[$i]['state_2'] = 'text-success';
            } else {
                $fight_data[$i]['state_1'] = $fight_data[$i]['state_2'] = '';
            } 
            $i++;
        }

        $this->template->fights = $fights;
        $this->template->fight_data = $fight_data;
        $this->template->i = 0;
        $this['breadCrumb']->addLink("Kolá", $this->link("Round:all"));
        $this['breadCrumb']->addLink($this->roundRow->name);

        if ($this->user->isLoggedIn()) {
            $this->getComponent('addForm');
        }

        $this['breadCrumb']->addLink("Kolá", $this->link("Round:all"));
        $this['breadCrumb']->addLink($this->roundRow->name);
    }

    public function actionEdit($id) {
        $this->userIsLogged();
        $this->roundRow = $this->roundsRepository->findById($id);
    }

    public function renderEdit($id) {
        if (!$this->roundRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->round = $this->roundRow;
        $this->getComponent('editRoundForm')->setDefaults($this->roundRow);
    }

    public function actionDelete($id) {
        $this->userIsLogged();
        $this->roundRow = $this->roundsRepository->findById($id);
    }

    public function renderDelete($id) {
        if (!$this->roundRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->round = $this->roundRow;
        $this->getComponent('deleteForm');
    }

    public function actionArchView($id) {
        $this->archRow = $this->archiveRepository->findById($id);
    }

    public function renderArchView($id) {
        $this->template->rounds = $this->roundsRepository->findByValue('archive_id', $id);
        $this->template->archive = $this->archRow;
        $this['breadCrumb']->addLink("Archív", $this->link("Archive:all"));
        $this['breadCrumb']->addLink($this->archRow->title, $this->link("Archive:view", $this->archRow));
        $this['breadCrumb']->addLink("Kolá");
    }

    protected function createComponentAddRoundForm() {
        $form = new Form;
        $form->addText('name', 'Názov')
                ->addRule(Form::FILLED, "Opa, zabudli ste vyplniť názov kola");
        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = $this->submittedAddRoundForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditRoundForm() {
        $form = new Form;
        $form->addText('name', 'Názov')
                ->addRule(Form::MAX_LENGTH, "Dĺžka názvu môže byť len 50 znakov", 50)
                ->setRequired("Názov je povinné pole");
        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = $this->submittedEditRoundForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddRoundForm(Form $form) {
        $this->userIsLogged();
        $values = $form->getValues();
        $this->roundsRepository->insert($values);
        $this->redirect('all');
    }

    public function submittedEditRoundForm(Form $form) {
        $this->userIsLogged();
        $values = $form->getValues();
        $this->roundRow->update($values);
        $this->redirect('all');
    }

    public function submittedDeleteForm() {
        $this->userIsLogged();
        $fights = $this->roundRow->related('fights');
        /* Odstráni všetky zápasy daného kola */
        foreach ($fights as $fight) {
            $fight->delete();
        }
        $this->roundRow->delete();
        $this->flashMessage('Kolo bolo odstránené.', 'success');
        $this->redirect('all');
    }

    public function formCancelled() {
        $this->redirect('all');
    }

}
