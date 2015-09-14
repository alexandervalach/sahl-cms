<?php

namespace App\Model;

class TablesRepository extends Repository {

    public function getTableStats($type) {
        $rows = $this->findByValue('onSidebar', 1)->where('type', $type)->order('points DESC');
        if (!$rows) {
            return null;
        }
        $result = array();
        foreach ($rows as $row) {
            $result[] = $row;
        }
        return $result;
    }

    public function incrementTableValue($teamId, $type, $columnName, $value) {
        $tableRow = $this->findAll()->where('team_id', $teamId)->where('type', $type)->fetch();
        $newValue = $tableRow[$columnName] + $value;
        $data = array($columnName => $newValue);
        return $tableRow->update($data);
    }

}
