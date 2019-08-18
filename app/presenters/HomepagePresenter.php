<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\Model\GroupsRepository;
use App\Model\LinksRepository;
use App\Model\SeasonsGroupsRepository;
use App\Model\SponsorsRepository;
use App\Model\TeamsRepository;
use App\Model\PostsRepository;
use App\Model\RoundsRepository;
use App\Model\SeasonsGroupsTeamsRepository;
use App\Model\TableTypesRepository;

/**
 * Class HomepagePresenter
 * @package App\Presenters
 */
class HomepagePresenter extends BasePresenter
{
  /** @var PostsRepository */
  private $postsRepository;

  /** @var TableTypesRepository */
  private $tableTypesRepository;

  /** @var RoundsRepository */
  private $roundsRepository;

  /**
   * HomepagePresenter constructor.
   * @param GroupsRepository $groupsRepository
   * @param LinksRepository $linksRepository
   * @param SponsorsRepository $sponsorsRepository
   * @param TeamsRepository $teamsRepository
   * @param PostsRepository $postsRepository
   * @param TableTypesRepository $tableTypesRepository
   * @param RoundsRepository $roundsRepository
   * @param SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository
   * @param SeasonsGroupsRepository $seasonsGroupsRepository
   */
  public function __construct(
      GroupsRepository $groupsRepository,
      LinksRepository $linksRepository,
      SponsorsRepository $sponsorsRepository,
      TeamsRepository $teamsRepository,
      PostsRepository $postsRepository,
      TableTypesRepository $tableTypesRepository,
      RoundsRepository $roundsRepository,
      SeasonsGroupsTeamsRepository $seasonsGroupsTeamsRepository,
      SeasonsGroupsRepository $seasonsGroupsRepository
  )
  {
    parent::__construct($groupsRepository, $linksRepository, $sponsorsRepository, $teamsRepository,
        $seasonsGroupsRepository, $seasonsGroupsTeamsRepository);
    $this->postsRepository = $postsRepository;
    $this->tableTypesRepository = $tableTypesRepository;
    $this->roundsRepository = $roundsRepository;
  }

  /**
   *
   */
  public function actionAll(): void
  {

  }

  /**
   *
   */
  public function renderAll(): void
  {
    $posts = $this->postsRepository->getLatestPosts();
    $sideTables = array();
    $sideTableTypes = $this->tableTypesRepository->getTableTypes();

    /*
    foreach ($sideTableTypes as $type) {
      $sideTables[$type->name] = $this->tablesRepository->findByValue('archive_id', null)
              ->where('type = ?', $type)
              ->order('points DESC, (score1 - score2) DESC');
    }
    */

    $sideFights = $this->roundsRepository->getLatestFights();
    $sideRound = $this->roundsRepository->getLatestRound();
    $this->template->sideRound = $sideRound;
    $this->template->sideFights = $sideFights;

    if ($sideFights) {
      $this->template->sideFightsCount = $sideFights->count();
    } else {
      $this->template->sideFightsCount = 0;
    }

    $this->template->sideTableTypes = $sideTableTypes;
    $this->template->sideTables = $sideTables;
    $this->template->posts = $posts;
  }

}
