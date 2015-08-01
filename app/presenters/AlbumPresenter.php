<?php

namespace App\Presenters;

use Nette\Database\Table\ActiveRow;

class AlbumPresenter extends BasePresenter{
    /** @var ActiveRow */
    private $albumRow;
    
    public function actionAll() {
    }
    
    public function renderAll() {
        $this->template->albums = $this->albumsRepository->findAll();
    }
}
