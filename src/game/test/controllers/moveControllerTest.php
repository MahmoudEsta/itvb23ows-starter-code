<?php

namespace test\controllers;

use controllers\MoveController;
use objects\Board;
use PHPUnit\Framework\TestCase;
use test\mocks\DatabaseServiceMock;
use function PHPUnit\Framework\assertArrayNotHasKey;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertStringContainsString;
use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertEquals;

class MoveControllerTest extends TestCase
{
    public function testMoveQueenBug()
    {
        $_SESSION = [
            'last_move' => 1,
            'game_id' => 12345,
            'move_number' => 2,
            'endGame' => false,
            'player' => 0,
            'hand' => [
                0 => ["Q" => 0, "B" => 2, "S" => 2, "A" => 3, "G" => 3]
            ],
        ];
        $boardArray = [
            '0,0' => [
                0 => [0, 'Q']
            ],
            '1,0' => [
                0 => [1, 'Q']
            ],
        ];
        $board = new Board($boardArray);
        $database = new DatabaseServiceMock();
        $from = '0,0';
        $to = '0,1';
        $moveController = new MoveController($from, $to, $board, $database);
        $moveController->executeMove();

        assertArrayHasKey('0,1', $_SESSION['board']);
        assertArrayNotHasKey('0,0', $_SESSION['board']);
        assertEquals(3, $_SESSION['move_number']);
        assertEquals("0,1", $_SESSION['white_queen']);
    }
    public function testValidateGrassshopperHappyFlow()
    {
        [$database, $board, $boardArray] = $this->setUpGrassShopper();
        $from = '-2,-1';
        $to = '-2,1';
        $moveController = new MoveController($from, $to, $board, $database);

        // Act (perform the action to be tested)
        $result = $moveController->validateGrasshopperMove($boardArray);

        // Assert (check the result)
        $this->assertTrue($result);
    }

    public function testValidateGrassshopperMoveFromIsSameASTo()
    {
        [$database, $board, $boardArray] = $this->setUpGrassShopper();
        $from = "0,2";
        $to = "0,2";
        $moveController = new MoveController($from, $to, $board, $database);

        $result = $moveController->validateGrasshopperMove($boardArray);

        $this->assertFalse($result);
    }

    public function testValidateGrassshopperJumpOverWhiteSpace()
    {
        [$database, $board, $boardArray] = $this->setUpGrassShopper();
        $from = '-2,-1';
        $to = '-2,2';
        $moveController = new MoveController($from, $to, $board, $database);

        // Act (perform the action to be tested)
        $result = $moveController->validateGrasshopperMove($boardArray);

        // Assert (check the result)
        $this->assertFalse($result);
    }

    public function testValidateGrassshopperJumpOver()
    {
        [$database, $board, $boardArray] = $this->setUpGrassShopper();
        $from = '-2,-1';
        $to = '-2,2';
        $moveController = new MoveController($from, $to, $board, $database);

        // Act (perform the action to be tested)
        $result = $moveController->validateGrasshopperMove($boardArray);

        // Assert (check the result)
        $this->assertFalse($result);
    }

    public function testValidateGrassshopperUnvalidPosition()
    {
        [$database, $board, $boardArray] = $this->setUpGrassShopper();
        $from = '-2,-1';
        $to = '-3,0';
        $moveController = new MoveController($from, $to, $board, $database);

        // Act (perform the action to be tested)
        $result = $moveController->validateGrasshopperMove($boardArray);

        // Assert (check the result)
        $this->assertFalse($result);
    }

    public function testValidateGrassshopperRightUp()
    {
        [$database, $board, $boardArray] = $this->setUpSpider();
        $boardArray = [
            '0,0' => [
                0 => [0, 'Q']
            ],
            '0,1' => [
                0 => [1, 'Q']
            ],
            '-1,0' => [
                0 => [0, 'G']
            ],
            '-2,0' => [
                0 => [0, 'B']
            ],
            '-2,1' => [
                0 => [0, 'G']
            ],
        ];
        $from = '-2,1';
        $to = '0,-1';
        $moveController = new MoveController($from, $to, $board, $database);

        // Act (perform the action to be tested)
        $result = $moveController->validateGrasshopperMove($boardArray);

        // Assert (check the result)
        $this->assertTrue($result);
    }

