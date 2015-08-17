<?php

namespace App\Presenters;

use Nette\Database\Table\ActiveRow;

class ReplyPresenter extends BasePresenter {

    /** @var ActiveRow */
    private $replyRow;

    /** @var string */
    private $error = "Reply not found!";

    public function actionAdd($id) {
        
    }

    public function renderAdd($id) {
        
    }

    public function actionDelete($id) {
        
    }

    public function renderDelete($id) {
        
    }
    
    public function formCancelled() {
        $this->redirect('Forum:all');
    }

}
