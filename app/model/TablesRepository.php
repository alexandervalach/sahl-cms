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

    public function incrementTableValueByOne($teamId, $type, $columnName) {
        $tableSelection = $this->findAll()->where('team_id', $teamId)->where('type', $type);
        foreach ($tableSelection as $row) {
            $value = $row[$columnName] + 1;
            $data = array( $columnName => $value );
            return $row->update($data);
        }
    }

}
