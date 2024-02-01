<?php

namespace controllers;

use database\DatabaseService;
use objects\Board;

class AiController
{
    private string $url = 'http://ai:5000/';
    private $moveNumber;
    private Board $board;
    private $hand;
    private DatabaseService $database;

    public function __construct(Board $board, DatabaseService $database)
    {
        $this->database = $database;
        $this->moveNumber = $_SESSION["move_number"];
        $this->board = $board;
        $this->hand = $_SESSION['hand'];
    }

    public function setDatabase(DatabaseService $database)
    {
        $this->database = $database;
    }

    public function getResults()
    {
        $body = [
            'move_number' => $this->moveNumber,
            'hand' => $this->hand,
            'board' => $this->board->getBoard()
        ];

        $options = [
            'http' => [
                'header' => "Content-Type: application/json\r\n",
                'method' => 'POST',
                'content' => json_encode($body),
            ],
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($this->url, false, $context);
        return json_decode($result);
    }

    public function executeAiPlay()
    {
        if ($_SESSION['endGame']){
            $_SESSION['error'] = "Restart the game to play again";
        } else {
            $resultArray = $this->getResults();
            $board = $this->board->getBoard();

            if (!isset($resultArray)) {
                $_SESSION['error'] = "There is no AI move received";
                return;
            }

            if ($resultArray[0] == "play") {
                $playController = new PlayController($resultArray[1], $resultArray[2], $this->board, $this->database);
                $playController->executePlayWithoutAnyValidation();
            } elseif ($resultArray[0] == "move") {
                if (count($board[$resultArray[1]]) > 1) {
                    $tile = array_pop($board[$resultArray[1]]);
                } else {
                    $tile = array_pop($board[$resultArray[1]]);
                    unset($board[$resultArray[1]]);
                }
                $moveController = new MoveController($resultArray[1], $resultArray[2], $this->board, $this->database);
                $result = $moveController->moveWithoutAnyValidation($board, $resultArray[2], $tile);
                $_SESSION['board'] = $result;
            } elseif ($resultArray[0] == "pass") {
                $passController = new PassController($this->database);
                $passController->pass();
            }
        }

    }

}
