<?php
/**
 * Board class
 *
 * @author Jose Porta
 */
class Board {
    private $current = array();
    private $solutions = array();
    private $currentRow;
    private $currentColumn;
    private $currentDirection;
    private $solutionLastRow = array();
    private $solutionLastColumn = array();
    private $solutionLastDirection = array();
    const RIGHT = 1;
    const DOWN = 2;
    const LEFT = 3;
    const UP = 4;

    /*
     * Class default constructor
     * Sets up board and init parameters
     * Uses php Stack implementation (array) to store valid results
     * Supports different game configurations
     * 1=marble
     * 0=empty space
     * 2=not available
     * 
    */
    function __construct() {
        $this->current = array(
                0 => array(0=>2, 1=>2, 2=>1, 3=>1, 4=>1, 5=>2, 6=>2),
                1 => array(0=>2, 1=>2, 2=>1, 3=>1, 4=>1, 5=>2, 6=>2),
                2 => array(0=>1, 1=>1, 2=>1, 3=>1, 4=>1, 5=>1, 6=>1),
                3 => array(0=>1, 1=>1, 2=>1, 3=>0, 4=>1, 5=>1, 6=>1),
                4 => array(0=>1, 1=>1, 2=>1, 3=>1, 4=>1, 5=>1, 6=>1),
                5 => array(0=>2, 1=>2, 2=>1, 3=>1, 4=>1, 5=>2, 6=>2),
                6 => array(0=>2, 1=>2, 2=>1, 3=>1, 4=>1, 5=>2, 6=>2));
        $this->resetSearch();
        array_push($this->solutions, $this->current);
    }

    /*
     * Function that updates the current search position
     * First, searches in all directions (Up, Down, Right & Left)
     * If no more directions available, moves the search  position.
     * If no more search positions available, returns false
     *
    */
    private function moveSearch() {
        //Try another direction
        if ($this->currentDirection<4) {
            $this->currentDirection++;
            //No more direction changes available
        } else {
            $this->currentDirection=1;
            //Stop moving only when current position has a marble
            do {
                //Try to move one column to the right
                if ($this->currentColumn<6) {
                    $this->currentColumn++;
                    //Go to next row
                } else {
                    if ($this->currentRow<6) {
                        $this->currentRow++;
                        $this->currentColumn=0;
                    } else
                        return false;
                }
            } while ($this->current[$this->currentRow][$this->currentColumn]!=1);
        }
        return true;
    }

    /*
     * Sets search position at (0,0) and Current direction RIGHT
     * Fresh start
    */
    private function resetSearch() {
        $this->currentRow = 0;
        $this->currentColumn = 0;
        $this->currentDirection = Board::RIGHT;
    }

    /*
     * Prints current board configuration
    */

    private function printBoard($board) {
        foreach ($board as $row) {
            foreach ($row as $place) {
                switch ($place) {
                    case 0:
                        echo ".";
                        break;
                    case 1;
                        echo "O";
                        break;
                    case 2;
                        echo " ";
                        break;
                    default:
                        echo $place;
                }
            }
            echo "\n";
        }
        echo "-------\n";
    }

    /*
     * Updates current board configuration with moved marbles
    */
    private function updateBoard() {
        switch ($this->currentDirection) {
            case Board::RIGHT:
                $this->current[$this->currentRow][$this->currentColumn] = 0;
                $this->current[$this->currentRow][$this->currentColumn+1] = 0;
                $this->current[$this->currentRow][$this->currentColumn+2] = 1;
                break;
            case Board::DOWN:
                $this->current[$this->currentRow][$this->currentColumn] = 0;
                $this->current[$this->currentRow+1][$this->currentColumn] = 0;
                $this->current[$this->currentRow+2][$this->currentColumn] = 1;
                break;
            case Board::LEFT:
                $this->current[$this->currentRow][$this->currentColumn] = 0;
                $this->current[$this->currentRow][$this->currentColumn-1] = 0;
                $this->current[$this->currentRow][$this->currentColumn-2] = 1;
                break;
            case Board::UP:
                $this->current[$this->currentRow][$this->currentColumn] = 0;
                $this->current[$this->currentRow-1][$this->currentColumn] = 0;
                $this->current[$this->currentRow-2][$this->currentColumn] = 1;
                break;
            default:
                break;
        }
    }

