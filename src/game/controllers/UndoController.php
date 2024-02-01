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

        $result = $this->database->getLastMove($_SESSION['last_move']);
        $this->updateQueenPlace($result);
        $this->database->deleteMove($_SESSION['last_move']);
        $_SESSION['last_move'] = $result[5];
        $this->database->setState($result[6]);
        $_SESSION['move_number'] = $_SESSION['move_number'] - 1;
        $_SESSION['endGame'] = false;
    }

    private function updateQueenPlace($result)
    {
        if ($result[3] == "Q"){
            if ($result[4] == $_SESSION['black_queen']){
                unset($_SESSION['black_queen']);
            } elseif ($result[4] == $_SESSION['white_queen']) {
                unset($_SESSION['white_queen']);
            }
        } elseif ($result[2] == 'move'){
            if ($result[4] == $_SESSION['black_queen']){
                $_SESSION['black_queen'] = $result[3];
            } elseif ($result[4] == $_SESSION['white_queen']){
                $_SESSION['white_queen'] = $result[3];
            }
        }
    }
}
