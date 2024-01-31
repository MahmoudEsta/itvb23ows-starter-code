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
        if ($_SESSION['endGame']){
            $_SESSION['error'] = "Restart the game to play again";
        } else{
            unset($_SESSION['error']);
            if ($this->validatePlay($this->piece, $this->to)){
                $this->executePlayWithoutAnyValidation();
            }
        }
    }

    public function executePlayWithoutAnyValidation()
    {
        $board = $this->board->getBoard();
        $board[$this->to] = [[$this->player, $this->piece]];
        $this->hand[$this->piece]--;
        $_SESSION['player'] = 1 - $_SESSION['player'];
        $lastMove = $this->database->play($_SESSION['game_id'], $this->piece, $this->to, $_SESSION['last_move']);
        $_SESSION['last_move'] = $lastMove;
        $_SESSION['board'] = $board;
        $_SESSION['hand'][$this->player] = $this->hand;
        $_SESSION['move_number'] = $_SESSION['move_number'] + 1;
        if ($this->piece == "Q" ) {
            if ($this->player == 0) {
                $_SESSION['white_queen'] = $this->to;
            } else {
                $_SESSION['black_queen'] = $this->to;
            }
        }
    }

    private function validatePlay($piece, $to): bool
    {
        $board = $this->board->getBoard();
        if (!$this->hand[$piece]) {
            $_SESSION['error'] = "Player does not have tile";
        } elseif (isset($board[$to])) {
            $_SESSION['error'] = 'Board position is not empty';
        } elseif (count($board) && !$this->board->hasNeighBour($to, $board)) {
            $_SESSION['error'] = "Board position has no neighbour";
        } elseif (array_sum($this->hand) < 11 && !$this->board->neighboursAreSameColor($this->player, $to, $board)) {
            $_SESSION['error'] = "Board position has opposing neighbour";
        } elseif ($piece != 'Q' && array_sum($this->hand) <= 8 && $this->hand['Q']) {
            $_SESSION['error'] = 'Must play queen bee';
        } else {
            return true;
        }
        return false;
    }

    public function noValidPlays()
    {
        $possiblePositions = $this->board->getPossiblePositions();
        foreach ($possiblePositions as $pos) {
            foreach ($this->hand as $pieceType => $amount) {
                if ($amount > 0 && $this->validatePlay($pieceType, $pos)) {
                    unset($_SESSION["error"]);
                    $_SESSION['error'] = 'You can still play '.$pieceType." at ".$pos;
                    return;
                }
            }
        }
    }
}

