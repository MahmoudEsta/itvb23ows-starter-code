<?php

namespace controllers;

use objects\Board;
use database\DatabaseService;

class PlayController
{
    private $piece;
    private $to;
    private $player;
    private Board $board;
    private $hand;
    private DatabaseService $database;

    public function __construct($piece, $to, $board, DatabaseService $database)
    {
        $this->piece = $piece;
        $this->to = $to;
        $this->player = $_SESSION['player'];
        $this->board = $board;
        $this->hand = $_SESSION['hand'][$this->player];
        $this->database = $database;
    }


    public function executePlay()
    {
        unset($_SESSION['error']);
        $thisBoard = $this->board->getBoard();
        if (!$this->hand[$this->piece]) {
            $_SESSION['error'] = "Player does not have tile";
        } elseif (isset($thisBoard[$this->to])) {
            $_SESSION['error'] = 'Board position is not empty';
        } elseif (count($thisBoard) && !$this->board->hasNeighBour($this->to, $thisBoard)) {
            $_SESSION['error'] = "Board position has no neighbour";
        } elseif (array_sum($this->hand) < 11 && !$this->board->neighboursAreSameColor($this->player, $this->to, $thisBoard)) {
            $_SESSION['error'] = "Board position has opposing neighbour";
        } elseif ($this->piece != 'Q' && array_sum($this->hand) <= 8 && $this->hand['Q']) {
            $_SESSION['error'] = 'Must play queen bee';
        } else {
            $thisBoard[$this->to] = [[$this->player, $this->piece]];
            $this->hand[$this->piece]--;
            $_SESSION['player'] = 1 - $_SESSION['player'];

            $lastMove = $this->database->play($_SESSION['game_id'], $this->piece, $this->to, $_SESSION['last_move']);
            $_SESSION['last_move'] = $lastMove;
        }

        $_SESSION['board'] = $thisBoard;
        $_SESSION['hand'][$this->player] = $this->hand;

    }
}

