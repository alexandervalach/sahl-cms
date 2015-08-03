<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Application\BadRequetsException;

class TeamsPresenter extends BasePresenter {

    /** @var string */
    private $error = "Team not found";

    public function actionCreate() {
        $this->userIsLogged();
    }

    public function actionDelete($id) {
        $this->userIsLogged();

        $this->template->team = $this->teamsRepository->findById($id);

        if (!$this->template->team) {
            throw new BadRequestException($this->error);
        }
    }

    public function actionEdit($id) {
        $this->userIsLogged();
    }

    public function renderView() {
        $team = $this->teamsRepository->findAll()->order("name ASC");

        $this->template->teams = $team;
    }

    public function renderEdit() {
        $team = $this->teamsRepository->findById($id);

        if (!$team)
            throw new BadRequestException($this->error);

        $this->getComponent('addTeamForm')->setDefaults($team);
    }

    protected function createComponentAddTeamForm() {
        $form = new Form;

        $form->addText('name', 'Tím: ')
                ->setRequired('Názov tímu je povinné pole.')
                ->addRule(Form::MAX_LENGTH, "Dĺžka reťazce smie byť len 255 znakov.", 255);

        $form->addSubmit('save', 'Uložiť');

        $form->onSuccess[] = $this->teamFormSucceeded;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedDeleteForm() {
        $id = $this->getParameter('id');
        $team = $this->teamsRepository->findById($id);
        $players = $team->related('players');

        foreach ($players as $player) {
            $player->delete();
        }

        $team->delete();

        $this->flashMessage('Tím bol zmazaný.', 'success');
        $this->redirect('view');
    }

    public function formCancelled() {
        $this->redirect('view');
    }

    public function teamFormSucceeded($form) {
        $this->userIsLogged();

        $values = $form->getValues();
        $id = $this->getParameter('id');
        $teamRow = $this->teamsRepository;

        if (!$id) {
            $teamRow->insert($values);
        } else {
            $teamRow->findById($id)->update($values);
        }

        $this->redirect('view');
    }

}
