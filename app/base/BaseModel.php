<?php

namespace BaseModel;

use Core\Core;

class BaseModel extends Core
{
    public function __construct() {
        parent::__construct();

        if ($this->features)
        foreach($this->features as $feature => $value) {
            $this->{$feature} = $value;
        }
    }

    public $features = null;

    // Example on how to use helpers
    // 
    // public $features = [
    //     'dbBlameDate' => false,
    //     'dbBlameUser' => false,
    // ];
    
    /**
     * Return the status from the model.
     * 
     * @param string $message The message
     * @param array  $data    The data.
     * @param string $status  The status.
     * 
     * @return array
     */
    public function status($message = '', $data = [], $status = 'ok'): array
    {
        return ['message' => $message, 'data' => $data, 'status' => $status];
    }
}