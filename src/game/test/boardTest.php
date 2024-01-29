<?php

namespace test;

use objects\Board;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

class boardTest extends TestCase
{
    function testLostGameHappyFlow()
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
        $board = new Board($boardArray);
        $result = $board->lostGame($boardArray, "0,0");

        assertTrue($result);
    }

    function testLostGameBlackPlayerDidNotLose()
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
        $board = new Board($boardArray);
        $result = $board->lostGame($boardArray, "0,1");

        assertFalse($result);
    }
}