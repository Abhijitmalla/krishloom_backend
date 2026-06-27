<?php

class Database
{
    private $host = "localhost";
    private $user = "root";
    private $password = "";
    private $database = "krishloom";

    public function connect()
    {
        return new mysqli(
            $this->host,
            $this->user,
            $this->password,
            $this->database
        );
    }
}