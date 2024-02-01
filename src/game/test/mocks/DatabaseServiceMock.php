<?php

namespace test\mocks;

use database\DatabaseService;

class DatabaseServiceMock extends DatabaseService
{

    public function __construct()
    {

    }

    public function play($gameId, $piece, $to, $lastMove)
    {
        $_SESSION['board'][$to] = [[1, $piece]];
    }

    public function move($gameId, $from, $to, $lastMove){
        unset($_SESSION['board'][$from]);
        $_SESSION['board'][$to] = [[0 , 'Q']];
    }
    public function pass($gameId, $lastMove)
    {
        return;
    }

}