<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;

class RoundPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $roundRow;

    public function actionAll() {
        
    }

    public function renderAll() {
        $this->template->rounds = $this->roundsRepository->findAll();
    }

    protected function createComponentAddRoundForm() {
        $form = new Form;
        $form->addText('name', 'Názov')
            ->addRule(Form::MAX_LENGTH, "Dĺžka názvu môže byť len 50 znakov", 50)
            ->setRequired("Názov je povinné pole");
        $form->addSubmit('save', 'Uložiť');
        $form->onSuccess[] = $this->submittedAddRoundForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedAddRoundForm(Form $form) {
        $values = $form->getValues();
        $this->roundsRepository->insert($values);
        $this->redirect('all');
    }

    public function formCancelled() {
        $this->redirect('all');
    }

}
