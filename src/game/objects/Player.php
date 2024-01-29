<?php

namespace objects;

class Player {
    private $hand;
    private $playerNumber;

    public function __construct($playerNumber ,$hand)
    {
        $this->hand = $hand;
        $this->playerNumber = $playerNumber;

    }
    public function getHand()
    {
        return $this->hand;
    }

    public function setHand($hand): void
    {
        $this->hand = $hand;
    }

    public function switchPlayer()
    {
        $_SESSION['player'] = 1 - $_SESSION['player'];
    }

    public function getPlayerNumber()
    {
        return $this->playerNumber;
    }

    public function setPlayerNumber($playerNumber): void
    {
        $this->playerNumber = $playerNumber;
    }

    public function getAvailableHandPieces(): array
    {
        $hand = $this->hand[$this->playerNumber];
        $pieces = [];

        foreach ($hand as $tile => $ct) {
            if ($ct > 0) {
                $pieces[] = $tile;
            }
        }

        return $pieces;
    }

    public function getPlayerPieces($board): array
    {
        $from = [];
        foreach ($board as $pos => $tiles) {
            if (end($tiles)[0] == $this->playerNumber) {
                $from[] = $pos;
            }
        }

        return $from;
    }

    function getPossiblePositionsForPlayer(Board $board)
    {
        $to = [];
        foreach ($board->getOffset() as $pq) {
            foreach (array_keys($board->getBoard()) as $pos) {
                $pq2 = explode(',', $pos);
                if ($pq[0] + $pq2[0] === $this->playerNumber) {
                    $position = ($pq[0] + $pq2[0]) . ',' . ($pq[1] + $pq2[1]);
                    if (!isset($board->getBoard()[$position])) {
                        $to[] = $position;
                    }
                }
            }
        }

        $to = array_unique($to);
        if (!count($to)) $to[] = '0,0';

        return $to;
    }

}

?>