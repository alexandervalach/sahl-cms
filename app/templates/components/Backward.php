<?php

namespace App\Components;

use Nette\Application\UI\Control;

class Backward extends Control {

    public function render($link, $title, $args = null) {
        $this->template->setFile(__DIR__ . '/Backward.latte');
        $this->template->link = $link;
        $this->template->title = $title;
        $this->template->args = $args;
        $this->template->render();
    }

}
