<?php

use BaseController\BaseController;

/**
 * @property AuthModel $Auth
 * @property UserModel $User
 */
class AuthController extends BaseController {
    public array $excludeActions = [
        'login',
        'register',
        'loginView',
        'registerView',
        'logout',
    ];

    public function initialize(): void
    {
        $this->loadModel(['Auth', 'User']);
    }

    public function loginView()
    {
        $this->loadView('login');
    }

    public function registerView()
    {
        $this->loadView('register');
    }

    public function login()
    {
        $data = $this->request->post();

        if (empty($data['email']) && empty($data['phone']) && empty($data['password'])) return $this->exitJson('No data has been sent.', [], 'error');

        $user = $this->User->getUserByEmailOrPhone($data['email'] ?? '');

        if (empty($user)) return $this->exitJson('There is no account with this email.', [], 'error');
        if (!password_verify($data['password'], $user['password'])) return $this->exitJson('Incorect password.', [], 'error');

        unset($user['password']);

        $token = $this->Auth->refreshToken($user['id']);
        return $this->exitJson('You have successfuly loged in.', ['token' => $token, 'user' => $user]);
    }

    public function register()
    {
        $data = $this->request->post();

        if (empty($data['email'])) return $this->exitJson('Emailul este obligatoriu.', [], 'error');
        if (empty($data['password'])) return $this->exitJson('Parola este obligatorie.', [], 'error');
        if ($data['password'] !== $data['confirmPassword']) return $this->exitJson('Parolele nu corespund.', [], 'error');

        unset($data['confirmPassword']);

        $isValidEmail = $this->Auth->validateEmail($data['email']);
        if (!$isValidEmail) $this->exitJsonErr('Invalid email.');

        $isValidPassword = $this->Auth->validatePassword($data['password']);
        if (!$isValidPassword) $this->exitJsonErr('Password length must be higher than 8 characters.');

        $user = $this->User->getUserByEmailOrPhone($data['email']);
        if (!empty($user)) return $this->exitJson('There is already an user with this email.', [], 'error');
        
        $userId = $this->User->addUser($data);
        if (!$userId) return $this->exitJson('There was an error while trying to create the account.', [], 'error');
        
        $tokenId = $this->Auth->addToken($userId);
        if (!$tokenId) return $this->exitJson('Account created successfuly, please login.');

        $token = $this->Auth->getTokenNameById($tokenId);
        return $this->exitJson('Account successfuly created.', ['token' => $token]);
    }

    public function logout() {
        $userId = $this->getUser('id');
        $this->Auth->setTokenExpired($userId);
        return $this->exitJson('You have successfuly logged out.');
    }

    public function checkToken() {
        $userId = $this->getUser('id');
        $token = $this->Auth->getTokenNameByUserId($userId); // In case token got refreshed.
        $this->exitJson('', ['token' => $token]);
    }
}