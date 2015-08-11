<?php

namespace App\Presenters;

use Nette;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter {

    /** @var Nette\Database\Selection */
    private $post;

    public function renderDefault() {
        $limit = 4;
        $posts = $this->postsRepository->findAll();

        $latests = $posts->order('created_at ASC')->limit($limit);

        $this->template->posts = $posts;
        $this->template->latests = $latests;
        $this->template->default = "/images/sahl.jpg";
        $this->template->imgFolder = $this->imgFolder;
    }

}
