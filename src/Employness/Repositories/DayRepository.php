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

    public function getDaysByCategory($limit = 30)
    {
        $output = $this->getDays($limit);

        /** The offset of the initial row is 0 (not 1) */
        $limit = $limit-1;

        /** Only with MySql >= 4.1 (GROUP_CONCAT) ! */
        $query = "SELECT    d.day, c.name, SUM( k.karma ) AS karma, GROUP_CONCAT( u.email ) AS participants
                  FROM      employness_days       d
                  LEFT JOIN employness_karma      k ON ( d.id          = k.day_id )
                  LEFT JOIN employness_users      u ON ( k.user_id     = u.id     )
                  LEFT JOIN employness_categories c ON ( u.category_id = c.id     )
                  WHERE     d.day >= (
                                 SELECT COALESCE( (
                                            SELECT   day
                                            FROM     employness_days
                                            ORDER BY day DESC
                                            LIMIT    {$limit}, 1
                                        ),
                                        min(day) )
                                 FROM employness_days )
                 GROUP BY  d.day, c.name
                 ORDER BY  d.id DESC";

        if (isset($this->cache[md5($query)])) {
            return $this->cache[md5($query)];
        }

        $days = $this->conn->fetchAll($query);

        foreach ($days as $row) {
            $output[$row['day']]['categories'][$row['name']] = array(
                'karma'         =>  $row['karma'],
                'participants'  =>  explode( ',', $row['participants'] ),
            );
        }

        return $this->cache[md5($query)] = $output;
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