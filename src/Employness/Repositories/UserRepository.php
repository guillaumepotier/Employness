<?php

namespace Employness\Repositories;

class UserRepository extends AbstractRepository
{
    public function getUser($email, $password = false)
    {
        return false === $password ? $this->findOneBy(array('LOWER(email)' => strtolower($email))) : $this->findOneBy(array('LOWER(email)' => strtolower($email), 'password' => $password));
    }

    public function getUserWithBestKarma()
    {
        return $this->conn->fetchAssoc("SELECT * FROM {$this->table} ORDER BY karma/evaluated_days DESC LIMIT 1");
    }

    public function getAvgUsersKarma()
    {
        $avg = $this->conn->fetchAssoc("SELECT AVG(karma/evaluated_days) AS avg FROM {$this->table}");
        return round($avg['avg'], 2);
    }
}