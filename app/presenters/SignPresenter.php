<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;

/**
 * Sign in/out presenters.
 */
class SignPresenter extends BasePresenter {

    /**
     * Sign-in form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentSignInForm() {
        $form = new Form;
        $form->addText('username', 'Používateľské meno:')
             ->setRequired('Zadajte používateľské meno.');

        $form->addPassword('password', 'Heslo:')
             ->setRequired('Zadaj heslo.');

        $form->addSubmit('login', 'Prihlásiť');

        $form->onSuccess[] = $this->submittedSignInForm;
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedSignInForm(Form $form) {
        $values = $form->values;

        try {
            $this->getUser()->login($values->sahl_username, $values->sahl_password);
            $this->redirect('Homepage:');
        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError('Nesprávne meno alebo heslo.');
        }
    }

    public function actionOut() {
        $this->getUser()->logout();
        $this->flashMessage('Boli ste odhlásený.', 'success');
        $this->redirect('Homepage:');
    }

}
