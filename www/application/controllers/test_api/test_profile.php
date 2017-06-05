<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
/**
 * Testing wheel 
 * 
 */
class test_profile extends CI_Controller {
  
    function __construct() {

        parent::__construct();

        //loading library unit test
        $this->load->library( 'unit_test' );

        // loading database test
        $this->load->database( 'test', TRUE );

        //To enable strict mode 
        $this->unit->use_strict( TRUE );

        // Disable database debugging so we can test all units without stopping
        // at the first SQL error
        $this->db->db_debug = FALSE;

    }

    function createWinConfirmation() {
        $this->load->model('winconfirmation');

        for ($i=0; $i < 20; $i++) { 

            $data = array(
            'winConfirmed' => 1,
            'playerId' => rand( 1, 3),
            // 'accountData',
            'serialNumber' => '341315135135351',
            'entry' => 1213123,
            'prizeAmount' => rand(3000, 4000)/100,
            'prizeName' => 'prizeName' . rand(1,40),
            'taxableAmount' => rand( 1, 20)/10,
            'payPalCorrelationId' => 'payPalCorrelationId' . rand(1,40),
            'payPalPayDate' => date('Y-m-' . rand(1,28) . ' H:i:s'),
            'playerActionChoice' => 0,
            'winDate'=> date('Y-m-' . rand(1,28) . ' H:i:s'),
            // 'payPalEmail',
            'payPalPaymentStatus' => 'P',
            'payPalTransactionId' => rand(10000000000, 40000000000),
            'status' => 'P',
            'cumulativeAnnualPaid' => rand(3000, 4000)/100,
            'organizationId' => $i,
            );

            $id = $this->winconfirmation->insert( $data, TRUE );
        }
    }

    function testGetProfile() {
        $this->load->model( 'facebookinvite' );
        $this->load->model( 'position' );
        $this->load->model( 'winconfirmation' );
        $this->load->model( 'gamecount' );
        // To verify get Profile return is invalid
        //=========================================
        
        // To verify id player is invalid
        $player = $this->player->limit(1)->order_by( 'id', 'DESC' )->get_all();
        $playerIdExit = !empty( $player ) ? $player[0]->id : 0 ;
        $playerIdNotExit = ($playerIdExit + 1); 
        $playIdInvalid = array(null, 'abc', 0, $playerIdNotExit, -1 );

        // To verify player is invalid
        foreach ( $playIdInvalid as $key => $value ) {
            
            $testResultFirst = $this->player->profile( $value );
            if ( is_array( $testResultFirst ) && isset( $testResultFirst['errors'] ) ) {

                // To verify player Id input is invalid
                $this->unit->run( $testResultFirst['errors'], 'Id must be a numeric and greater than zero', 'To verify get profile return is invalid', 'To verify player Id input is invalid' );
                
            } elseif ( is_array( $testResultFirst ) && isset( $testResultFirst['error'] )) {

                //To verify player is not exist
                $this->unit->run( $testResultFirst['error'] , 'Not authorized', "To verify get profile return is invalid", "To verify player is invalid" );
            }
        }

        // Login with admin 
        $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);

        $login = $this->player->login($dataLogin);
        $this->player->setToken( $login['token'] );
        $playerId = 2;

        // To verify get Profile return is valid
      
        // get position from db
        $position = $this->position->get_by( array( 'playerId' => $playerId, 'calendarDate' => date( 'Y-m-d' ) ) );
        $currentPositionExpected =  ($position) ? $position->endPosition : 0;
      
        // get highest position from database with playerId
        $highestExpected =  $this->position->order_by( 'endPosition', 'DESC' )->limit( 1 )->get_by( 'playerId', $playerId );
        $hightestPositionExpected = ($highestExpected) ? $highestExpected->endPosition : 0;
        

         // select max(prizeAmount) AS highest, sum(prizeAmount) AS total from WinConfirmations where playerId = $1
        $win = $this->db->select( 'max(prizeAmount) AS highest, sum(prizeAmount) AS total', FALSE )
                ->where( 'playerId', $playerId )
                ->get( 'WinConfirmations' )
                ->row();
        
        // get expected highest Win
        $highestWinExpected = empty($win->highest) ? 0.0 : (float)$win->highest;

        // get total AggregateWin expected
        $totalAddgrgateWinExpected = empty($win->total) ? 0.0 : (float)$win->total; 

        // get favoriteGame 
        $gameCount = $this->db->select( 'sum(count) AS count, gameType')
                    ->where( 'playerId', $playerId )
                    ->group_by( 'gameType' )
                    ->order_by( 'count', 'DESC' )
                    ->limit( 1 )
                    ->get( 'GameCount' )
                    ->row();
        
        $favoriteGameExpected = empty($gameCount) ? '' : $gameCount->gameType;

        // get list friend facebook
        $friendList = $this->facebookinvite->get_many_by( 'playerId', $playerId );

        $friendListExpected = array();

        if ( !empty( $friendList ) ) {

            foreach ($friendList as $key => $value) {
                
                array_push( $friendListExpected, $value->friendFacebookId );
            }
        }

        $testResultSecond = $this->player->profile( $playerId );

        if ( is_array( $testResultSecond ) && isset( $testResultSecond['profile'] ) ) {

            // To verify highest return must be equal max of prizaAmount of get from database player
            $this->unit->run( $testResultSecond['profile']['highestWin'], $highestWinExpected, 'To verify get Profile return is valid','To verify highest return must be equal max of prizaAmount of get from database player' );

            // To verify totalAggregateWin must be sum prizaAmount from database by player
            $this->unit->run( $testResultSecond['profile']['totalAggregateWin'], $totalAddgrgateWinExpected, 'To verify get Profile return is valid','To verify totalAggregateWin must be sum prizaAmount from database by player' );

            // To verify currentPostion return must be equal endPosition get from database by player
            $this->unit->run( $testResultSecond['profile']['currentPosition'], $currentPositionExpected, 'To verify get Profile return is valid','To verify currentPostion return must be equal endPosition get from database by player' );

            // To verify hightestPosition return is valid
            $this->unit->run( $testResultSecond['profile']['highestPosition'], $hightestPositionExpected, 'To verify get Profile return is valid','To verify hightestPosition return is valid' );

            // To verify favoriteGame return is valid
            $this->unit->run( $testResultSecond['profile']['favoriteGame'], $favoriteGameExpected, 'To verify get Profile return is valid','To verify favoriteGame return is valid' );

            // To verify friends list return is valid
            $testFrients = $testResultSecond['profile']['friendList'];
            if ( !empty($testFrients ) ) {

                foreach ($testFrients as $key => $value) {

                    if ( array_key_exists($key, $friendListExpected) ){

                        $this->unit->run( $testFrients["$key"], $friendListExpected["$key"] , 'To verify get Profile return is valid','To verify friends list return is valid' );
                    }

                }
            } 
            else {
                 $this->unit->run( $testFrients, 'is_null' , 'To verify get Profile return is valid','To verify friends list return is valid' );
            }

        }
        
        echo $this->unit->report();
        echo $this->returnResult( $this->unit->result() );
    }
    /**
     * returnResult 
     * @param  array $results 
     * @return string
     */
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



