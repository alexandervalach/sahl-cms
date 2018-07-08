<?php

namespace App\Services;

class WebDir {

	private $wwwDir;

	public function __construct($wwwDir) {
		$this->wwwDir = $wwwDir;
	}

	public function getPath($fromBaseDir = '') {
		return $this->wwwDir. DIRECTORY_SEPARATOR . $fromBaseDir;
	}

}