    public function testValidateSoldierAntHappyFlow()
    {
        [$database, $board, $boardArray] = $this->setUpSoldierAnt();
        $from = '0,-1';
        $to = '2,1';
        $moveController = new MoveController($from, $to, $board, $database);

        // Act (perform the action to be tested)
        $result = $moveController->validateSoldierAntMove($boardArray);

        // Assert (check the result)
        $this->assertTrue($result);
    }

    public function testValidateSoldierAntPositionNotConnectedToHive()
    {
        [$database, $board, $boardArray] = $this->setUpSoldierAnt();
        $from = '0,-1';
        $to = '-2,-1';
        $moveController = new MoveController($from, $to, $board, $database);

        // Act (perform the action to be tested)
        $result = $moveController->validateSoldierAntMove($boardArray);

        // Assert (check the result)
        $this->assertFalse($result);
    }

    public function testValidateSoldierAntMiddenSwarm()
    {
        [$database, $board, $boardArray] = $this->setUpSoldierAnt();
        $from = '0,-1';
        $to = '1,0';
        $moveController = new MoveController($from, $to, $board, $database);

        // Act (perform the action to be tested)
        $result = $moveController->validateSoldierAntMove($boardArray);
        if ($result){
            $result = $moveController->canSlide($boardArray, $to);
        }
        // Assert (check the result)
        $this->assertFalse($result);
    }

    public function testValidateSoldierAntMoveToSamePlace()
    {
        [$database, $board, $boardArray] = $this->setUpSoldierAnt();
        $from = '0,-1';
        $to = '0,-1';
        $moveController = new MoveController($from, $to, $board, $database);

        // Act (perform the action to be tested)
        $result = $moveController->validateSoldierAntMove($boardArray);

        // Assert (check the result)
        $this->assertFalse($result);
    }

    public function testValidateSpiderHappyFlow()
    {
        [$database, $board, $boardArray] = $this->setUpSpider();
        $from = '2,3';
        $to = '3,0';
        $moveController = new MoveController($from, $to, $board, $database);

        // Act (perform the action to be tested)
        $result = $moveController->validateSpiderMove($boardArray);

        // Assert (check the result)
        $this->assertTrue($result);
    }

    public function testValidateSpiderSecondHappyFlow()
    {
        [$database, $board, $boardArray] = $this->setUpSpider();
        $from = '2,3';
        $to = '-1,4';
        $moveController = new MoveController($from, $to, $board, $database);

        // Act (perform the action to be tested)
        $result = $moveController->validateSpiderMove($boardArray);

        // Assert (check the result)
        $this->assertTrue($result);
    }

    public function testValidateSpiderToTheSamePlace()
    {
        [$database, $board, $boardArray] = $this->setUpSpider();
        $from = '2,3';
        $to = '2,3';
        $moveController = new MoveController($from, $to, $board, $database);

        // Act (perform the action to be tested)
        $result = $moveController->validateSpiderMove($boardArray);

        // Assert (check the result)
        $this->assertFalse($result);
    }

    public function testValidateSpiderNotConnectedToHive()
    {
        [$database, $board, $boardArray] = $this->setUpSpider();
        $from = '2,3';
        $to = '4,2';
        $moveController = new MoveController($from, $to, $board, $database);

        // Act (perform the action to be tested)
        $result = $moveController->validateSpiderMove($boardArray);

        // Assert (check the result)
        $this->assertFalse($result);
    }

    public function testValidateSpiderLessThanThreeSteps()
    {
        [$database, $board, $boardArray] = $this->setUpSpider();
        $from = '2,3';
        $to = '2,1';
        $moveController = new MoveController($from, $to, $board, $database);

        // Act (perform the action to be tested)
        $result = $moveController->validateSpiderMove($boardArray);

        // Assert (check the result)
        $this->assertFalse($result);
    }

