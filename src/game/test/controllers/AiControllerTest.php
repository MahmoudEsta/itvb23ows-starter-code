<?php

namespace test\controllers;

use controllers\AiController;
use objects\Board;
use PHPUnit\Framework\TestCase;
use test\mocks\DatabaseServiceMock;
use function PHPUnit\Framework\assertArrayNotHasKey;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertThat;

class AiControllerTest extends TestCase
{
    public function testAiPlay()
    {
        $database = new DatabaseServiceMock();
        $boardArray = [
            '0,0' => [
                0 => [0, 'Q']
            ],
        ];
        $_SESSION = [
            'endGame' => false,
            'last_move' => 1,
            'game_id' => 12345,
            'player' => 1,
            'move_number' => 1,
            'hand' => [0 => ["Q" => 0, "B" => 2, "S" => 2, "A" => 3, "G" => 3],
                       1 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3]],
            'board' => $boardArray,
        ];
        $board = new Board($boardArray);
        $aiController = $this->getMockBuilder(AiController::class)
            ->setConstructorArgs([$board ,$database])
            ->onlyMethods(['getResults'])
            ->getMock();

        $expectedResult = ["play", "Q", "1,0"];
        $aiController->expects($this->any())
            ->method('getResults')
            ->willReturn(json_decode(json_encode($expectedResult)));

        $aiController->executeAiPlay();

        assertNotNull($_SESSION['board']['1,0']);
    }

    public function testGetAIResults()
    {
        $aiControllerMock = $this->getMockBuilder(AiController::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getResults'])
            ->getMock();

        $expectedResult = ["play", "Q", "1,0"];
        $aiControllerMock->expects($this->any())
            ->method('getResults')
            ->willReturn(json_decode(json_encode($expectedResult)));

        $result = $aiControllerMock->getResults();
        $this->assertEquals($expectedResult, $result);
    }

    public function testAiMove()
    {
        $database = new DatabaseServiceMock();
        $boardArray = [
            '0,0' => [
                0 => [0, 'Q']
            ],
            '1,0' => [
                0 => [1, 'Q']
            ],
        ];
        $_SESSION = [
            'endGame' => false,
            'last_move' => 1,
            'game_id' => 12345,
            'player' => 1,
            'move_number' => 1,
            'hand' => [0 => ["Q" => 0, "B" => 2, "S" => 2, "A" => 3, "G" => 3],
                1 => ["Q" => 0, "B" => 2, "S" => 2, "A" => 3, "G" => 3]],
            'board' => $boardArray,
        ];
        $board = new Board($boardArray);
        $aiController = $this->getMockBuilder(AiController::class)
            ->setConstructorArgs([$board ,$database])
            ->onlyMethods(['getResults'])
            ->getMock();

        $expectedResult = ["move", "0,0", "0,1"];
        $aiController->expects($this->any())
            ->method('getResults')
            ->willReturn(json_decode(json_encode($expectedResult)));

        $aiController->executeAiPlay();

        assertNotNull($_SESSION['board']['0,1']);
        assertArrayNotHasKey('0,0', $_SESSION['board']);
    }

    public function testAiPass()
    {
        $database = new DatabaseServiceMock();
        $boardArray = [
            '0,0' => [
                0 => [0, 'Q']
            ],
            '1,0' => [
                0 => [1, 'Q']
            ],
        ];
        $_SESSION = [
            'endGame' => false,
            'last_move' => 1,
            'game_id' => 12345,
            'player' => 1,
            'move_number' => 1,
            'hand' => [0 => ["Q" => 0, "B" => 2, "S" => 2, "A" => 3, "G" => 3],
                1 => ["Q" => 0, "B" => 2, "S" => 2, "A" => 3, "G" => 3]],
            'board' => $boardArray,
        ];
        $board = new Board($boardArray);
        $aiController = $this->getMockBuilder(AiController::class)
            ->setConstructorArgs([$board ,$database])
            ->onlyMethods(['getResults'])
            ->getMock();

        $expectedResult = ["pass", null, null];
        $aiController->expects($this->any())
            ->method('getResults')
            ->willReturn(json_decode(json_encode($expectedResult)));

        $aiController->executeAiPlay();

        assertEquals($_SESSION['board'], $boardArray);
    }

}