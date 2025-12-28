<?php

namespace App\Rdb;

use mysqli;

class SqlHelper{
    public function openDbConnection(): mysqli  {
        $host = $_ENV["DB_HOST"];
        $port = $_ENV["DB_PORT"];
        $username = $_ENV["DB_USERNAME"];
        $password = $_ENV["DB_PASSWORD"];
        $database = $_ENV["DB_NAME"];

        return new mysqli($host, $username, $password, $database, $port);
    }

    public function pingDb() : void {
        $connection = $this->openDbConnection();
        $connection->close();
    }
}
