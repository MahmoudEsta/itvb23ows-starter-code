<?php
    session_start();

    use controllers\PlayController as playController;
    use controllers\MoveController as moveController;
    use controllers\PassController as passController;
    use controllers\RestartController as restartController;
    use controllers\UndoController as undoController;
    use database\DatabaseService;
    use objects\Board;
    use objects\Game;
    use objects\Player;

    require_once './vendor/autoload.php';

    $database = new DatabaseService();
    $restartController = new restartController($database);

    // Handle 'Restart' button press and unset board (initial condition)
    if (array_key_exists('restart', $_POST) || $_SESSION['board'] == null) {
        $restartController->restartGame();
    }

    $board = new Board($_SESSION['board']);
    $hand = $_SESSION['hand'];
    $player = new Player($_SESSION['player'] ,$hand);
    $game = new Game($player, $board, $_SESSION['game_id']);

    // Handle 'Pass' button press
    if(array_key_exists('pass', $_POST)) {
        $passController = new passController($database);
        $passController->pass();

        header('Location: ./index.php');
    }

    // Handle 'Undo' button press
    if(array_key_exists('undo', $_POST)) {
        $undoController = new undoController($database);
        $undoController->undoMove();
        $game->getPlayer()->switchPlayer();

        header('Location: ./index.php');
    }

    // Handle 'Play' button press
    if(array_key_exists('play', $_POST)) {
        $piece = $_POST['piece'];
        $to = $_POST['to'];

        $playController = new playController($piece, $to, $game->getBoard(), $database);
        $playController->executePlay();

        header('Location: ./index.php');
    }

    // Handle 'Move' button press
    if(array_key_exists('move', $_POST) && isset($_POST['from'])) {
        $from = $_POST['from'];
        $to = $_POST['to'];

        $moveController = new moveController($from, $to, $game->getBoard(), $database);
        $moveController->executeMove();

        header('Location: ./index.php');
    }

    $to = $game->getBoard()->getPossiblePositions();
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Hive</title>
        <link rel="stylesheet" type="text/css" href="./css/default.css">
    </head>
    <body>
        <div class="board">
            <?php
                $min_p = 1000;
                $min_q = 1000;
                foreach ($game->getBoard()->getBoard() as $pos => $tile) {
                    $pq = explode(',', $pos);
                    if ($pq[0] < $min_p) $min_p = $pq[0];
                    if ($pq[1] < $min_q) $min_q = $pq[1];
                }
                foreach (array_filter($game->getBoard()->getBoard()) as $pos => $tile) {
                    $pq = explode(',', $pos);
                    $pq[0];
                    $pq[1];
                    $h = count($tile);
                    echo '<div class="tile player';
                    echo $tile[$h-1][0];
                    if ($h > 1) echo ' stacked';
                    echo '" style="left: ';
                    echo ($pq[0] - $min_p) * 4 + ($pq[1] - $min_q) * 2;
                    echo 'em; top: ';
                    echo ($pq[1] - $min_q) * 4;
                    echo "em;\">($pq[0],$pq[1])<span>";
                    echo $tile[$h-1][1];
                    echo '</span></div>';
                }
            ?>
        </div>
        <div class="hand">
            White:
            <?php
                foreach ($game->getPlayer()->getHand()[0] as $tile => $ct) {
                    for ($i = 0; $i < $ct; $i++) {
                        echo '<div class="tile player0"><span>'.$tile."</span></div> ";
                    }
                }
            ?>
        </div>
        <div class="hand">
            Black:
            <?php
            foreach ($game->getPlayer()->getHand()[1] as $tile => $ct) {
                for ($i = 0; $i < $ct; $i++) {
                    echo '<div class="tile player1"><span>'.$tile."</span></div> ";
                }
            }
            ?>
        </div>
        <div class="turn">
            Turn: <?php if ($game->getPlayer()->getPlayerNumber() == 0) echo "White"; else echo "Black"; ?>
        </div>
        <form method="post">
            <select name="piece">
                <?php
                    foreach ($game->getPlayer()->getAvailableHandPieces() as $piece) {
                        echo "<option value=\"$piece\">$piece</option>";
                    }
                ?>
            </select>
            <select name="to">
                <?php
                    foreach ($to as $pos) {
                        echo "<option value=\"$pos\">$pos</option>";
                    }
                ?>
            </select>
            <input type="submit" name="play" value="Play">
        </form>
        <form method="post">
            <select name="from">
                <?php
                    foreach (array_keys($game->getBoard()->getBoard()) as $pos) {
                        echo "<option value=\"$pos\">$pos</option>";
                    }
                ?>
            </select>
            <select name="to">
                <?php
                    foreach ($to as $pos) {
                        echo "<option value=\"$pos\">$pos</option>";
                    }
                ?>
            </select>
            <input type="submit" name="move" value="Move">
        </form>
        <form method="post">
            <input type="submit" name="pass" value="Pass">
        </form>
        <form method="post">
            <input type="submit" name="restart" value="Restart">
        </form>
        <strong>
            <?php if (isset($_SESSION['error'])) echo($_SESSION['error']); unset($_SESSION['error']); ?>
        </strong>
        <ol>
            <?php
                $result = $database->oldMoves($game->getId());
                while ($row = $result->fetch_array()) {
                    echo '<li>'.$row[2].' '.$row[3].' '.$row[4].'</li>';
                }
            ?>
        </ol>
        <form method="post">
            <input type="submit" name="undo" value="Undo">
        </form>
    </body>
</html>

