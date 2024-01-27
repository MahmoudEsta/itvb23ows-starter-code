<?php

namespace test\controllers;

use controllers\MoveController;
use objects\Board;
use PHPUnit\Framework\TestCase;
use test\mocks\DatabaseServiceMock;

class moveControllerTest extends TestCase
{
    public function testValidateGrassshopperMoveFromIsSameASTo()
    {
        [$database, $board, $boardArray] = $this->setUpGrassShopper();
        $from = "0,2";
        $to = "0,2";
        $moveController = new MoveController($from, $to, $board, $database);

        $result = $moveController->validateGrasshopperMove($boardArray);

        $this->assertFalse($result);
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
}
