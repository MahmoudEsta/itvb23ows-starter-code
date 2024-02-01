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
        if ($_SESSION['endGame']){
            $_SESSION['error'] = "Restart the game to play again";
        } else {
            unset($_SESSION['error']);

            $board = $this->board->getBoard();
            if (count($board[$this->from]) > 1) {
                $tile = array_pop($board[$this->from]);
            } else {
                $tile = array_pop($board[$this->from]);
                unset($board[$this->from]);
            }
            $this->validateMove($this->from, $this->to);
            if (isset($_SESSION['error'])) {
                if (isset($board[$this->from])) {
                    array_push($board[$this->from], $tile);
                } else {
                    $board[$this->from] = [$tile];
                }
            } else {
                $board = $this->moveWithoutAnyValidation($board, $this->to, $tile);
            }
            $_SESSION['board'] = $board;
        }
    }

    public function moveWithoutAnyValidation($board, $to, $tile)
    {
        if (isset($board[$to])) {
            array_push($board[$to], $tile);
            $_SESSION['move_number'] = $_SESSION['move_number'] + 1;
        } else {
            $board[$to] = [$tile];
            $_SESSION['move_number'] = $_SESSION['move_number'] + 1;
            if ($tile[1] == "Q"){
                if ($this->player == 0) {
                    $_SESSION['white_queen'] = $this->to;
                } else {
                    $_SESSION['black_queen'] = $this->to;
                }
            }
        }
        $_SESSION['player'] = 1 - $_SESSION['player'];
        $lastMove = $this->database->move($_SESSION['game_id'], $this->from, $this->to, $_SESSION['last_move']);
        $_SESSION['last_move'] = $lastMove;

        return $board;
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

    public function validateSoldierAntMove($board): bool
    {
        if ($this->from == $this->to) {
            $_SESSION['error'] = 'A Soldier Ant can not jump in the same place';
            return false;
        }

        if ($this->board->hasNoNeighbours($board, $this->to)){return false;}

        // Remove $from tile from board array
        unset($board[$this->from]);

        $visited = [];
        $tiles = array($this->from);

        // Find if path exists between $from and $to using DFS
        while (!empty($tiles)) {
            $currentTile = array_shift($tiles);

            if (!in_array($currentTile, $visited)) {
                $visited[] = $currentTile;
            }

            $b = explode(',', $currentTile);

            // Put all adjacent legal board positions relative to current tile in $tiles array
            foreach ($this->board->getOffset() as $pq) {
                $p = $b[0] + $pq[0];
                $q = $b[1] + $pq[1];

                $position = $p . "," . $q;

                if (
                    !in_array($position, $visited) &&
                    !isset($board[$position]) &&
                    $this->board->hasNeighbour($position, $board)
                ) {
                    if ($position == $this->to) {
                        return true;
                    }
                    $tiles[] = $position;
                }
            }
        }

        return false;
    }

    public function validateSpiderMove($board): bool {
        unset($board[$this->from]);
        $visited = [];
        $tiles = [$this->from];
        $tiles[] = null;
        $prevTile = null;
        $depth = 0;

        if ($this->from == $this->to) {
            $_SESSION['error'] = 'A Spider can not jump in the same place';
            return false;
        }

        if ($this->board->hasNoNeighbours($board, $this->to)){return false;}

        while (!empty($tiles) && $depth < 3) {
            $currentTile = array_shift($tiles);

            // Null is added to $tiles array to indicate an increase in depth
            if ($currentTile == null) {
                $depth++;
                $tiles[] = null;

                if (reset($tiles) == null) {
                    // Double null = all nodes have been visited
                    break;
                } else {
                    continue;
                }
            }

            if (!in_array($currentTile, $visited)) {
                $visited[] = $currentTile;
            }

            $b = explode(',', $currentTile);

            // Put all adjacent legal board positions relative to the current tile in $tiles array
            foreach ($this->board->getOffset() as $pq) {
                $p = $b[0] + $pq[0];
                $q = $b[1] + $pq[1];
                $position = $p . "," . $q;

                if (
                    !in_array($position, $visited) &&
                    $position != $prevTile &&
                    !isset($board[$position]) &&
                    $this->board->hasNeighbour($position, $board)
                ) {
                    if ($position == $this->to && $depth == 2) {
                        return true;
                    }

                    $tiles[] = $position;
                }
            }

            $prevTile = $currentTile;
        }

        return false;
    }

    public function canSlide($board, $to): bool
    {
        $offset = $this->board->getOffset();
        $b = explode(',', $to);
        $counter = 0;
        foreach ($offset as $ps)
        {
            $p = $b[0] + $ps[0];
            $q = $b[1] + $ps[1];
            $position = $p . "," . $q;
            if (isset($board[$position])){$counter = $counter + 1;}
        }

        if ($counter >= 5){
            return false;
        }
        return true;
    }

    private function validateMove($from, $to)
    {
        unset($_SESSION['error']);

        $board = $this->board->getBoard();
        if (!isset($board[$from])) {
            $_SESSION['error'] = 'Board position is empty';
        } elseif ($board[$from][count($board[$from])-1][0] != $this->player) {
            $_SESSION['error'] = "Tile is not owned by player";
        } elseif ($this->hand['Q']) {
            $_SESSION['error'] = "Queen bee is not played";
        } elseif (!$this->board->hasNeighBour($to, $board)) {
            $_SESSION['error'] = "Move would split hive";
        } else {
            if (count($board[$from]) > 1) {
                $tile = array_pop($board[$from]);
            } else {
                $tile = array_pop($board[$from]);
                unset($board[$from]);
            }
            $all = $this->getSplitTiles($board);
            if ($all) {
                $_SESSION['error'] = "Move would split hive";
            } else {
                if ($from == $to) {
                    $_SESSION['error'] = 'Tile must move';
                } elseif (isset($board[$to]) && $tile[1] != "B") {
                    $_SESSION['error'] = 'Tile not empty';
                } elseif ($tile[1] == "Q" || $tile[1] == "B") {
                    if (!$this->board->slide($from, $to, $this->board->getBoard())) {
                        $_SESSION['error'] = 'Tile must slide';
                    }
                } elseif ($tile[1] == "G") {
                    if (!$this->validateGrasshopperMove($this->board->getBoard())) {
                        $_SESSION['error'] = 'Unvalid move for Grassshopper';
                    }
                } elseif ($tile[1] == "A") {
                    if (!$this->validateSoldierAntMove($this->board->getBoard())) {
                        $_SESSION['error'] = 'Unvalid move for Soldier Ant';
                    } else {
                        if (!$this->canSlide($this->board->getBoard(), $this->to)) {
                            $_SESSION['error'] = 'Soldier Ant can not fit';
                        }
                    }
                } elseif ($tile[1] == "S") {
                    if (!$this->validateSpiderMove($this->board->getBoard())) {
                        $_SESSION['error'] = 'Unvalid move for Spider';
                    } else {
                        if (!$this->canSlide($this->board->getBoard(), $to)) {
                            $_SESSION['error'] = 'Spider can not fit';
                        }
                    }
                }
            }
        }
    }

    public function noValidMoves()
    {
        $board = $this->board->getBoard();
        $possiblePositions = $this->board->getPossiblePositions();
        foreach ($board as $pos => $tiles) {
            $topTile = end($tiles);

            if ($topTile[0] == $this->player) {
                foreach ($possiblePositions as $to) {
                    $this->validateMove($pos, $to);
                    if (!isset($_SESSION["error"])) {
                        $_SESSION['error'] = 'You can still move '.$pos;
                        return;
                    }
                }
            }
        }
    }

    private function getDirection($fromExploded, $toExploded): ?array
    {
        $from0 = $fromExploded[0];
        $from1 = $fromExploded[1];
        $to0 = $toExploded[0];
        $to1 = $toExploded[1];

        $differenceFrom = abs($from0 - $to0);
        $differenceTo = abs($from1 - $to1);

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

