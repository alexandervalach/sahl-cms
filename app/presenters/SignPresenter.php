<?php

declare(strict_types=1);

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use Nette\Utils\ArrayHash;

class SignPresenter extends BasePresenter {

  public function actionIn(): void
  {
    if ($this->user->isLoggedIn()) {
      $this->redirect('Homepage:all');
    }
  }

  public function renderIn(): void
  {
    $posts = $this->postsRepository->getLatestPosts();
    $sideTables = array();
    $sideTableTypes = $this->tableTypesRepository->getTableTypes();

    /*
    foreach ($sideTableTypes as $type) {
      $sideTables[$type->name] = $this->tablesRepository->findByValue('archive_id', null)
              ->where('type = ?', $type)
              ->order('points DESC, (score1 - score2) DESC');
    }
    */

    $sideFights = $this->roundsRepository->getLatestFights();
    $sideRound = $this->roundsRepository->getLatestRound();
    $this->template->sideRound = $sideRound;
    $this->template->sideFights = $sideFights;

    if ($sideFights) {
      $this->template->sideFightsCount = $sideFights->count();
    } else {
      $this->template->sideFightsCount = 0;
    }

    $this->template->sideTableTypes = $sideTableTypes;
    $this->template->sideTables = $sideTables;
    $this->template->posts = $posts;
  }

  /**
   * Component for creating a sign in form
   * @return Form
   */
  protected function createComponentSignInForm(): Form
  {
    $form = new Form;
    $form->addText('username', 'Používateľské meno')
            ->setRequired('Zadajte používateľské meno');
    $form->addPassword('password', 'Heslo')
            ->setRequired('Zadajte heslo');
    $form->addCheckbox('remember', ' Zapamätať si ma, kým nezavriem prehliadač');
    $form->addSubmit('login', 'Prihlásiť');
    $form->addProtection(self::CSRF_TOKEN_EXPIRED);
    $form->onSuccess[] = [$this, 'submittedSignInForm'];
    FormHelper::setBootstrapFormRenderer($form);
    return $form;
  }

  /**
   * Checking whether user exists
   *
   * @param Form $form
   * @param array $values
   */
  public function submittedSignInForm(Form $form, ArrayHash $values): Form
  {
    if ($values->remember) {
      $this->user->setExpiration(null, 0);
    } else {
      $this->user->setExpiration('30 minutes', 0);
    }

    try {
      $this->user->login($values->username, $values->password);
      $this->flashMessage('Vitajte v administrácií SAHL', self::SUCCESS);
      $this->redirect('Homepage:all');
    } catch (AuthenticationException $e) {
      $this->flashMessage('Nesprávne meno alebo heslo', self::DANGER);
      $this->redirect('Homepage:all');
    }
  }

  /**
   * Log out action routing
   */
  public function actionOut(): void
  {
    $this->getUser()->logout();
    $this->flashMessage('Boli ste odhlásený', self::SUCCESS);
    $this->redirect('Homepage:all');
  }

}
