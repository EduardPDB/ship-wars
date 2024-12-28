<?php

namespace Router;

class Router {
    private $routes = [];

    private $url;

    private $mapedAction;

    private $controller;

    private $action;

    public function __construct() {

        $this->url = $_SERVER['REQUEST_URI'];

        $this->routes = [
            '/'                   => 'Play',
            '/api/getStarted'     => 'Play.getStarted',
            '/api/checkMoves'     => 'Play.checkMoves',

            '/login'              => 'Auth.loginView',
            '/register'           => 'Auth.registerView',

            '/api/login'          => 'Auth.login',
            '/api/register'       => 'Auth.register',
            '/api/logout'         => 'Auth.logout',
            '/api/checkToken'     => 'Auth.checkToken',

            '/api/attack'         => 'Play.attack',
            '/api/placeShip'      => 'Play.placeShip',
            '/api/playerQuit'     => 'Play.playerQuit',

            '/profile'            => 'Profile',
            '/stats'              => 'Profile.stats',
            '/api/getStats'       => 'Profile.getStats',
            '/api/profile'        => 'Profile.getProfile',
            '/api/updateProfile'  => 'Profile.update',
        ];
    }

    public function getAction(): array
    {
        $env = parse_ini_file('.env');
        $uri = parse_url($this->url);

        if ($env['LOCAL'] == 'local') $uri['path'] = str_replace($env['PROJECT_NAME'] . '/', '', $uri['path']);
        if (!array_key_exists($uri['path'], $this->routes)) showError('error', ['message' => "The route {$uri['path']} does not exist!"]);

        $request          = explode('.', $this->routes[$uri['path']]);
        $this->controller = $request[0] . 'Controller';
        $this->action     = $request[1] ?? 'index';

        $this->mapedAction = [
            'controller' => $this->controller,
            'action' => $this->action
        ];

        return $this->mapedAction;
    }
}