<?php

namespace database;
use mysqli;

class DatabaseService
{
    private $db;

    public function __construct()
    {
        $this->db = new mysqli('hive-db', 'root', 'EstaCR7', 'hive');
    }

    private function get_state(): string
    {
        return serialize([$_SESSION['hand'], $_SESSION['board'], $_SESSION['player']]);
    }

    public function set_state($state)
    {
        list($a, $b, $c) = unserialize($state);
        $_SESSION['hand'] = $a;
        $_SESSION['board'] = $b;
        $_SESSION['player'] = $c;
    }

    public function pass($gameId, $lastMove)
    {
        $state = $this->get_state();
        $stmt = $this->db->prepare('INSERT INTO moves (game_id, type, move_from, move_to, previous_id, state) VALUES (?, "pass", null, null, ?, ?)');
        $stmt->bind_param('iss', $gameId, $lastMove, $state);
        $stmt->execute();
        return $this->db->insert_id;
    }

    public function restart()
    {
        $this->db->prepare('INSERT INTO games VALUES ()')->execute();
        return $this->db->insert_id;
    }

    public function undo($lastMove)
    {
        $stmt = $this->db->prepare('SELECT * FROM moves WHERE id = ' . $lastMove);
        $stmt->execute();
        return $stmt->get_result()->fetch_array();
    }

    public function move($gameId, $from, $to, $lastMove)
    {
        $state = $this->get_state();
        $stmt = $this->db->prepare('INSERT INTO moves (game_id, type, move_from, move_to, previous_id, state) VALUES (?, "move", ?, ?, ?, ?)');
        $stmt->bind_param('issis', $gameId, $from, $to, $lastMove, $state);
        $stmt->execute();
        return $this->db->insert_id;
    }

    public function play($gameId, $piece, $to, $lastMove)
    {
        $state = $this->get_state();
        $stmt = $this->db->prepare('INSERT INTO moves (game_id, type, move_from, move_to, previous_id, state) VALUES (?, "play", ?, ?, ?, ?)');
        $stmt->bind_param('issis', $gameId, $piece, $to, $lastMove, $state);
        $stmt->execute();
        return $this->db->insert_id;
    }

    public function oldMoves(int $getId)
    {
        $stmt = $this->db->prepare('SELECT * FROM moves WHERE game_id = '. $getId);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function deleteMove($id) {
        $stmt = $this->db->prepare('DELETE FROM moves WHERE id = '. $id);

        $stmt->execute();
    }

}

