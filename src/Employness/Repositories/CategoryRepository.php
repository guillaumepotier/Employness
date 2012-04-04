<?php

namespace Employness\Repositories;

class CategoryRepository extends AbstractRepository
{
    public function getCategory($name)
    {
        return $this->findOneBy(array('LOWER(name)' => strtolower($name)));
    }
}