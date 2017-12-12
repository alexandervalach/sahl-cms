<?php

namespace App\Presenters;

use Nette;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter {

	/** @var array */
	protected $side_table_types;

    public function renderDefault() {
        $posts = $this->postsRepository->findAll()->order('id DESC')->limit(6);        

        if ($this->side_table_types == null) {
            $this->side_table_types = $this->tableTypesRepository->findByValue('visible = ?', 1);

            foreach($this->side_table_types as $type) {
                $side_tables[$type->name] = $this->tablesRepository->findByValue('archive_id', null)
                                                                   ->where('type = ?', $type)
                                                                   ->order('points DESC');
            }
        }
        
        $this->template->links = $this->linksRepository->findByValue('sponsor', 0)->order('title');
        $this->template->sponsors = $this->linksRepository->getSponsors();
        $this->template->imgFolder = $this->imgFolder;
        $n_teams = $this->teamsRepository->findByValue('archive_id', NULL)->order('id');
        $this->template->n_teams = $n_teams;
        $this->template->teams_count = $n_teams->count();
        $this->template->sideRound = $this->roundsRepository->getLatestRound();
        $this->template->sideFights = $this->roundsRepository->getLatestRoundFights();
       	$this->template->side_table_types = $this->side_table_types;
        $this->template->side_tables = $side_tables;
        $this->template->posts = $posts;
        $this->template->default = $this->default_img;
        $this->template->imgFolder = $this->imgFolder;
    }

}
