<?php

namespace Employness\Repositories;

class KarmaRepository extends AbstractRepository
{
    public function getKarmaRepartitionForDayWithId($id)
    {
        $repartition = array();
        $repartition_db = $this->conn->fetchAll("SELECT day_id, karma, COUNT(*) AS count FROM {$this->table} WHERE day_id = $id GROUP BY karma");
        foreach ($repartition_db as $row) {
            $repartition[] = array('karma' => $row['karma'], 'count' => $row['count']);
        }

        return $repartition;
    }
}