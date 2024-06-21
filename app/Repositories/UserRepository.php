<?php

namespace App\Repositories;

use App\Database;

class UserRepository
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function loadUser(string $username): ?array
    {
        $query = "SELECT * FROM users WHERE username = :username";

        $stmt = $this->database->prepare($query);

        $stmt->bindValue(':username', $username, SQLITE3_TEXT);

        $result = $stmt->execute();

        $user = $result->fetchArray(SQLITE3_ASSOC);

        return $user ?: null;
    }
}


