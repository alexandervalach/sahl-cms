<?php

namespace App\Presenters;

use Nette;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter {

    public function renderDefault() {
        $posts = $this->postsRepository->findAll()->order('id DESC');
        $slider = $this->postsRepository->findAll()->order('id DESC')->limit(3);

        $this->template->posts = $posts;
        $this->template->slider = $slider;
        $this->template->default = $this->default_img;
        $this->template->imgFolder = $this->imgFolder;
    }

}
