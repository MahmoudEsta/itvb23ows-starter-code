<?php

namespace test\objects;

use objects\Board;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

class BoardTest extends TestCase
{
    public function testLostGameHappyFlow()
    {
        $board = $this->getBoard();
        $result = $board->lostGame($board->getBoard(), "0,0");

        assertTrue($result);
    }

    public function testLostGameBlackPlayerDidNotLose()
    {
        $board = $this->getBoard();
        $result = $board->lostGame($this->getBoard()->getBoard(), "0,1");

        assertFalse($result);
    }

    private function getBoard()
    {
        $boardArray = [
            '0,0' => [
                0 => [0, 'Q']
            ],
            '0,1' => [
                0 => [1, 'Q']
            ],
            '1,0' => [
                0 => [0, 'A']
            ],
            '-1,0' => [
                0 => [1, 'A']
            ],
            '-1,1' => [
                0 => [0, 'B']
            ],
            '0,-1' => [
                0 => [1, 'B']
            ],
            '1,-1' => [
                0 => [1, 'S']
            ],
        ];
        return new Board($boardArray);
    }
}