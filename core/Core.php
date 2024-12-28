<?php

namespace Core;

require_once './database/DataBase.php';

use DataBase;
use ServerRequest\ServerRequest;
use SessionManager\SessionManager;

/**
 * @property \app\models\AuthModel $auth
 * @property UserModel $user
 */
class Core extends DataBase
{
    /**
     * Request data.
     */
    protected $request;

    protected $session;

    protected $authUser;

    public function __construct()
    {
        parent::__construct();

        $this->request = new ServerRequest;
        $this->session = new SessionManager;
    }

    public function checkPermission($action): void
    {
        $token = $this->request->get('token');
        $this->loadModel('auth');
        $result = $this->auth->checkToken($token, $action);
        if ($result['status'] !== 'ok') {
            $this->exitJsonErr($result['message']);
        }

        $userId = $result['data'];

        $this->loadModel('user');
        $user = $this->user->getUserById($userId);

        unset($user['password']);

        $this->authUser = $user;
        $this->dbUserId = $userId;
    }

    /**
     * Get the loged user details, if there is no data return null.
     * 
     * @param string $index Get the data at a specific index.
     * @return mixed[]|null
     */
    public function getUser($index = null)
    {
        if (!$index)                        return $this->authUser;
        if (isset($this->authUser[$index])) return $this->authUser[$index];

        return null;
    }

    /**
     * Load the view from the views folder.
     * 
     * @param string $fileName The name of the view that you need to load.
     * @param array $data The data parsed in the view.
     * @return string
     */
    public function loadView(string $fileName, array $data = []): string
    {
        $filePath = "./app/views/$fileName.php";
        
        if (!file_exists($filePath)) showError('error', ['message' => "View $fileName not found!"]);
        extract($data);

        include $filePath;
        $view = file_get_contents($filePath, false);

        return $view;
    }

    /**
     * Load the model from the models folder.
     * No need to use NameModel, only Name.
     * 
     * @param string|array $model The name of the model(s) that you need to load.
     * @param string|array $nickname If you want to name the loaded model(s) in any other way.
     * @return void
     */
    public function loadModel($models, $nickname = ''): void
    {
        if (is_string($models)) {
            $modelName = $models;
            $model     = ucfirst(strtolower($models)) . 'Model';
            $filePath  = './app/models/' . $model . '.php';
    
            if (!file_exists($filePath)) showError('error', ['message' => "The file $model does not exist!"]);
    
            require_once($filePath);
    
            if (!class_exists($model)) showError('error', ['message' => "The model $model does not exist!"]);
    
            if ((class_exists($model)) && $nickname) {
                $this->{$nickname} = new $model;

                $defaultTable = strtolower($modelName) . 's';

                $this->{$nickname}->defaultTable = $defaultTable;
                $this->{$nickname}->authUser = $this->getUser();
                $this->{$nickname}->dbUserId = $this->getUser('id');

                if (method_exists($this->{$nickname}, 'initialize')) $this->{$nickname}->initialize();
            } else if (class_exists($model)) {
                $this->{$modelName} = new $model;

                $defaultTable = strtolower($modelName) . 's';

                $this->{$modelName}->defaultTable = $defaultTable;
                $this->{$modelName}->authUser = $this->getUser();
                $this->{$modelName}->dbUserId = $this->getUser('id');

                if (method_exists($this->{$modelName}, 'initialize')) $this->{$modelName}->initialize();
            }
        }

        if (is_array($models)) {
            foreach($models as $originalModel => $modelAlias) {
                if (is_string($originalModel)) {
                    $modelName = $originalModel;
                    $model     = ucfirst(strtolower($originalModel)) . 'Model';
                } else {
                    $modelName = $modelAlias;
                    $model     = ucfirst(strtolower($modelAlias)) . 'Model';
                }
                $filePath  = './app/models/' . $model . '.php';
        
                if (!file_exists($filePath)) showError('error', ['message' => "The file $model does not exist!"]);
        
                require_once($filePath);
        
                if (!class_exists($model)) showError('error', ['message' => "The model $model does not exist!"]);
        
                if (is_string($modelAlias)) {
                    $this->{$modelAlias} = new $model;

                    $defaultTable = strtolower($modelName) . 's';

                    $this->{$modelAlias}->defaultTable = $defaultTable;
                    $this->{$modelAlias}->authUser     = $this->getUser();
                    $this->{$modelAlias}->dbUserId     = $this->getUser('id');

                    if (method_exists($this->{$modelAlias}, 'initialize')) $this->{$modelAlias}->initialize();
                } else {
                    $this->{$modelName} = new $model;

                    $defaultTable = strtolower($modelName) . 's';
                    
                    $this->{$modelName}->defaultTable = $defaultTable;
                    $this->{$modelName}->authUser     = $this->getUser();
                    $this->{$modelName}->dbUserId     = $this->getUser('id');

                    if (method_exists($this->{$modelName}, 'initialize')) $this->{$modelName}->initialize();
                }
            }
        }
    }

    /**
     * Send the data in json format.
     * 
     * @param string $message The message the user will receive.
     * @param array  $data    The data that you like to send.
     * @param string $status  The status of the request.
     * @return void
     */
    public function exitJson($message = '', $data = [], $status = 'ok'): void
    {
        echo json_encode([
            'status'  => $status,
            'message' => $message,
            'data'    => $data
        ]);
        exit();
    }

    public function isApi()
    {
        if (
            strpos($_SERVER['REQUEST_URI'], '/api/') !== false || // URL pattern
            $_SERVER['HTTP_ACCEPT'] === 'application/json' || // JSON Accept header
            $_SERVER['CONTENT_TYPE'] === 'application/json' || // JSON Content-Type
            (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') // AJAX request
        ) {
            return true;
        }
        return false;
    }

    /**
     * Send error response with message.
     * 
     * @param string $message The message the user will receive.
     * @return void
     */
    public function exitJsonErr($message = ''): void
    {
        echo json_encode([
            'status'  => 'error',
            'message' => $message,
            'data'    => []
        ]);
        exit();
    }
}