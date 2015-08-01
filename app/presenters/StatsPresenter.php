<?php

namespace App\Presenters;

use Nette\Database\Table\Selection;

class StatsPresenter extends BasePresenter
{
	/** $var Selection */
	private $playerSelection;

	public function actionDefault() {
		$this->playerSelection = $this->playersRepository->findAll()->order('goals DESC, fights ASC'); 
	}

	public function renderDefault() {
		$this->template->stats = $this->playerSelection;
	}

}

