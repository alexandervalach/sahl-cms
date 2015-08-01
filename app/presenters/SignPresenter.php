<?php

namespace App\Presenters;

use Nette;

/**
 * Sign in/out presenters.
 */
class SignPresenter extends BasePresenter
{

	/**
	 * Sign-in form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm()
	{
		$form = new Nette\Application\UI\Form;
		$form->addText('username', 'Užívateľské meno:')
			 ->setRequired('Zadaj, prosím, užívateľské meno.');

		$form->addPassword('password', 'Heslo:')
			 ->setRequired('Zadaj, prosím, heslo.');

		$form->addSubmit('send', 'Prihlásiť');

		// call method signInFormSucceeded() on success
		$form->onSuccess[] = $this->submittedSignInForm;

		return $form;
	}


	public function submittedSignInForm($form)
	{
		$values = $form->values;

		try {
			$this->getUser()->login($values->username, $values->password);
			$this->redirect('Homepage:');

		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError('Nesprávne meno alebo heslo.');
		}
	}


	public function actionOut()
	{
		$this->getUser()->logout();
		$this->flashMessage('Boli ste odhlásený.','success');
		$this->redirect('Homepage:');
	}

}
