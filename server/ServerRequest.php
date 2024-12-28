<?php

namespace ServerRequest;

class ServerRequest {
    private $uri;

    private $post;

    private $get;

    private $query;

    public function __construct()
    {
        $uri = parse_url($_SERVER['REQUEST_URI']);
        if (isset($uri['query'])) parse_str($uri['query'], $uri['query']);

        $this->query = $uri['query'] ?? [];
        $this->post  = $_POST;
        $this->get   = $_GET;
        $this->uri   = $uri;
    }

    public function post(string $name = '')
    {
        if (empty($name))              return $this->post;
        if (isset($this->post[$name])) return $this->post[$name];

        return null;
    }

    public function get(string $name = '')
    {
        if (empty($name))             return $this->get;
        if (isset($this->get[$name])) return $this->get[$name];
        
        return null;
    }

    public function query(string $name = '')
    {
        if (empty($name))               return $this->query;
        if (isset($this->query[$name])) return $this->query[$name];
        
        return null;
    }
}