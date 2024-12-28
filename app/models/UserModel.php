<?php

use BaseModel\BaseModel;

class UserModel extends BaseModel {
    /**
     * Get an user by email or by phone.
     * 
     * @param string $email The email of the user.
     * @param string $phone The phone of the user.
     * @return array|null The user we find.
     */
    public function getUserByEmailOrPhone($email = '', $phone = ''): ?array
    {
        if (empty($email) && empty($phone)) return null;
        
        $sql = "SELECT u.id,
                       u.name,
                       u.email,
                       u.password,
                       u.type
                FROM users u
                WHERE u.email = '$email'";

        return $this->dbQuery($sql, false);
    }

    public function getUserById($id = '')
	{
        $sql = "
            SELECT id,
                   email,
                   name username,
                   game_id,
                   my_turn,
                   in_game
            FROM users
            WHERE id = '$id'
        ";
	    return $this->dbQuery($sql, false);
	}

    /**
     * Add new user in the database.
     * 
     * @param array $user User details.
     * @return int|null Id of the created user.
     */
    public function addUser($user): ?int
    {
        $password      = $user['password'] ?? '';
        $encryptedPass = password_hash($password, PASSWORD_DEFAULT);

        $user['password']   = $encryptedPass;

        $this->dbInsert('users', $user);
        return $this->getInsertedId();
    }

    public function updateUser($userId, $data)
    {
        return $this->dbUpdate('users', $data, ['id' => $userId]);
    }

    public function getUserByName($name = '')
	{
        $sql = "
            SELECT id,
                   email,
                   name username
            FROM users
            WHERE name = '$name'
        ";
	    return $this->dbQuery($sql, false);
	}

    public function getUserDetails($id)
    {
        $sql = "
            SELECT id,
                email,
                name
            FROM users
            WHERE id = '$id'
        ";
	    return $this->dbQuery($sql, false);
    }

    public function update($id, $data)
    {
        return $this->dbUpdateDef($data, ['id' => $id]);
    }
}