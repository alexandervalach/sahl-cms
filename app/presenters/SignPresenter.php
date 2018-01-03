<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;

/**
 * Sign in/out presenters.
 */
class SignPresenter extends BasePresenter {

    /**
     * Sign-in form factory.
     * @return Form
     */
    protected function createComponentSignInForm() {
        $form = new Form;
        $form->addText('username', 'Používateľské meno')
             ->setRequired('Zadajte používateľské meno.');
        $form->addPassword('password', 'Heslo')
             ->setRequired('Zadajte heslo.');
        $form->addSubmit('login', 'Administrácia')
             ->setAttribute('class', 'btn btn-success');
        $form->addProtection();
        $form->onSuccess[] = [$this, 'submittedSignInForm'];
        FormHelper::setBootstrapFormRenderer($form);
        return $form;
    }

    public function submittedSignInForm(Form $form, $values) {
        try {
            $this->getUser()->login($values->username, $values->password);
            $this->flashMessage('Vitajte v administrácii SAHL', 'success');
            $this->redirect('Posts:all');
        } catch (AuthenticationException $e) {
            $form->addError('Nesprávne meno alebo heslo');
        }
    }

    public function actionOut() {
        $this->getUser()->logout();
        $this->flashMessage('Boli ste odhlásený', 'success');
        $this->redirect('Posts:all');
    }

}
