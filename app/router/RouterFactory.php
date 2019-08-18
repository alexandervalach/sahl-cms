<?php

namespace App;

use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;
use Nette\Application\IRouter;

/**
 * Router factory.
 */
class RouterFactory {

    /**
     * @return IRouter
     */
    public function createRouter(): IRouter
    {
      $router = new RouteList();
      $router[] = new Route('admin', 'Sign:in');
      // $router[] = new Route('<presenter>/<action>[/<groupId>]', 'Teams:all');
      $router[] = new Route('<presenter>/<action>[/<id>][/<param>]', 'Homepage:all');
      return $router;
    }

}
