<?php

namespace controllers;

use objects\Board;
use database\DatabaseService;

class MoveController
{
    private $from;
    private $to;
    private $player;
    private Board $board;
    private $hand;
    private DatabaseService $database;

    public function __construct($from, $to, Board $board, DatabaseService $database)
    {
        $this->from = $from;
        $this->to = $to;
        $this->player = $_SESSION['player'];
        $this->board = $board;
        $this->hand = $_SESSION['hand'][$this->player];
        $this->database = $database;

    }

    public function executeMove()
    {
        unset($_SESSION['error']);

        $board = $this->board->getBoard();
        if (!isset($board[$this->from])) {
            $_SESSION['error'] = 'Board position is empty';
        } elseif ($board[$this->from][count($board[$this->from])-1][0] != $this->player) {
            $_SESSION['error'] = "Tile is not owned by player";
        } elseif ($this->hand['Q']) {
            $_SESSION['error'] = "Queen bee is not played";
        } elseif (!$this->board->hasNeighBour($this->to, $board)) {
            $_SESSION['error'] = "Move would split hive";
        }else {
            if (count($board[$this->from]) > 1){
                $tile = array_pop($board[$this->from]);}
            else{
                $tile = array_pop($board[$this->from]);
                unset($board[$this->from]);
            }
            $all = $this->getSplitTiles($board);
                if ($all) {
                    $_SESSION['error'] = "Move would split hive";
                } else {
                    if ($this->from == $this->to) {
                        $_SESSION['error'] = 'Tile must move';
                    } elseif (isset($board[$this->to]) && $tile[1] != "B") {
                        $_SESSION['error'] = 'Tile not empty';
                    } elseif ($tile[1] == "Q" || $tile[1] == "B") {
                        if (!$this->board->slide($this->from, $this->to, $this->board->getBoard())) {
                            $_SESSION['error'] = 'Tile must slide';
                        }
                    } elseif ($tile[1] == "G"){
                        if (!$this->validateGrasshopperMove($this->board->getBoard())){
                            $_SESSION['error'] = 'Unvalid move for Grassshopper';
                        }
                    }
                }

            // zet de from terug in je bord
            if (isset($_SESSION['error'])) {
                if (isset($board[$this->from])) {
                    array_push($board[$this->from], $tile);
                } else {
                    $board[$this->from] = [$tile];
                }
            // move from to to als er geen fouten zijn
            } else {
                if (isset($board[$this->to])) {
                    array_push($board[$this->to], $tile);
                } else {
                    $board[$this->to] = [$tile];
                }

                $_SESSION['player'] = 1 - $_SESSION['player'];
                $lastMove = $this->database->move($_SESSION['game_id'], $this->from, $this->to, $_SESSION['last_move']);
                $_SESSION['last_move'] = $lastMove;
            }
        }

        $_SESSION['board'] = $board;
    }

    private function getSplitTiles($board): array
    {
        // checken of hive gesplitst is
        $all = array_keys($board);
        $queue = [array_shift($all)];

        while ($queue) {
            $next = explode(',', array_shift($queue));
            foreach ($this->board->getOffset() as $pq) {
                list($p, $q) = $pq;
                $p += $next[0];
                $q += $next[1];

                $position = $p . "," . $q;

                if (in_array($position, $all)) {
                    $queue[] = $position;
                    $all = array_diff($all, [$position]);
                }
            }
        }

        return $all;
    }

    public function validateGrasshopperMove($board): bool
    {
        if ($this->from == $this->to) {
            $_SESSION['error'] = 'A grasshopper can not jump in the same place';
            return false;
        }

        $fromExploded = explode(',', $this->from);
        $toExploded = explode(',', $this->to);


        $direction = $this->getDirection($fromExploded, $toExploded);
        if ($direction == null) {return false;}

        $p = $fromExploded[0] + $direction[0];
        $q = $fromExploded[1] + $direction[1];

        $position = $p . "," . $q;
        $positionExploded = [$p, $q];

        if (!isset($board[$position])) {
            return false;
        }

        while (isset($board[$position])) {
            $p = $positionExploded[0] + $direction[0];
            $q = $positionExploded[1] + $direction[1];

            $position = $p . "," . $q;
            $positionExploded = [$p, $q];
        }

        if ($position == $this->to) {
            return true;
        }
        return false;
    }

    private function getDirection($fromExploded, $toExploded): ?array
    {
        $from0 = $fromExploded[0];
        $from1 = $fromExploded[1];
        $to0 = $toExploded[0];
        $to1 = $toExploded[1];

        $differenceFrom = abs($from0 - $from1);
        $differenceTo = abs($to0 - $to1);

        if ($from0 == $to0){
            return $to1 > $from1 ? [0, 1] : [0, -1];
        }elseif ($from1 == $to1){
            return $to0 > $from0 ? [1, 0] : [-1, 0];
        }elseif ($differenceFrom == $differenceTo){
            return $to1 > $from1 ? [-1, 1] : [1, -1];
        }else {
            return null;
        }
    }

}

