<?php

/**
* 
*/
error_reporting(E_ALL);
ini_set('display_errors', 1);

class test_gamecounts extends CI_Controller
{
  
    function __construct()
    {
        parent::__construct();

        $this->load->model( 'gamecount' );
        //loading library unit test
        $this->load->library('unit_test');

        // loading database test
        $this->load->database('test', TRUE);

        //To enable strict mode 
        $this->unit->use_strict(TRUE);

        // Disable database debugging so we can test all units without stopping
        // at the first SQL error
        $this->db->db_debug = FALSE;
    }

    /**
     *  testAddGameCount 
     *  Function testing create gameCounts of player
     * 
     */
    function testAddGameCount() {
        // To verify player add new gameCount is invalid
        // =============================================
        // To verify player id input is invalid
        $playerIdInvalid = array(null, '', 'abc', 0, -123);
        foreach ($playerIdInvalid as $key => $value) {
            $data = array();
            $testResutlOne = $this->gamecount->add($value, $data);
            if ( is_array($testResutlOne) && isset($testResutlOne['errors'])) {

                $this->unit->run($testResutlOne['errors'], "Id must is a numeric and greater than zero", "To verify player add new gameCount is invalid", "To verify player id input is invalid");
            }
        }

        // To verify data input is invalid
        $dataInvalid = array(null, '');

        foreach ($dataInvalid as $value) {
            
            $testResutlSecond = $this->gamecount->add(13, $data);

            // To verify if user is not login before create newGameCount
            if ( is_array($testResutlSecond) && isset($testResutlSecond['error'])) {

                $this->unit->run($testResutlSecond['error'], "Not authorized", "To verify player add new gameCount is invalid", "To verify if user is not login before create newGameCount");
            }

        }

        foreach ($dataInvalid as $value) {
            $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);

            $login = $this->player->login($dataLogin);
            if (is_array($login) && isset($login['token']) ) {
                
                $this->player->setToken( $login['token'] );
                $playerId = $login['playerId'];
                $testResutlThird = $this->gamecount->add($playerId, $value);

                if (is_array($testResutlThird) && isset($testResutlThird['error']) ) {

                    $this->unit->run($testResutlThird['error'], "The gameType is required", "To verify player add new gameCount is invalid", "To verify data input is invalid");
                }
            } else {

                echo "<h4 style='color: red;'>".$login['error']." when try add new gameCount. Pls try again.<h4>";
            }
        }    
        
        // To verify player add new gameCount is valid
        // =============================================
        $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);
        $data['gameType'] = "ABC";
        $login = $this->player->login($dataLogin);
        
        if (is_array($login) && isset($login['token']) ) {
            
            $this->gamecount->setToken( $login['token']);
            $this->player->setToken( $login['token'] );
            $playerId = $login['playerId'];
            $gameCount = $this->gamecount->getByPlayerId($playerId, null);
            if (is_array($gameCount) && isset($gameCount['gameCounts'])) {
                $gameCounts = [];
                $gameCounts = $gameCount['gameCounts'];

                foreach ( $gameCounts as $value) {

                    if ($value->gameType === "ABC") {

                        $count = $value->count;

                    } else {

                        $count = 0;
                    }
                }
                
            } else {

                $count = 0;
            } 
            
            $testResutlFourth = $this->gamecount->add($playerId, $data);

            if (is_object($testResutlFourth) && isset($testResutlFourth) ) {

                // To verify game count return is must be count + 1
                $this->unit->run((int)$testResutlFourth->count, (int)($count + 1), "To verify player add new gameCount is valid", "To verify game count return is must be count + 1");

                // To verify name type must be is "ABC"
                $this->unit->run($testResutlFourth->gameType, "ABC", "To verify player add new gameCount is valid", "To verify name type must be is 'ABC'");

            }

        } else {

            echo "<h4 style='color: red;'>".$login['errors']." when try add new gameCount. Pls try again.<h4>";
        }

        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());
    }
    
    /**
     * testGetByPlayerId 
     * 
     * Function testing get list gameCount by playerId
     */
    function testGetByPlayerId() {

    } 
    function returnResult($results) {
        $passed = [];
        $failed = [];
        foreach($results as $value) {
            if($value['Result'] === "Passed") {
                array_push($passed, $value['Result']);
            }

            if($value['Result'] === "Failed") {
                array_push($failed, $value['Result']);
            }
        }
        return "<h1> Tests: ". sizeof($results). ", Passed: " .sizeof($passed). ", Failed:".sizeof($failed)."</h1>";
    }
}