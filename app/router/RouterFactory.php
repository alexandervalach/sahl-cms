<?php

namespace App;

use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;

/**
 * Router factory.
 */
class RouterFactory {

    /**
     * @return \Nette\Application\IRouter
     */
    public function createRouter() {
        $router = new RouteList();
        $router[] = new Route('admin', 'Sign:in');
        $router[] = new Route('<presenter>/<action>[/<id>][/<param>]', 'Homepage:all');
        return $router;
    }

}
