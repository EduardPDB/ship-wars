<?php

use BaseController\BaseController;

/**
 * @property UserModel $User
 * @property GameModel $Game
 */
class ProfileController extends BaseController {
	public array $excludeActions = [
        'stats',
        'index',
    ];

    public function initialize()
    {
        $this->loadModel(['User', 'Game']);
    }

    public function stats()
    {
        $this->loadView('profile/stats');
    }

    public function index()
    {
        $this->loadView('profile/index');
    }

    public function getStats()
    {
        $userId = $this->getUser('id');
        $games = $this->Game->getGamesPlayed($userId);
        $stats = $this->Game->stats($userId);
        $this->exitJson('', ['stats' => $stats, 'games' => $games]);
    }

    public function getProfile()
    {
        $user = $this->User->getUserDetails($this->getUser('id'));
        $this->exitJson('', $user);
    }

    public function update()
    {
        $userId = $this->getUser('id');
        $data = $this->request->post();
        $result = $this->User->update($userId, $data);
        if (!$result) $this->exitJsonErr('Profile could not updated.');
        $this->exitJson('Profile updated.');
    }
}