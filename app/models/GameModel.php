<?php

use BaseModel\BaseModel;

class GameModel extends BaseModel {
    public function getGameByUser($userId = null): ?array
    {
        if (!$userId) return null;

        $sql = "
            SELECT * FROM games
            WHERE user1 = $userId
            OR    user2 = $userId
            AND   finished = 0
        ";

        return $this->dbQuery($sql, false);
    }

    public function getGameById($id = ''): ?array
    {
        if (!$id) return null;
        
        return $this->dbSelectDef(['id' => $id], false);
    }

    public function deleteGameWhereId($id = ''): bool
    {
        return $this->dbDeleteDef($id);
    }

    public function addGame($data)
    {
        if (empty($data)) return false;

        $this->dbInsertDef($data);

        return $this->getInsertedId();
    }

    public function updateGame($id = '', $data = ''): bool
    {
        return $this->dbUpdateDef($data, ['id' => $id]);
    }

    public function addAttack($data)
    {
        if (empty($data)) return false;

        $this->dbInsert('attacks', $data);

        return $this->getInsertedId();
    }

    public function getLastAttackedByUserId($userId)
    {
        if (empty($userId)) return false;

        $sql = "
            SELECT id,
                   field
            FROM attacks
            WHERE user_id = $userId
            AND placed = 0
            ORDER BY id DESC
            LIMIT 1
        ";

        $lastAttacked = $this->dbQuery($sql, false);

        if ($lastAttacked) {
            $this->dbUpdate('attacks', ['placed' => 1], ['id' => $lastAttacked['id']]);
            return $lastAttacked['field'];
        }

        return false;
    }

    public function placeShip($data)
    {
        $this->dbInsert('ships', $data);

        return $this->getInsertedId();
    }

    public function getShipByGameIdAndField($gameId, $userId, $fields)
    {
        $fields = explode(',', $fields);
        $field1 = "{$fields[0]},";
        $field2 = !empty($fields[1]) ? " OR fields LIKE '%$fields[1],%'" : "";
        $field3 = !empty($fields[2]) ? " OR fields LIKE '%$fields[2],%'" : "";
        $field4 = !empty($fields[3]) ? " OR fields LIKE '%$fields[3],%'" : "";

        $sql = "
            SELECT * FROM ships
            WHERE game_id = $gameId
            AND   user_id = $userId
            AND (
                fields LIKE '%$field1%'
                $field2
                $field3
                $field4
            )
            LIMIT 1;
        ";

        return $this->dbQuery($sql, false);
    }

    public function getShipHit($gameId, $userId, $field)
    {
        $sql = "
            SELECT * FROM ships
            WHERE game_id = $gameId
            AND user_id   = $userId
            AND fields LIKE '%$field%'
            LIMIT 1;
        ";

        return $this->dbQuery($sql, false);
    }

    public function checkGameStarted($gameId)
    {
        $sql = "
            SELECT (SELECT COUNT(*) 
                    FROM ships
                    WHERE user_id = g.user1
                    AND game_id = g.id) AS user1_ships,
                    (SELECT COUNT(*) 
                    FROM ships
                    WHERE user_id = g.user2
                    AND game_id = g.id) AS user2_ships
            FROM games g
            WHERE g.id = $gameId
            HAVING user1_ships >= 9 AND user2_ships >= 9
        ";

        $result = $this->dbQuery($sql, false);

        return !empty($result) ? true : false;
    }

    public function setGameOver($gameId, $userId)
    {

    }

    public function setUserLeftGame($gameId, $userId)
    {
        $this->dbUpdateDef(['user_left' => $userId], ['id' => $gameId]);
    }

    public function checkGameWin($gameId)
    {
        $sql = "
            SELECT (SELECT COUNT(*) 
                    FROM attacks
                    WHERE user_id = g.user1
                    AND game_id = g.id
                    AND hit = true) AS user1_hits,
                    (SELECT COUNT(*) 
                    FROM attacks
                    WHERE user_id = g.user2
                    AND game_id = g.id
                    AND hit = true) AS user2_hits,
                    g.user1,
                    g.user2
            FROM games g
            WHERE g.id = $gameId
        ";
        $result = $this->dbQuery($sql, false);
        return [
            [
                'id' => $result['user1'],
                'win' => $result['user1_hits'] >= 27
            ],
            [
                'id' => $result['user2'],
                'win' => $result['user2_hits'] >= 27
            ]
        ];
    }

    public function getGamesPlayed($userId)
    {
        $sql = "
            SELECT g.id,
                   g.date,
                   IF(g.user_win = $userId, true, false) win,
                   o.name opponent
            FROM games g
            LEFT JOIN users o ON o.id = IF(g.user1 = $userId, g.user2, g.user1)
            WHERE g.user1 = $userId
            OR g.user2 = $userId
            ORDER BY g.date DESC
        ";
        return $this->dbQuery($sql);
    }

    public function stats($userId)
    {
        $sql = "
            SELECT COUNT(*) totalGames,
                   SUM(IF(user_win = $userId, 1, 0)) totalWins
            FROM games
            WHERE user1 = $userId
            OR user2 = $userId
        ";

        return $this->dbQuery($sql, false);
    }
}