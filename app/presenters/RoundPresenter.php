<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Database\Table\ActiveRow;

class RoundPresenter extends BasePresenter{
    /** @var ActiveRow */
    private $roundRow;
    
    public function actionAll() {
   
    }
    
    public function renderAll() {
        $this->template->rounds = $this->roundsRepository->findAll();
    }
    
    public function formCancelled() {
        $this->redirect('all');
    }
}
