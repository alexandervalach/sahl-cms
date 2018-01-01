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
    private $error = "Round not found";

    public function renderAll() {
        $this->template->rounds = $this->roundsRepository->findByValue('archive_id', null);
        $this['breadCrumb']->addLink("Kolá");

        if ($this->user->loggedIn) {
            $this->getComponent('addForm');
        }
    }

    public function actionView($id) {
        $this->roundRow = $this->roundsRepository->findById($id);
    }

    public function renderView($id) {
        if (!$this->roundRow) {
            throw new BadRequestException($this->error);
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
        $this->template->round = $this->roundRow;
        $this['breadCrumb']->addLink("Kolá", $this->link("Rounds:all"));
        $this['breadCrumb']->addLink($this->roundRow->name);

        if ($this->user->loggedIn) {
            $this->getComponent('editForm')->setDefaults($this->roundRow);
            $this->getComponent('removeForm');
        }
    }

    public function actionArchView($id) {
        $this->archRow = $this->archiveRepository->findById($id);
    }

    public function renderArchView($id) {
        $this->template->rounds = $this->roundsRepository->findByValue('archive_id', $id);
        $this->template->archive = $this->archRow;
        $this['breadCrumb']->addLink("Archív", $this->link("Archives:all"));
        $this['breadCrumb']->addLink($this->archRow->title, $this->link("Archives:view", $this->archRow));
        $this['breadCrumb']->addLink("Kolá");
    }

    protected function createComponentAddForm() {
        $form = new Form;
        $form->addText('name', 'Názov')
             ->addRule(Form::FILLED, "Opa, zabudli ste vyplniť názov kola");
        $form->addSubmit('add', 'Pridať');
        $form->onSuccess[] = [$this, 'submittedAddForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentEditForm() {
        $form = new Form;
        $form->addText('name', 'Názov')
             ->addRule(Form::MAX_LENGTH, "Dĺžka názvu môže byť len 50 znakov", 50)
             ->setRequired("Názov je povinné pole");
        $form->addSubmit('edit', 'Upraviť')
             ->setAttribute('class', 'btn btn-large btn-success');
        $form->onSuccess[] = [$this, 'submittedEditForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentRemoveForm() {
        $form = new Form;
        $form->addSubmit('remove', 'Odstrániť')
             ->setAttribute('class', 'btn btn-large btn-danger');
        $form->addSubmit('cancel', 'Zrušiť')
             ->setAttribute('class', 'btn btn-large btn-warning')
             ->setAttribute('data-dismiss', 'close');
        $form->onSuccess[] = [$this, 'submittedRemoveForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddForm(Form $form, $values) {
        $this->roundsRepository->insert($values);
        $this->flashMessage('Kolo bolo pridané', 'success');
        $this->redirect('all');
    }

    public function submittedEditForm(Form $form, $values) {
        $this->roundRow->update($values);
        $this->flashMessage('Kolo bolo upravené', 'success');
        $this->redirect('view', $this->roundRow);
    }

    public function submittedRemoveForm() {
        $fights = $this->roundRow->related('fights');

        foreach ($fights as $fight) {
            $fight->delete();
        }

        $this->roundRow->delete();
        $this->flashMessage('Kolo bolo odstránené', 'success');
        $this->redirect('all');
    }
}
