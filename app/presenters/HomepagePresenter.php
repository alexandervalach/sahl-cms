<?php

namespace App\Presenters;

use Nette;
use IPub\VisualPaginator\Components as VisualPaginator;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter {

    public function renderDefault() {
        $limit = 4;
        $postSelection = $this->postsRepository->findAll()->order('id DESC');

        $latests = $this->postsRepository->findAll()->order('id DESC')->limit($limit);

        $visualPaginator = $this->getComponent('visualPaginator');
        $paginator = $visualPaginator->getPaginator();
        $paginator->itemsPerPage = 5;
        $paginator->itemCount = $postSelection->count();
        $postSelection->limit($paginator->itemsPerPage, $paginator->offset);

        $this->template->posts = $postSelection;
        $this->template->latests = $latests;
        $this->template->default = "sahl.png";
        $this->template->imgFolder = $this->imgFolder;
    }

    protected function createComponentVisualPaginator() {
        $control = new VisualPaginator\Control;
        $control->setTemplateFile('bootstrap.latte');
        $control->disableAjax();
        return $control;
    }

}
