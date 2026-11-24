<?php
declare(strict_types=1);

namespace App\Core;

class Model
{
    protected \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
}
