<?php

namespace App\Presenters;

use App\FormHelper;
use Nette\Application\UI\Form;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\FileSystem;

class HomepagePresenter extends BasePresenter {

    /** @var array */
    private $side_table_types;

    public function renderAll() {
        $posts = $this->postsRepository->findAll()->order('id DESC')->limit(3);
        $side_tables = array();

        if ($this->side_table_types == null) {
            $this->side_table_types = $this->tableTypesRepository->findByValue('visible = ?', 1);

            foreach($this->side_table_types as $type) {
                $side_tables[$type->name] = $this->tablesRepository->findByValue('archive_id', null)
                                                                   ->where('type = ?', $type)
                                                                   ->order('points DESC, (score1 - score2) DESC');
            }
        }

        $this->template->sideRound = $this->roundsRepository->getLatestRound();
        $this->template->sideFights = $this->roundsRepository->getLatestFights();

        $this->template->side_table_types = $this->side_table_types;
        $this->template->side_tables = $side_tables;

        $this->template->posts = $posts;
        $this->template->default = $this->default_img;
        $this->template->imgFolder = $this->imgFolder;
    }

}
