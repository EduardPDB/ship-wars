<?php

namespace Server;

use ControllerManagement;
use Router\Router;

class Server {
    public function __construct()
    {
        
    }

    public function run(): void
    {
        $Routes = new Router();
        $mapedAction = $Routes->getAction();

        $ControllerMng = new ControllerManagement($mapedAction['controller'], $mapedAction['action']);
        $ControllerMng->execute();
        
        exit();
    }
}