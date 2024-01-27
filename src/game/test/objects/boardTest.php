<?php

namespace test\objects;

use objects\Board;
use PHPUnit\Framework\TestCase;

class boardTest extends TestCase
{
    public function testNoValidMovesOrPlaysHappyFlow()
    {
        $boardArray = [];
        $board = new Board($boardArray);

        $resultMoves = $board->noValidMoves();
        $resultPlays = $board->noValidPlays();

        $this->assertTrue($resultMoves);
        $this->assertTrue($resultPlays);
    }

    public function testNoValidMovesOrPlaysError()
    {
        $boardArray = [];
        $board = new Board($boardArray);

        $resultMoves = $board->noValidMoves();
        $resultPlays = $board->noValidPlays();

        $this->assertFalse($resultMoves);
        $this->assertFalse($resultPlays);
    }

}