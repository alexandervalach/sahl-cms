<?php

namespace App\Presenters;

class HomepagePresenter extends BasePresenter {

    /** @var array */
    private $sideTableTypes;

    public function renderAll() {
        $posts = $this->postsRepository->findAll()->order('id DESC')->limit(3);
        $sideTables = array();

        if ($this->sideTableTypes == null) {
            $this->sideTableTypes = $this->tableTypesRepository->findByValue('visible = ?', 1);

            foreach ($this->sideTableTypes as $type) {
                $sideTables[$type->name] = $this->tablesRepository->findByValue('archive_id', null)
                        ->where('type = ?', $type)
                        ->order('points DESC, (score1 - score2) DESC');
            }
        }

        $this->template->sideRound = $this->roundsRepository->getLatestRound();
        $this->template->sideFights = $this->roundsRepository->getLatestFights();

        $this->template->sideTableTypes = $this->sideTableTypes;
        $this->template->sideTables = $sideTables;

        $this->template->posts = $posts;
    }

}
