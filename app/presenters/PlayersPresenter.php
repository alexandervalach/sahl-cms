<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;

class PlayersPresenter extends BasePresenter {

    const PLAYER_NOT_FOUND = "Player not found";
    const TEAM_NOT_FOUND = "Team not found";

    /** @var ActiveRow */
    private $playerRow;

    /** @var ActiveRow */
    private $teamRow;

    /** @var ActiveRow */
    private $archRow;

    public function renderAll() {
        $this->template->players = $this->playersRepository->findByValue('archive_id', null)
                ->where('name NOT LIKE ?', 'voľné miesto%')
                ->where('name != ?', ' ')
                ->order('goals DESC, name DESC');
        $this->template->i = 0;
        $this->template->j = 0;
        $this->template->current = 0;
        $this->template->previous = 0;

        if ($this->user->isLoggedIn()) {
            $this->getComponent('resetForm');
        }
    }

    public function actionView($id) {
        $this->playerRow = $this->playersRepository->findById($id);

        if (!$this->playerRow) {
            throw new BadRequestException(self::PLAYER_NOT_FOUND);
        }

        $this->teamRow = $this->teamsRepository->findById($this->playerRow->team_id);
    }

    public function renderView($id) {
        if (!$this->teamRow) {
            throw new BadRequestException(self::TEAM_NOT_FOUND);
        }

        $this->template->player = $this->playerRow;
        $this->template->team = $this->teamRow;
        $this->template->goals_count = $this->goalsRepository->getPlayerGoalsCount($id);
        $this->template->type = $this->playerTypesRepository->findById($this->playerRow->type_id);

        if ($this->user->isLoggedIn()) {
            $this->getComponent(self::EDIT_FORM)->setDefaults($this->playerRow);
            $this->getComponent(self::REMOVE_FORM);
        }
    }

    public function actionArchAll($id) {
        $this->archRow = $this->seasonsRepository->findById($id);
    }

    public function renderArchAll($id) {
        $this->template->stats = $this->playersRepository->getArchived($id)
                ->where('name != ?', ' ')
                ->order('goals DESC, name DESC');
        $this->template->archive = $this->archRow;
        $this->template->i = 0;
        $this->template->j = 0;
        $this->template->current = 0;
        $this->template->previous = 0;
    }

    public function actionArchView($id, $param) {
        $this->teamRow = $this->teamsRepository->findById($param);
    }

    public function renderArchView($id, $param) {
        if (!$this->teamRow) {
            throw new BadRequestException($this->error);
        }
        $this->template->players = $this->playersRepository->findByValue('team_id', $param)->where('archive_id', $id)->where('NOT type_id', 2);
        $this->template->goalies = $players = $this->playersRepository->findByValue('team_id', $param)->where('archive_id', $id)->where('type_id', 2);
        $this->template->team = $this->teamRow;
        $this->template->archive = $this->teamRow->ref('archive', 'archive_id');
    }

    protected function createComponentEditForm() {
        $types = $this->playerTypesRepository->getTypes();

        $form = new Form;
        $form->addText('name', 'Meno a priezvisko')
             ->setAttribute('placeholder', 'Zdeno Chára')
             ->addRule(Form::FILLED, 'Meno musí byť vyplnené');
        $form->addText('num', 'Číslo')
             ->setAttribute('placeholder', 14);
        $form->addText('goals', 'Góly');
        $form->addCheckbox('trans', ' Prestupový hráč');
        $form->addSelect('type_id', 'Typ hráča', $types);
        $form->addSubmit('edit', 'Uložiť')
             ->setAttribute('class', 'btn btn-large btn-success');
        $form->addSubmit('cancel', 'Zrušiť')
             ->setAttribute('class', 'btn btn-large btn-warning')
             ->setAttribute('data-dismiss', 'modal');
        $form->onSuccess[] = [$this, self::SUBMITTED_EDIT_FORM];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    protected function createComponentResetForm() {
        $form = new Form;
        $form->addSubmit('reset', 'Vynulovať')
             ->setAttribute('class', self::BTN_DANGER)
             ->onClick[] = $this->submittedResetForm;
        $form->addSubmit('cancel', 'Zrušiť')
             ->setAttribute('class', self::BTN_WARNING)
             ->setAttribute('data-dismiss', 'modal');
        $form->addProtection();
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedEditForm(Form $form, $values) {
        $this->playerRow->update($values);
        $this->flashMessage('Hráč bol upravený', self::SUCCESS);
        $this->redirect('view', $this->playerRow);
    }

    public function submittedRemoveForm() {
        $team = $this->playerRow->team_id;
        $this->playerRow->delete();
        $this->flashMessage('Hráč bol odstránený.', self::SUCCESS);
        $this->redirect('Teams:view', $team);
    }

    public function submittedResetForm() {
        $players = $this->playersRepository
                ->findByValue('archive_id', null)
                ->where('goals != ?', 0);
        $values = array('goals' => 0);
        foreach ($players as $player) {
            $player->update($values);
        }
        $this->redirect('all');
    }

}
