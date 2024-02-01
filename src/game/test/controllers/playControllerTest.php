<?php

namespace test\controllers;

use controllers\PlayController;
use objects\Board;
use PHPUnit\Framework\TestCase;
use test\mocks\DatabaseServiceMock;
use function PHPUnit\Framework\assertArrayNotHasKey;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertStringContainsString;

class PlayControllerTest extends TestCase
{
    public function testNoValidPlays()
    {
        $database = new DatabaseServiceMock();
        $_SESSION = [
            'player' => 0,
            'hand' => [
                0 => ["Q" => 1, "B" => 1, "S" => 2, "A" => 3, "G" => 3]
            ],
        ];
        $boardArray = [
            '0,0' => [
                0 => [0, 'B']
            ],
            '0,1' => [
                0 => [1, 'Q']
            ],
        ];
        $board = new Board($boardArray);
        $playController = new PlayController("B", "0,0", $board, $database);

        $playController->noValidPlays();

        assertNotNull($_SESSION["error"]);
    }

    public function testNoValidPlaysCanPlayAnything()
    {
        $database = new DatabaseServiceMock();
        $_SESSION = [
            'player' => 0,
            'hand' => [
                0 => ["Q" => 0, "B" => 0, "S" => 0, "A" => 0, "G" => 0]
            ],
        ];
        $boardArray = [
            '0,0' => [
                0 => [0, 'Q']
            ],
            '1,0' => [
                0 => [1, 'Q']
            ],
            '-1,0' => [
                0 => [0, 'B']
            ],
            '2,0' => [
                0 => [1, 'A']
            ],
            '-2,0' => [
                0 => [0, 'B']
            ],
            '3,0' => [
                0 => [1, 'A']
            ],
            '-3,0' => [
                0 => [0, 'G']
            ],
            '4,0' => [
                0 => [1, 'A']
            ],
            '-4,0' => [
                0 => [0, 'G']
            ],
            '5,0' => [
                0 => [1, 'S']
            ],
            '-5,0' => [
                0 => [0, 'G']
            ],
            '6,0' => [
                0 => [1, 'S']
            ],
            '-6,0' => [
                0 => [0, 'S']
            ],
            '7,0' => [
                0 => [1, 'G']
            ],
            '-7,0' => [
                0 => [0, 'S']
            ],
            '8,0' => [
                0 => [1, 'G']
            ],
            '-8,0' => [
                0 => [0, 'A']
            ],
            '9,0' => [
                0 => [1, 'G']
            ],
            '-9,0' => [
                0 => [0, 'A']
            ],
            '10,0' => [
                0 => [1, 'B']
            ],
            '-10,0' => [
                0 => [0, 'A']
            ],
            '11,0' => [
                0 => [1, 'B']
            ]
        ];
        $board = new Board($boardArray);
        $playController = new PlayController("B", "0,0", $board, $database);

        $playController->noValidPlays();

        assertArrayNotHasKey("error", $_SESSION);
    }

    public function testNoPlayWhenGameEnd()
    {
        $database = new DatabaseServiceMock();
        $_SESSION = [
            'player' => 0,
            'endGame' => true,
            'hand' => [
                0 => ["Q" => 0, "B" => 0, "S" => 0, "A" => 0, "G" => 0]
            ],
        ];
        $boardArray = [
            '0,0' => [
                0 => [0, 'B']
            ],
            '0,1' => [
                0 => [1, 'Q']
            ]
        ];
        $board = new Board($boardArray);
        $playController = new PlayController("B", "0,0", $board, $database);

        $playController->executePlay();

        assertStringContainsString('Restart the game to play again', $_SESSION['error']);
    }
}
