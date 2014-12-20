<?php
/**
 * This script is intended to be run from Command Line, not a Browser.
 *
 * @author Jose Porta
 */
require_once('Board.php');
$b = new Board();
$startTime = microtime(true);
$b->process();
$b->printSolution();
$endTime = microtime(true);
$elapsedTime = $endTime - $startTime;
echo "Solution found in: ".$elapsedTime." seconds";

?>
