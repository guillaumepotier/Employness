<?php

/*
 * This file is part of gordonslondons' SilexBlog.
 * https://github.com/gordonslondon/SilexBlog
 */
 
namespace Employness\Repositories;

use Doctrine\DBAL\Connection;

abstract class AbstractRepository
{
    protected $conn;

    protected $table;

    private $identifiers;

    public function __construct(Connection $conn, $table, array $identifiers)
    {
        $this->conn = $conn;
        $this->table = $table;
        $this->identifiers = $identifiers;
    }

    public function getConnection()
    {
        return $this->conn;
    }

    public function update($id, array $data, $identifier = null)
    {
        if (null === $identifier) {
            $identifier = $this->getIdentifier();
        }

        return $this->conn->update($this->table, $data, array($identifier => $id));
    }

    public function insert(array $data) 
    {
        return $this->conn->insert($this->table, $data);
    }

    public function delete($id)
    {
        return $this->conn->delete($this->table, array($this->identifiers[0] => $id));
    }

    public function findAll()
    {
        return $this->conn->fetchAll("SELECT * FROM {$this->table}");
    }

    public function findBy(array $params = array())
    {
        $query = "SELECT * FROM {$this->table} WHERE " . self::formatClause(array_keys($params), 'AND');
        return $this->conn->fetchAll($query, array_values($params));
    }

    public function findOneBy(array $params = array())
    {
        $query = "SELECT * FROM {$this->table} WHERE " . self::formatClause(array_keys($params), 'AND');
        return $this->conn->fetchAssoc($query, array_values($params));
    }

    public function find($id)
    {
        $params = array_fill(0, count($this->identifiers), $id);
        $query = "SELECT * FROM {$this->table} WHERE " . self::formatClause($this->identifiers, 'OR');
        return $this->conn->fetchAssoc($query, $params);
    }

    public function getIdentifier()
    {
        return $this->identifiers[0];
    }

    public function getTable()
    {
        return $this->table;
    }

    private static function formatClause(array $keys, $operator = 'AND')
    {
        $clause = '';
        foreach ($keys as $i => $key) {
            $clause .= sprintf('%s %s = ? ', $i == 0 ? '' : $operator, $key);
        }
        return $clause;
    }
}