<?php

class ControllerManagement {
    private $controller;
    
    private string $action;

    public function __construct(string $controller, string $action)
    {
        $filePath = './app/controllers/' . $controller . '.php';

        if (!file_exists($filePath)) showError('error', ['message' => "File $filePath does not exist"]);

        require_once($filePath);

        if (!class_exists($controller))           showError('error', ['message' => "The controller $controller does not exist."]);
        if (!method_exists($controller, $action)) showError('error', ['message' => "The action $action does not exist."]);
        
        $defaultTable     = str_replace('Controller', '', $controller)  . 's';
        $controller       = new $controller();
        $this->action     = $action;
        $this->controller = $controller;
        $this->controller->defaultTable = $defaultTable;
    }

    public function execute()
    {
        if (!in_array($this->action, $this->controller->excludeActions)) {
            $allowedTypes = $this->controller->permissions[$this->action] ?? null;
            $this->controller->checkPermission($allowedTypes);
        }

        if (method_exists($this->controller, 'initialize'))  $this->controller->initialize();
        if (method_exists($this->controller, $this->action)) $this->controller->{$this->action}();
        if (method_exists($this->controller, 'finalize'))    $this->controller->finalize();
    }
}