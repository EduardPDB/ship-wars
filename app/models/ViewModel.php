<?php

use BaseModel\BaseModel;

class ViewModel extends BaseModel {
    public function initialize()
    {
        $this->loadModel('user');
    }

    public function getViewsByUser($userId = null): ?array
    {
        if (!$userId) return null;

        return $this->dbSelectDef(['user_id' => $userId], false);
    }

    public function getUnivByLocation($location = ''): ?array
    {
        return $this->dbSelectDef(['location' => $location], false);
    }

    public function getUnivById($id = ''): ?array
    {
        return $this->dbSelectDef(['id' => $id], false);
    }

    public function getUnivBySpecId($specId = ''): ?array
    {
        return $this->dbSelectDef(['spec_id' => $specId], false);
    }

    public function deleteUnivWhereId($id = ''): bool
    {
        return $this->dbDeleteDef($id);
    }

    public function addUniv($data): bool
    {
        if (empty($data)) return false;

        return $this->dbInsertDef($data);
    }

    public function updateUniv($id = '', $data = ''): bool
    {
        return $this->dbUpdateDef($data, ['id' => $id]);
    }
}