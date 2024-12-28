<?php

class DataBase {
    private $db;

    /**
     * Override de default table that has been set in the core.
     * It is used because some words don't use only 's' to pluralize.
     */
    public string $defaultTable;

    /**
     * Automaticaly adds created_at, updated_at (excepts queryes).
     * 
     * Default value is true.
     */
    public bool $dbBlameDate = false;

    /**
     * Automaticaly adds created_by, updated_by (excepts queryes).
     * 
     * Default value is true.
     */
    public bool $dbBlameUser = false;

    /**
     * The id of the loged user.
     */
    public ?int $dbUserId = null;

    public function __construct()
    {
        $env = env();

        // Create connection
        $conn = new mysqli(
            $env['HOSTNAME'],
            $env['USER'],
            $env['PASS'],
            $env['DB'],
        );

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $this->db = $conn;
    }
    
    /**
     * Insert the data in a specific table.
     * 
     * @param string $table The table that you want to insert into.
     * @param array $data The data that you want to insert in the table.
     * @return bool
     */
    public function dbInsert(string $table, array $data): bool
    {
        if ($this->dbBlameDate)
        {
            $date = date('Y-m-d H:i:s');
            $data['created_at'] = $date;
        }

        if ($this->dbBlameUser)
        {
            $data['created_by'] = $this->dbUserId;
        }

        $keys   = array_keys($data);
        $values = array_values($data);

        
        $keys   = implode(', ' , $keys);
        $values = implode("', '" , $values);

        $sql = "INSERT INTO $table ($keys) values ('$values')";

        if (!$this->db->query($sql)) {
            return showDbError('error', ['message' => $this->db->error]);
        } else {
            return true;
        };
    }

    /**
     * Select something from a table.
     * 
     * @param string $table The table that you want to insert into.
     * @param array $identifier An array that contains the column and the item id, name, etc...
     * @param bool $returnAll True to return all the result find, or false to return just one.
     * 
     * @return array
     */
    public function dbSelect(
        string $table,
        array $identifier = null,
        bool $returnAll   = true
    ): array {
        $filter = '';

        if (!empty($identifier))
        foreach ($identifier as $key => $col) {
            $filter .= "AND $key = '$col' ";
        }

        $limit  = !$returnAll ? "LIMIT 1" : "";
        
        $sql = "SELECT * 
                FROM $table
                WHERE 1
                $filter
                $limit";
    
        $query = $this->db->query($sql);

        if (!$query) showDbError('error', ['message' => $this->db->error]);

        if ($returnAll) {
            return (array) $query->fetch_all(MYSQLI_ASSOC);
        } else {
            return (array) $query->fetch_assoc();
        }
    }

    /**
     * Make a query to the database.
     * 
     * @param bool $returnAll If true return all, else return just the first.
     * @param bool $return If true returns the data.
     * @return array|null
     */
    public function dbQuery($sql = '', $returnAll = true, $return = true): ?array
    {
        $query = $this->db->query($sql);

        if (!$query) showDbError('error', ['message' => $this->db->error]);

        if ($return && $returnAll) {
            return (array) $query->fetch_all(MYSQLI_ASSOC);
        } else if ($return) {
            return (array) $query->fetch_assoc();
        }
    }

    /**
     * Update the data from a specific table.
     * 
     * @param string $table The table where we want to delete.
     * @param array  $data The data we want to update with.
     * @param array  $identifier What row should be updated.
     * 
     * @return bool
     */
    public function dbUpdate($table = '', $data, $identifier = [])
    {
        $updates = '';

        if ($this->dbBlameDate)
        {
            $date = date('Y-m-d H:i:s');
            $data['updated_at'] = $date;
        }

        if ($this->dbBlameUser)
        {
            $data['updated_by'] = $this->dbUserId;
        }

        foreach($data as $column => $value) {
            $updates .= "$column = '$value', ";
        }

        $updates = substr($updates, 0, -2);

        $key = key($identifier);
        $col = $identifier[$key];

        $sql = "UPDATE $table SET $updates WHERE $key='$col'";

        $query = $this->db->query($sql);

        if (!$query) return showDbError('error', ['message' => $this->db->error]);

        return true;
    }

