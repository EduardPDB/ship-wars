<?php

require_once './app/base/BaseController.php';
require_once './app/base/BaseModel.php';
require_once './core/Core.php';
include_once './helpers/helpers.php';
require_once './server/Server.php';
require_once './server/ServerRequest.php';
require_once './server/SessionManager.php';
require_once './server/Router.php';
require_once './server/ControllerManagement.php';

date_default_timezone_set("Europe/Bucharest");

session_start();
use Server\Server;

$server = new Server();
$server->run();