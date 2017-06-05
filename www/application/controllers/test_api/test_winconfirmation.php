<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
/**
 * Testing winconfirmation 
 * 
 */
class test_winconfirmation extends CI_Controller {
  
    function __construct() {

        parent::__construct();

        // loading model winconfirmation
        $this->load->model( 'winconfirmation' );

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

    function testGetPendingWiners() {
        $count = $this->winconfirmation->count_by( array( 'status'=>'N', 'playerActionChoice' => 0 ) );

        if ( $count > 0) {
            
            // To verify get winner pending is invalid
            // To verify player is invalid
            $player = $this->player->limit(1)->order_by( 'id', 'DESC' )->get_all();
            $playerIdExit = !empty( $player ) ? $player[0]->id : 0 ;
            $playerIdNotExit = ($playerIdExit + 1); 
            $playIdInvalid = array(null, 'abc', 0, $playerIdNotExit, -1 );

            // To verify player is invalid
            foreach ( $playIdInvalid as $key => $value ) {
                
                $testResultFirst = $this->winconfirmation->getPendingWinners( $value );
                if ( is_array( $testResultFirst ) && isset( $testResultFirst['errors'] ) ) {

                    // To verify player Id input is invalid
                    $this->unit->run( $testResultFirst['errors'], 'Id must be a numeric and greater than zero', 'To verify get winner pending is invalid', 'To verify player Id input is invalid' );
                    
                } elseif ( is_array( $testResultFirst ) && isset( $testResultFirst['error'] )) {

                    //To verify player is not exist
                    $this->unit->run( $testResultFirst['error'] , 'Not authorized', "To verify get winner pending is invalid", "To verify player is invalid" );
                }
            }

            $pendingWinner = $this->winconfirmation->get_by( array('status' => 'N', 'playerActionChoice' => 0 ));
            $playerId = $pendingWinner->playerId;

            // login with admin user
            $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);
            $login = $this->player->login($dataLogin);
            $this->player->setToken( $login['token'] );

            // To verify get winner pending is valid
            $testResultSecond = $this->winconfirmation->getPendingWinners( $playerId );
            
            if ( is_array( $testResultSecond ) && isset( $testResultSecond['winners'] ) ) {

                foreach ($testResultSecond['winners'] as $value) {
                    
                    // To verify playerId return must be equal playerId input
                    $this->unit->run( $value->playerId, $playerId, "To verify get winner pending is valid", "To verify playerId return must be equal playerId input" ); 

                    // To verify status return must N 
                    $this->unit->run( $value->status, "N", "To verify get winner pending is valid", "To verify status return must be equal N" ); 

                    // To verify playerActionChoice return must be is 0 
                    $this->unit->run( (int)$value->playerActionChoice, 0, "To verify get winner pending is valid", "To verify playerActionChoice return must be equal 0" );
                }
                
            }

        } 
        else {
               
           echo "<h4 style='color: red;'>Can't verify get winner pending with the player.</h4>" ;
            
        }

        echo $this->unit->report();
        echo $this->returnResult( $this->unit->result() );
    }

    function testGetConfirmedWiners() {

        $count = $this->winconfirmation->count_by( array( 'status'=>'N', 'playerActionChoice' => 0 ) );


        if ( $count > 0) {
            
            // To verify get winner confirmed is invalid
            // =========================================
            
            // To verify player is invalid
            $player = $this->player->limit(1)->order_by( 'id', 'DESC' )->get_all();
            $playerIdExit = !empty( $player ) ? $player[0]->id : 0 ;
            $playerIdNotExit = ($playerIdExit + 1); 
            $playIdInvalid = array(null, 'abc', 0, $playerIdNotExit, -1 );

            // To verify player is invalid
            foreach ( $playIdInvalid as $key => $value ) {
                
                $testResultFirst = $this->winconfirmation->getConfirmedWinners( $playerIdExit);

                if ( is_array( $testResultFirst ) && isset( $testResultFirst['errors'] ) ) {

                    // To verify player Id input is invalid
                    $this->unit->run( $testResultFirst['errors'], 'Id must be a numeric and greater than zero', 'To verify get winner confirmed is invalid', 'To verify player Id input is invalid' );
                    
                } elseif ( is_array( $testResultFirst ) && isset( $testResultFirst['error'] )) {

                    //To verify player is not exist
                    $this->unit->run( $testResultFirst['error'] , 'Not authorized', "To verify get winner confirmed is invalid", "To verify player is invalid" );
                }
            }

            // To verify get winner confirm is valid
            // =====================================
            $confirmedWinner = $this->winconfirmation->get_by( array('status' => 'P', 'playerActionChoice' => 0 ));
            $playerId = $confirmedWinner->playerId;

            // login with admin user
            $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);
            $login = $this->player->login($dataLogin);
            $this->player->setToken( $login['token'] );

            // To verify get winner confirm is valid
            $testResultSecond = $this->winconfirmation->getConfirmedWinners( $playerId );
            
            if ( is_array( $testResultSecond ) && isset( $testResultSecond['winners'] ) ) {

                foreach ($testResultSecond['winners'] as $value) {
                    
                    // To verify playerId return must be equal playerId input
                    $this->unit->run( $value->playerId, $playerId, "To verify get winner confirmed is valid", "To verify playerId return must be equal playerId input" ); 

                    // To verify status return must P 
                    $this->unit->run( $value->status, "P", "To verify get winner confirmed is valid", "To verify status return must be equal P" ); 

                    // To verify playerActionChoice return must be is 0 
                    $this->unit->run( (int)$value->playerActionChoice, 0, "To verify get winner confirmed is valid", "To verify playerActionChoice return must be equal 0" );
                }
                
            }
        } 
        else {
               
           echo "<h4 style='color: red;'>Can't verify get winner confirmed with the player.</h4>" ;
            
        }

