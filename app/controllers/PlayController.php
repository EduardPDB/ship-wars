<?php

use BaseController\BaseController;

/**
 * @property UserModel $user
 * @property GameModel $game
 */
class PlayController extends BaseController {
	public array $excludeActions = [
        'index',
    ];

	public function initialize()
	{
		$this->loadModel(['user', 'game']);
	}

  	public function index()
  	{
		$this->loadView('index');
  	}

	public function getStarted()
	{
		$gameId  = $this->request->post('game_id');
		$user    = $this->getUser();
		$myTurn  = false;

		if ($gameId) {
			$game = $this->game->getGameById($gameId);
			if (!$game) 		   				$this->exitJsonErr('Jocul nu exista!');
			if ($game['user1'] === $user['id']) $this->exitJsonErr('Esti deja in acest meci!');
			if ($game['finished']) 			    $this->exitJsonErr('Meciul s-a incheiat!');
		}

		if ($gameId) {
			$this->game->updateGame($gameId, ['user2' => $user['id']]);
		} else {
			$date 	  = date('Y-m-d H:i:s');
			$gameData = [
				'user1' => $user['id'],
				'date'  => $date,
			];
			$gameId = $this->game->addGame($gameData);
			$myTurn = true;
		}

		$this->user->updateUser($user['id'], [
			'game_id' => $gameId,
			'in_game' => true,
			'my_turn' => $myTurn ? 1 : 0
		]);

		$data = [
			'gameId' => $gameId,
			'user'   => $user,
			'myTurn' => $myTurn,
		];

		$this->exitJson('', $data);
	}

	public function attack()
	{
		$field  = $this->request->post('field_id');
		$gameId = (string)$this->getUser('game_id');
		$userId = $this->getUser('id');
		
		$game = $this->game->getGameById($gameId);
		if ($userId === $game['user1']) {
			$this->user->updateUser($userId, ['my_turn' => 0]);
			$this->user->updateUser($game['user2'], ['my_turn' => 1]);
			$attackedUser = $game['user2'];
		}

		if ($userId === $game['user2']) {
			$this->user->updateUser($userId, ['my_turn' => 0]);
			$this->user->updateUser($game['user1'], ['my_turn' => 1]);
			$attackedUser = $game['user1'];
		}

		$shipHit = $this->game->getShipHit($gameId, $attackedUser, $field);
		$isHit   = !empty($shipHit) ? true : false;

		$attackData = [
			'game_id' => $gameId,
			'user_id' => $attackedUser,
			'field'   => $field,
			'hit'	  => $isHit ? 1 : 0,
		];
		$this->game->addAttack($attackData);

		$data['hit'] = $isHit;

		$this->exitJson("", $data);
	}

	public function checkMoves()
	{
		$user 		   = $this->getUser();
		$gameId 	   = $this->getUser('game_id');
		$userId 	   = $this->getUser('id');
		$fieldAttacked = $this->game->getLastAttackedByUserId($userId);
		[$user1, $user2] = $this->game->checkGameWin($gameId);

		if ($user1['win'] || $user2['win']) {
			$gameWinData['finished'] = 1;
			$gameWinData['user_win'] = $user1['win'] ? $user1['id'] : $user2['id'];
			$this->game->updateGame($gameId, $gameWinData);
		}
		$game 		   = $this->game->getGameById($gameId);
		$opponent      = $this->user->getUserById($game['user1'] === $userId ? $game['user2'] : $game['user1']);

		$data = [
			'fieldAttacked' => $fieldAttacked,
			'myTurn'   	    => $user['my_turn'] ? true : false,
			'gameStarted'   => $game['started'] ? true : false,
			'gamefinished'  => $game['finished'] ? true : false,
			'opponent'		=> $opponent['username'] ?? $opponent['email'] ?? false,
			'opponentLeft'  => false,
			'win'			=> $game['user_win'] === $userId ? true : false,
		];

		if (
			!empty($game['user_left']) && !empty($opponent['id']) && 
			$game['user_left'] === $opponent['id']
		) {
			$data['opponentLeft'] = true;
		}

		$this->exitJson("", $data);
	}

	public function placeShip()
	{
		$gameId 	= $this->getUser('game_id');
		$userId 	= $this->getUser('id');
		$shipFields = $this->request->post('shipFields');

		if (empty($shipFields)) $this->exitJsonErr("WTF!");

		$ship = $this->game->getShipByGameIdAndField($gameId, $userId, $shipFields);
		if ($ship) $this->exitJsonErr('Baaa cf?<br>Pui o nava una peste alta?');

		$shipData = [
			'user_id' => $userId,
			'game_id' => $gameId,
			'fields'  => $shipFields,
		];

		$result = $this->game->placeShip($shipData);
		if (!$result) $this->exitJsonErr("Mai incearca sa pui inca o data vaporul!");

		$gameStarted = $this->game->checkGameStarted($gameId);
		if ($gameStarted) $this->game->updateGame($gameId, ['started' => 1]);

		$this->exitJson();
	}

	public function playerQuit()
	{
		$gameId = $this->getUser('game_id');
		$userId = $this->getUser('id');
		
		$this->game->setUserLeftGame($gameId, $userId);
		$this->exitJson('A dat ragequite inamicul');
	}
}