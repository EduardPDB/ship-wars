<?php

use BaseModel\BaseModel;

/**
 * @property UserModel $User
 */
class AuthModel extends BaseModel {

    public $features = [
        'dbBlameDate' => false,
        'dbBlameUser' => false,
    ];

    /**
     * Create a token for a user.
     * 
     * @param int $userId Id of the user we want to create the token.
     * @return int|null Id of the created token.
     */
    public function addToken($userId): ?int
    {
        $token          = $this->getRandomStringRandomInt();
        $currentDate    = buildTDate();
        $expidationDate = buildTDate('+30 days');
        $ipAddress      = getClientIp();

        $data = [
            'name'       => $token,
            'user_id'    => $userId,
            'created_at' => $currentDate,
            'expiration' => $expidationDate,
            'ip'         => $ipAddress,
        ];

        $this->dbInsert('tokens', $data);

        return $this->getInsertedId();
    }

    /**
     * Find a token by token name by token id.
     * 
     * @param int $id Token id.
     * @return string|null Token name.
     */
    public function getTokenNameById(int $id): ?string
    {
        $token = $this->dbSelect('tokens', ['id' => $id], false);
        return $token['name'] ?? null;
    }

    public function getTokenNameByUserId(int $userId): ?string
    {
        $token = $this->dbSelect('tokens', ['user_id' => $userId], false);
        return $token['name'] ?? null;
    }

    /**
     * Validate the user token.
     * 
     * @param string $token The client token.
     * @param array $permissions The controller permissions.
     * @return array
     */
    public function checkToken($token, $allowedTypes): array
    {
        $sql = "SELECT t.expiration,
                       u.type user_type,
                       u.id user_id,
                       t.ip
                FROM tokens t
                INNER JOIN users u ON u.id = t.user_id
                WHERE t.name = '$token'";

        $token       = $this->dbQuery($sql, false);
        $currentDate = buildTDate();

        if (empty($token)) return $this->status('Token couldn\'t be found.', [], 'error');

        if (($token['expiration'] - $currentDate) <= 0) {
            if ($token['ip'] !== getClientIp()) $this->status('Token has expired.', [], 'error');

            $updatedToken = $this->refreshToken($token['user_id']);
            if (!$updatedToken) $this->status('Token has expired.', [], 'error');

            $sql = "SELECT t.expiration,
                            u.type user_type,
                            u.id user_id
                    FROM tokens t
                    INNER JOIN users u ON u.id = t.user_id
                    WHERE t.name = '$updatedToken'";
            $token = $this->dbQuery($sql, false);
        }
        
        if (
            $token['user_type'] !== 'admin' && $allowedTypes &&
            !in_array($token['user_type'], $allowedTypes)
        ) {
            return $this->status('User is not allowed.', [], 'error');
        }

        return $this->status('Good', $token['user_id']);
    }

    public function refreshToken($userId): string
    {
        $token          = $this->getRandomStringRandomInt();
        $expidationDate = buildTDate('+30 days');

        $result = $this->dbUpdate('tokens', ['name' => $token, 'expiration' => $expidationDate], ['user_id' => $userId]);
        if (!$result) return false;

        return $token;
    }

    /**
     * Uses random_int as core logic and generates a random string
     * random_int is a pseudorandom number generator
     *
     * @param int $length
     * @return string
     */
    private function getRandomStringRandomInt($length = 24): string
    {
        $stringSpace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $pieces = [];
        $max = mb_strlen($stringSpace, '8bit') - 1;
        for ($i = 0; $i < $length; ++ $i) {
            $pieces[] = $stringSpace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }

    public function setTokenExpired($userId)
    {
        return $this->dbUpdate('tokens', ['expiration' => 0], ['user_id', $userId]);
    }
}