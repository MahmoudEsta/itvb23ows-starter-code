<?php

namespace controllers;

use database\DatabaseService;

class RestartController
{
    private DatabaseService $database;

    public function __construct(DatabaseService $database)
    {
        $this->database = $database;
    }

    public function restartGame()
    {
        unset($_SESSION['error']);
        $_SESSION['board'] = [];
        $_SESSION['hand'] = [0 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3],
                             1 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3]];
        $_SESSION['player'] = 0;
        $_SESSION['move_number'] = 0;
        unset($_SESSION['white_queen']);
        unset($_SESSION['black_queen']);

        $lastMove = $this->database->restart();
        $_SESSION['game_id'] = $lastMove;
    }
}
