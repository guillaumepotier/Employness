<?php

namespace Employness\Repositories;

class DayRepository extends AbstractRepository
{
    private $cache = array();

    public function getDays($limit = 30)
    {
        $output = array();
        $query = "SELECT * FROM {$this->table} ORDER BY id DESC LIMIT {$limit}";

        if (isset($this->cache[md5($query)])) {
            return $this->cache[md5($query)];
        }

        $days = $this->conn->fetchAll($query);

        foreach ($days as $row) {
            $output[$row['day']] = array(
                'id'            =>  $row['id'],
                'day'           =>  $row['day'],
                'karma'         =>  $row['karma'],
                'participants'  =>  unserialize($row['participants']),
            );
        }

        return $this->cache[md5($query)] = array_reverse($output);
    }

    public function getAvgKarma($limit = 30)
    {
        $karma = $participants = 0;
        $days = $this->getDays($limit);
    
        foreach ($days as $day) {
            $participants += sizeof($day['participants']);
            $karma += $day['karma'];
        }

        return $participants != 0 ? round($karma/$participants, 2) : 0;
    }
}