<?php

namespace BaseController;

require_once './core/Core.php';

use Core\Core;

class BaseController extends Core
{
    // Example on how to properly use $permissions
    //
    // public $permissions = [
    //     'action1' => [
    //         'user_type1',
    //         'user_type2',
    //         'user_type3',
    //     ],
    //     'action2' => [
    //         'user_type1',
    //         'user_type2',
    //         'user_type3',
    //     ],
    // ];
    public $permissions = null;
    
    // Example on how to use helpers
    // 
    // public $features = [
    //     'dbBlameDate' => true,
    //     'dbBlameUser' => true,
    // ];
    public $features = null;

    // Example on how to use exclude actions from check permissions
    // public ?$excludeActions = [
    //     'test',
    //     'test1',
    // ]
    public array $excludeActions = [];

    public function __construct()
    {
        parent::__construct();

        if ($this->features)
        foreach($this->features as $feature => $value) {
            $this->{$feature} = $value;
        }
    }
}