    /**
     * Delete the data from a specific table.
     * 
     * @param string       $table The table where we want to delete.
     * @param string|array $item Where we want to delete, default is the id.
     * @param bool         $deleteWhereId If true delete where id =.
     * 
     * @return bool
     */
    public function dbDelete($table = '', $item, $deleteWhereId = true)
    {
        if ($deleteWhereId) {
            $sql = "DELETE FROM $table WHERE id = '$item'";
            
            $this->db->query($sql);
        }

        $key = key($item);
        $col = $item[$key];

        $sql = "DELETE FROM $table WHERE $key = '$col'";

        $query = $this->db->query($sql);
        
        if (!$query)return showDbError('error', ['message' => $this->db->error]);

        return true;
    }

    /**
     * Get the id of the inserted object.
     * 
     * @return int|null
     */
    public function getInsertedId()
    {
        return $this->db->insert_id;
    }

    /**
     * Select something from a table.
     * 
     * @param string $table The table that you want to insert into.
     * @param array $identifier An array that contains the column and the item id, name, etc...
     * @param bool $returnAll True to return all the result find, or false to return just one.
     * 
     * @return array
     */
    public function dbSelectDef(
        array $identifier = null,
        bool $returnAll   = true
    ): array {
        $table  = $this->defaultTable;
        $filter = '';

        if (!empty($identifier))
        foreach ($identifier as $key => $col) {
            $filter .= "AND $key = '$col' ";
        }

        $limit  = !$returnAll ? "LIMIT 1" : "";
        
        $sql = "SELECT * 
                FROM $table
                WHERE 1
                $filter
                $limit";
    
        $query = $this->db->query($sql);

        if (!$query) return showDbError('error', ['message' => $this->db->error]);

        if ($returnAll) {
            return (array) $query->fetch_all(MYSQLI_ASSOC);
        } else {
            return (array) $query->fetch_assoc();
        }
    }

    /**
     * Update the data from a specific table.
     * 
     * @param string $table The table where we want to delete.
     * @param array  $data The data we want to update with.
     * @param array  $identifier What row should be updated.
     * 
     * @return bool
     */
    public function dbUpdateDef($data, $identifier = []): bool
    {
        $table   = $this->defaultTable;
        $updates = '';

        unset($data['id']);

        if ($this->dbBlameDate)
        {
            $date = date('Y-m-d H:i:s');
            $data['updated_at'] = $date;
        }

        if ($this->dbBlameUser)
        {
            $data['updated_by'] = $this->dbUserId;
        }

        foreach($data as $column => $value) {
            $updates .= $column . '=' . '\'' . $value . '\', ';
        }

        $updates = substr($updates, 0, -2);

        $key = key($identifier);
        $col = $identifier[$key];

        $sql = "UPDATE $table SET $updates WHERE $key='$col'";

        $query = $this->db->query($sql);

        if (!$query) return showDbError('error', ['message' => $this->db->error]);

        return true;
    }

    /**
     * Delete the data from the default table.
     * 
     * @param string|array $item Where we want to delete, default is the id.
     * @param bool         $deleteWhereId If true delete where id =.
     * 
     * @return bool
     */
    public function dbDeleteDef($item, $deleteWhereId = true): bool
    {
        $table = $this->defaultTable;

        if ($deleteWhereId) {
            $sql = "DELETE FROM $table WHERE id = '$item'";
            
            $this->db->query($sql);
        }

        $key = key($item);
        $col = $item[$key];

        $sql = "DELETE FROM $table WHERE $key = '$col'";

        $query = $this->db->query($sql);
        
        if (!$query) return showDbError('error', ['message' => $this->db->error]);

        return true;
    }

    /**
     * Insert the data in the default table.
     * 
     * @param array $data The data that you want to insert in the table.
     * @return bool
     */
    public function dbInsertDef(array $data): bool
    {
        $table  = $this->defaultTable;

        if ($this->dbBlameDate)
        {
            $date = date('Y-m-d H:i:s');
            $data['created_at'] = $date;
        }

        if ($this->dbBlameUser)
        {
            $data['created_by'] = $this->dbUserId;
        }

        $keys   = array_keys($data);
        $values = array_values($data);

        $keys   = implode(', ' , $keys);
        $values = implode("', '" , $values);
        
        $sql = "INSERT INTO $table ($keys) values ('$values')";

        if (!$this->db->query($sql)) {
            return showDbError('error', ['message' => $this->db->error]);
        } else {
            return true;
        };
    }
}