        echo $this->unit->report();
        echo $this->returnResult( $this->unit->result() );
    }

    function testEditWiners() {
        
        $dataUpdate = array(
            'status' => 'N',
            'prizeAmount'=> '13.00',
            'serialNumber' => '1111111111111'
            );

        $count = $this->winconfirmation->count_all();

        if ( $count > 0 ) {

            // To verify edit winers is invalid
            // =================================
            $player = $this->player->limit(1)->order_by( 'id', 'DESC' )->get_all();
            $playerIdExit = !empty( $player ) ? $player[0]->id : 0 ;
            $playerIdNotExit = ($playerIdExit + 1); 
            $playIdInvalid = array(null, 'abc', 0, $playerIdNotExit, -1 );

            // To verify player is invalid
            foreach ( $playIdInvalid as $key => $value ) {
                
                $testResultFirst = $this->winconfirmation->edit( $value, $id = 1, $dataUpdate);

                if ( is_array( $testResultFirst ) && isset( $testResultFirst['errors'] ) ) {

                    // To verify player Id input is invalid
                    $this->unit->run( $testResultFirst['errors'], 'Id must be a numeric and greater than zero', 'To verify  update winner is invalid', 'To verify player Id input is invalid' );
                    
                } elseif ( is_array( $testResultFirst ) && isset( $testResultFirst['error'] )) {

                    //To verify player is not exist
                    $this->unit->run( $testResultFirst['error'] , 'Not authorized', "To verify  update winner is invalid", "To verify player is invalid" );
                }
            }

            // To verify data input is invalid
            // login with admin user
            $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);
            $login = $this->player->login($dataLogin);
            $this->player->setToken( $login['token'] );

            $dataEmpty = array();
            $testResultSecond = $this->winconfirmation->edit( $playerIdExit, $id = 1, $dataEmpty );
            if ( is_array( $testResultSecond ) && isset( $testResultSecond['errors'] ) ) {

                    $this->unit->run( $testResultSecond['errors'], "Please the required enter data", "To verify  update winner is invalid", "To verify id is invalid" );
                }
            // To verify id input is invalid
            $IdInvalid = array(null, 'abc', 0, -1);

            foreach ($IdInvalid as $key => $value) {
                
                $testResultThird = $this->winconfirmation->edit( $playerIdExit, $value, $dataUpdate );
                if ( is_array( $testResultThird ) && isset( $testResultThird['errors'] ) ) {

                    $this->unit->run( $testResultThird['errors'], "Id must is a numeric and greater than zero", "To verify  update winner is invalid", "To verify id is invalid" );
                }
            }

            // get win confirmation
            $winner = $this->winconfirmation->limit(1)->order_by('id', 'DESC')->get_all();
            $playerId = $winner[0]->playerId;
            $id = $winner[0]->id;

            // To verify status is invalid
            $statusInvalid = array( null, 'ab', 12 );
            
            foreach ( $statusInvalid as $value ) {

                $status['status'] = $value;
                $testStatusInvalid = $this->winconfirmation->edit( $playerId, $id, $status);
                if ( is_array( $testStatusInvalid ) && isset( $testStatusInvalid['errors'] )) {

                    if ( is_null( $value ) ) {

                        // To verify status is empty
                        $this->unit->run( $testStatusInvalid['errors'][0], 'The status field is required.', 'To verify status is empty', 'To verify update winconfirmation is invalid' );
                    } else  {

                        // To verify status is invalid
                        $this->unit->run( $testStatusInvalid['errors'][0], 'The status field can not exceed 1 characters in length.', 'To verify status is invalid', 'To verify update winconfirmation is invalid' );
                    }
                }     
            }  

            // To verify name is empty
            $nameInvalid['name'] = '';
            $testnameInvalid = $this->winconfirmation->edit( $playerId, $id, $nameInvalid);
            if ( is_array( $testnameInvalid ) && isset( $testnameInvalid['errors'] )) {

                    // To verify name is empty
                    $this->unit->run( $testnameInvalid['errors'][0], 'The name field is required.', 'To verify name is empty', 'To verify update winconfirmation is invalid' );
            }

            // To verify entry is invalid
            $entryInvalid = array( null, 'abc', -1, 0 );
            foreach ( $entryInvalid as $value ) {

                $entry['entry'] = $value;
                $testentryInvalid = $this->winconfirmation->edit( $playerId, $id, $entry );
                if ( is_array( $testentryInvalid ) && isset( $testentryInvalid['errors'] )) {

                    if ( empty( $value ) ) {

                        // To verify entry is empty
                        $this->unit->run( $testentryInvalid['errors'][0], 'The entry field is required.', 'To verify entry is empty', 'To verify update winconfirmation is invalid' );
                    } else  {

                        // To verify entry is invalid
                        $this->unit->run( $testentryInvalid['errors'][0], 'The entry field must contain a number greater than 0.', 'To verify entry is invalid', 'To verify update winconfirmation is invalid' );
                    }
                }     
            }
            // To verify serialNumber is invalid
            $serialInvalid = array( null, str_repeat("a", 65 ) );
            foreach ( $serialInvalid as $value ) {
                $serialNumber['serialNumber'] = $value;
                $testserialNumberInvalid = $this->winconfirmation->edit( $playerId, $id, $serialNumber );
                if ( is_array( $testserialNumberInvalid ) && isset( $testserialNumberInvalid['errors'] )) {

                    if ( is_null( $value ) ) {

                        // To verify serialNumber is empty
                        $this->unit->run( $testserialNumberInvalid['errors'][0], 'The serialNumber field is required.', 'To verify serialNumber is empty', 'To verify update winconfirmation is invalid' );
                    } else  {

                        // To verify serialNumber is invalid
                        $this->unit->run( $testserialNumberInvalid['errors'][0], 'The serialNumber field can not exceed 64 characters in length.', 'To verify serialNumber is invalid', 'To verify update winconfirmation is invalid' );
                    }
                }     
            }

            // To verify prizeAmount is invalid
            $prizeInvalid = array( null, 'abc', 2 );

            foreach ( $prizeInvalid as $value ) {
                $prizeAmount['prizeAmount'] = $value;
                $testprizeAmountInvalid = $this->winconfirmation->edit( $playerId, $id, $prizeAmount );
                if ( is_array( $testprizeAmountInvalid ) && isset( $testprizeAmountInvalid['errors'] )) {

                    if ( is_null( $value ) ) {

                        // To verify prizeAmount is empty
                        $this->unit->run( $testprizeAmountInvalid['errors'][0], 'The prizeAmount field is required.', 'To verify prizeAmount is empty', 'To verify update winconfirmation is invalid' );
                    } else  {

                        // To verify prizeAmount is invalid
                        $this->unit->run( $testprizeAmountInvalid['errors'][0], 'The prizeAmount field is not in the correct format.', 'To verify prizeAmount is invalid', 'To verify update winconfirmation is invalid' );
                    }
                }     
            }
            
            // To verify taxableAmount is invalid
            foreach ( $prizeInvalid as $value ) {
                $taxableAmount['taxableAmount'] = $value;
                $testtaxableAmountInvalid = $this->winconfirmation->edit( $playerId, $id, $taxableAmount );
                if ( is_array( $testtaxableAmountInvalid ) && isset( $testtaxableAmountInvalid['errors'] )) {

                    if ( is_null( $value ) ) {

                        // To verify taxableAmount is empty
                        $this->unit->run( $testtaxableAmountInvalid['errors'][0], 'The taxableAmount field is required.', 'To verify taxableAmount is empty', 'To verify update winconfirmation is invalid' );
                    } else  {

                        // To verify taxableAmount is invalid
                        $this->unit->run( $testtaxableAmountInvalid['errors'][0], 'The taxableAmount field is not in the correct format.', 'To verify taxableAmount is invalid', 'To verify update winconfirmation is invalid' );
                    }
                }     
            }
            // To verify cumulativeAnnualPaid is invalid
            foreach ( $prizeInvalid as $value ) {

                $cumulativeAnnualPaid['cumulativeAnnualPaid'] = $value;
                $testcumulativeAnnualPaidInvalid = $this->winconfirmation->edit( $playerId, $id, $cumulativeAnnualPaid );
                if ( is_array( $testcumulativeAnnualPaidInvalid ) && isset( $testcumulativeAnnualPaidInvalid['errors'] )) {

                    if ( is_null( $value ) ) {

                        // To verify cumulativeAnnualPaid is empty
                        $this->unit->run( $testcumulativeAnnualPaidInvalid['errors'][0], 'The cumulativeAnnualPaid field is required.', 'To verify cumulativeAnnualPaid is empty', 'To verify update winconfirmation is invalid' );
                    } else  {

                        // To verify cumulativeAnnualPaid is invalid
                        $this->unit->run( $testcumulativeAnnualPaidInvalid['errors'][0], 'The cumulativeAnnualPaid field is not in the correct format.', 'To verify cumulativeAnnualPaid is invalid', 'To verify update winconfirmation is invalid' );
                    }
                }     
            }
            // To verify payPalCorrelationId is invalid

            $payPalCorrelationId['payPalCorrelationId'] =  str_repeat("a", 17 );
            $testpayPalCorrelationIdInvalid = $this->winconfirmation->edit( $playerId, $id, $payPalCorrelationId );
            if ( is_array( $testpayPalCorrelationIdInvalid ) && isset( $testpayPalCorrelationIdInvalid['errors'] )) {

                // To verify payPalCorrelationId is invalid
                $this->unit->run( $testpayPalCorrelationIdInvalid['errors'][0], 'The payPalCorrelationId field can not exceed 16 characters in length.', 'To verify payPalCorrelationId is invalid', 'To verify update winconfirmation is invalid' );
            }

            // To verify playerActionChoice is invalid
            $playerActionChoice = array( null, 'abc', -1 );

            foreach ( $playerActionChoice as $value ) {

                $playerActionChoice['playerActionChoice'] = $value;
                $testplayerActionChoiceInvalid = $this->winconfirmation->edit( $playerId, $id, $playerActionChoice );
                if ( is_array( $testplayerActionChoiceInvalid ) && isset( $testplayerActionChoiceInvalid['errors'] )) {

                    if ( empty( $value ) ) {

                        // To verify playerActionChoice is empty
                        $this->unit->run( $testplayerActionChoiceInvalid['errors'][0], 'The playerActionChoice field is required.', 'To verify playerActionChoice is empty', 'To verify update winconfirmation is invalid' );
                    } else  {

                        // To verify playerActionChoice is invalid
                        $this->unit->run( $testplayerActionChoiceInvalid['errors'][0], 'The playerActionChoice field must contain a number greater than -1.', 'To verify playerActionChoice is invalid', 'To verify update winconfirmation is invalid' );
                    }
                }     
            }
            
            // To verify edit winers is valid
            // ================================= 
            
            $testResultFourth = $this->winconfirmation->edit( $playerId, $id, $dataUpdate );

            if ( is_object($testResultFourth) ) {
                // To verify playerId return must be is equal playerId input
                $this->unit->run( $testResultFourth->playerId, $playerId, 'To verify edit winers is valid', 'To verify playerId return must be is equal playerId input');
                
                // To verify data update return must be equal data input
                $this->unit->run( $testResultFourth->serialNumber, $dataUpdate['serialNumber'], 'To verify edit winers is valid', 'To verify data update return must be equal data input');
                $this->unit->run( $testResultFourth->status, $dataUpdate['status'], 'To verify edit winers is valid', 'To verify data update return must be equal data input');

                $this->unit->run( $testResultFourth->prizeAmount, $dataUpdate['prizeAmount'], 'To verify edit winers is valid', 'To verify data update return must be equal data input');
            } 

        } else {

           echo "<h4 style='color: red;'>Can't verify update winner.Database empty!</h4>" ; 
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