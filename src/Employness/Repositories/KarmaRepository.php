<?php

namespace Employness\Repositories;

use Employness\Repositories\UserRepository;

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

    public function getKarmas($day_id, UserRepository $userRepo)
    {
        $karmas = array();
        $user_table = $userRepo->getTable();

        $karma_query = $this->conn->fetchAll("
            SELECT * FROM {$this->table} 
            LEFT JOIN {$user_table} 
                ON {$this->table}.user_id = {$user_table}.".$userRepo->getIdentifier()."
            WHERE day_id >= $day_id");

        foreach ($karma_query as $row) {
            $karmas[$row['day_id']][$row['email']] = $row;
        }

        return $karmas;
    }
}