    public function testnoValidMoves()
    {
        [$database, $board, $boardArray] = $this->setUpSpider();
        $from = '0,0';
        $to = '0,0';
        $moveController = new MoveController($from, $to, $board, $database);

        $moveController->noValidMoves();

        assertNotNull($_SESSION["error"]);

    }

    public function testnoValidMovesQueenIsNotPlayed()
    {
        [$database, $board, $boardArray] = $this->setUpSpider();
        $from = '0,0';
        $to = '0,0';
        $boardArray = [
            '0,0' => [
                0 => [0, 'B']
            ],
            '0,1' => [
                0 => [1, 'Q']
            ]
        ];
        $board = new Board($boardArray);
        $_SESSION = [
            'player' => 0,
            'hand' => [
                0 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3]
            ],
        ];
        $moveController = new MoveController($from, $to, $board, $database);

        $moveController->noValidMoves();

        assertNotNull($_SESSION["error"]);

    }

    public function testnoValidMovesEverythingIsPlayed()
    {
        [$database, $board, $boardArray] = $this->setUpSpider();
        $from = '0,0';
        $to = '0,0';
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
        $moveController = new MoveController($from, $to, $board, $database);

        $moveController->noValidMoves();

        assertNotNull($_SESSION["error"]);

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
        $from = '0,0';
        $to = '0,0';
        $boardArray = [
            '0,0' => [
                0 => [0, 'B']
            ],
            '0,1' => [
                0 => [1, 'Q']
            ]
        ];
        $board = new Board($boardArray);
        $moveController = new MoveController($from, $to, $board, $database);

        $moveController->executeMove();

        assertStringContainsString('Restart the game to play again', $_SESSION['error']);
    }

    private function setUpGrassShopper(): array
    {
        $database = new DatabaseServiceMock();
        $_SESSION = [
            'player' => 1,
            'hand' => [
                1 => ['piece1', 'piece2'],
            ],
        ];
        $boardArray = [
            '0,0' => [
                0 => [0, 'Q']
            ],
            '0,1' => [
                0 => [1, 'Q']
            ],
            '-1,0' => [
                0 => [0, 'G']
            ],
            '-2,0' => [
                0 => [0, 'B']
            ],
            '-2,-1' => [
                0 => [0, 'G']
            ],
        ];
        $board = new Board($boardArray);

        return [$database, $board, $boardArray];
    }

    private function setUpSoldierAnt(): array
    {
        $database = new DatabaseServiceMock();
        $_SESSION = [
            'player' => 1,
            'hand' => [
                1 => ['A', 'S'],
            ],
        ];
        $boardArray = [
            '0,0' => [
                0 => [0, 'Q']
            ],
            '0,1' => [
                0 => [1, 'Q']
            ],
            '1,-1' => [
                0 => [0, 'B']
            ],
            '1,1' => [
                0 => [1, 'B']
            ],
            '0,-1' => [
                0 => [0, 'A']
            ],
            '2,0' => [
                0 => [1, 'B']
            ],
        ];
        $board = new Board($boardArray);
        return [$database, $board, $boardArray];
    }

    private function setUpSpider(): array
    {
        $database = new DatabaseServiceMock();
        $_SESSION = [
            'player' => 0,
            'hand' => [
                0 => ["Q" => 0, "B" => 2, "S" => 2, "A" => 3, "G" => 3]
            ],
        ];
        $boardArray = [
            '0,0' => [
                0 => [0, 'Q']
            ],
            '0,1' => [
                0 => [1, 'Q']
            ],
            '1,-1' => [
                0 => [0, 'B']
            ],
            '1,1' => [
                0 => [1, 'B']
            ],
            '2,0' => [
                0 => [1, 'B']
            ],
            '1,3' => [
                0 => [0, 'B']
            ],
            '1,2' => [
                0 => [0, 'A']
            ],
            '0,3' => [
                0 => [0, 'G']
            ],
            '2,3' => [
                0 => [0, 'S']
            ],
        ];
        $board = new Board($boardArray);
        return [$database, $board, $boardArray];
    }
}