    /*
     * Prints all solutions from stack.
    */
    public function printSolution () {
        echo"**********************\n";
        echo"****START SOLUTION****\n";
        echo"**********************\n";
        while ($popped = array_shift($this->solutions))
            $this->printBoard($popped);
        echo"**********************\n";
        echo"*****END SOLUTION*****\n";
        echo"**********************\n";
    }

    /*
     * This is the main function
    */
    public function process() {
        //Repeat until 31 moves found
        while(count($this->solutions)<32) {
            //Found marble that can be moved
            if ($this->search()) {
                //Update board with moved marbles
                $this->updateBoard();
                //Add current solution to stack
                array_push($this->solutions, $this->current);
                //Save last search status for backtracking
                array_push($this->solutionLastRow, $this->currentRow);
                array_push($this->solutionLastColumn, $this->currentColumn);
                array_push($this->solutionLastDirection, $this->currentDirection);
                //Reset search (start from beginning)
                $this->resetSearch();
            } else {
                //No more movements allowed, go back.
                if (!$this->moveSearch()) {
                    //Discard last solution found
                    array_pop($this->solutions);
                    //No more solutions in stack, exit.
                    if(count($this->solutions)==0) {
                        break;
                    }
                    //Set Current board status to last stored values
                    $this->current = $this->solutions[count($this->solutions)-1];
                    $this->currentRow = array_pop($this->solutionLastRow);
                    $this->currentColumn = array_pop($this->solutionLastColumn);
                    $this->currentDirection = array_pop($this->solutionLastDirection);
                    //Change direction to avoid repeated result in the next search round.
                    $this->currentDirection++;
                }
            }
        }
        return;
    }

    /*
     * Using the current position and direction, check if the marble can be moved.
    */
    private function search() {
        switch ($this->currentDirection) {
            case Board::RIGHT:
                return $this->searchRight();
            case Board::DOWN:
                return $this->searchDown();
            case Board::LEFT:
                return $this->searchLeft();
            case Board::UP:
                return $this->searchUp();
            default:
                return false;
        }
    }

    /*
     * Helper function that prints the position and
     * direction of the marble found for movement
     * (0,0) = Up left corner
     * (6,6) = Bottom right corner
    */
    private function printFoundMarble() {
        echo "(".$this->currentRow.",".$this->currentColumn.") - ";
        switch ($this->currentDirection) {
            case Board::RIGHT:
                echo ">> RIGHT >>";
                break;
            case Board::DOWN:
                echo "vv DOWN vv";
                break;
            case Board::LEFT:
                echo "<< LEFT <<";
                break;
            case Board::UP:
                echo "^^ UP ^^";
                break;
            default:
                echo "Unknown";
        }
        echo "\n";
    }

    private function searchRight() {
        //Check if there's enough space to move to the right
        if ($this->currentColumn<5) {
            //Check if adjacent position has a marble, and if position next to the adjacent is empty
            if (($this->current[$this->currentRow][$this->currentColumn+1]==1) && ($this->current[$this->currentRow][$this->currentColumn+2]==0)) {
                $this->printFoundMarble();
                return true;
            }
        }
        return false;
    }

    private function searchDown() {
        if ($this->currentRow<5) {
            if (($this->current[$this->currentRow+1][$this->currentColumn]==1) && ($this->current[$this->currentRow+2][$this->currentColumn]==0)) {
                $this->printFoundMarble();
                return true;
            }
        }
        return false;
    }
    private function searchLeft() {
        if ($this->currentColumn>1) {
            if (($this->current[$this->currentRow][$this->currentColumn-1]==1) && ($this->current[$this->currentRow][$this->currentColumn-2]==0)) {
                $this->printFoundMarble();
                return true;
            }
        }
        return false;
    }
    private function searchUp() {
        if ($this->currentRow>1) {
            if (($this->current[$this->currentRow-1][$this->currentColumn]==1) && ($this->current[$this->currentRow-2][$this->currentColumn]==0)) {
                $this->printFoundMarble();
                return true;
            }
        }
        return false;
    }
}
?>
