<?php

namespace App\BreadCrumb;

use Nette\Application\UI\Control;
use Nette\Application\UI\Link;

class BreadCrumb extends Control {

	/** @var array links */
	public $links = array();

	public function render() {
		$this->template->setFile(__DIR__ . "/BreadCrumb.latte");
		$this->template->links = $this->links;
		$this->template->render();
	}

	/**
	 * Add link
	 * 
	 * @param $title
	 * @param Link $link
	 */
	public function addLink($title, $link = NULL) {
		$this->links[md5($title)] = array(
			'title' => $title, 
			'link' => $link,
		);
	}

}