<?php

namespace controllers;

use database\DatabaseService;

class PassController
{
    private DatabaseService $database;

    public function __construct(DatabaseService $database)
    {
        $this->database = $database;
    }

    public function pass()
    {
        $lastMove = $this->database->pass($_SESSION['game_id'], $_SESSION['last_move']);
        $_SESSION['last_move'] = $lastMove;
        $_SESSION['player'] = 1 - $_SESSION['player'];
    }
}
