<?php

namespace controllers;

use database\DatabaseService;

class UndoController
{
    private DatabaseService $database;

    public function __construct(DatabaseService $database)
    {
        $this->database = $database;
    }

    public function undoMove()
    {
        unset($_SESSION['error']);
        $result = $this->database->undo($_SESSION['last_move']);
        $this->database->deleteMove($_SESSION['last_move']);
        $_SESSION['last_move'] = $result[5];
        $this->database->setState($result[6]);
        $_SESSION['move_number'] = $_SESSION['move_number'] - 1;
    }
}
