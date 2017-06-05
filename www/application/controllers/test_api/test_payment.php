<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
/**
 * Testing payment 
 * 
 */
class test_payment extends CI_Controller {
  
    function __construct() {

        parent::__construct();

        // loading model payment
        $this->load->model( 'payment' );

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

    function testPaymentInfo() {

        // To verify get info payment is invalid
        // ========================================
        // To verify player is invalid
        $player = $this->player->limit(1)->order_by( 'id', 'DESC' )->get_all();
        $playerIdExit = !empty( $player ) ? $player[0]->id : 0 ;
        $playerIdNotExit = ($playerIdExit + 1); 
        $playIdInvalid = array(null, 'abc', 0, $playerIdNotExit, -1 );

        // To verify player is invalid
        foreach ( $playIdInvalid as $key => $value ) {
            
            $testResultFirst = $this->payment->payInfo( $value );

            if ( is_array( $testResultFirst ) && isset( $testResultFirst['errors'] ) ) {

                // To verify player Id input is invalid
                $this->unit->run( $testResultFirst['errors'], 'Id must is a numeric and greater than zero', 'To verify get info payment return is invalid', 'To verify player Id input is invalid' );
                
            } elseif ( is_array( $testResultFirst ) && isset( $testResultFirst['error'] )) {

                //To verify player is not exist
                $this->unit->run( $testResultFirst['error'] , 'Not authorized', "To verify get info payment return is invalid", "To verify player is invalid" );
            }
        }
        // To verify get info payment is valid
        // To verify update player return is valid
        $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);

        $login = $this->player->login($dataLogin);
        $this->player->setToken( $login['token'] );
        
        $testResultSecond = $this->payment->payInfo($login['playerId']);
        if ( is_array( $testResultSecond ) && ( isset( $testResultSecond['message'] ) && $testResultSecond['message'] === 'OK' ) ) {

            // To verify email return must be equal email when user login 
            $this->unit->run( $testResultSecond['email'], $dataLogin['email'], 'To verify get info payment is valid', 'To verify email return must be equal email when user login');

            // To verify paypal return must be equal paypal when user login
            $this->unit->run( $testResultSecond['payPal'], $dataLogin['email'], 'To verify get info payment is valid', 'To verify email return must be equal email when user login'); 
        }

        echo $this->unit->report();
        echo $this->returnResult( $this->unit->result() );
    }

    function testUpdatePayment() {
        $data['paypalEmail'] = "admin2@kizzang.com";

        // To verify update info payment is invalid
        // To verify player is invalid
        $player = $this->player->limit(1)->order_by( 'id', 'DESC' )->get_all();
        $playerIdExit = !empty( $player ) ? $player[0]->id : 0 ;
        $playerIdNotExit = ($playerIdExit + 1); 
        $playIdInvalid = array(null, 'abc', 0, $playerIdNotExit, -1 );

        // To verify player is invalid
        foreach ( $playIdInvalid as $key => $value ) {
            
            $testResultFirst = $this->payment->payUpdate( $value , $data);

            if ( is_array( $testResultFirst ) && isset( $testResultFirst['errors'] ) ) {

                // To verify player Id input is invalid
                $this->unit->run( $testResultFirst['errors'], 'Id must is a numeric and greater than zero', 'To verify get info payment return is invalid', 'To verify player Id input is invalid' );
                
            } elseif ( is_array( $testResultFirst ) && isset( $testResultFirst['error'] )) {

                //To verify player is not exist
                $this->unit->run( $testResultFirst['error'] , 'Not authorized', "To verify get info payment return is invalid", "To verify player is invalid" );
            }
        }
         // To verify update player return is valid
        $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);

        $login = $this->player->login($dataLogin);
        $this->player->setToken( $login['token'] );

        // To verify paypalEmail is invalid
        $paypalEmailInvalid = array('abc', null, 1, 0, -1);
        
        foreach ($paypalEmailInvalid as $key => $value) {
            $dataInvalid['paypalEmail'] = $value;
            $testResultSecond = $this->payment->payUpdate( $login['playerId'], $dataInvalid );
            if ( is_array( $testResultSecond ) && isset( $testResultSecond['errors'] ) ) {

                if ( empty( $value ) ) {

                    $this->unit->run( $testResultSecond['errors'][0], 'The paypalEmail field is required.', "To verify paypalEmail update invalid", "To verify paypalEmail is invalid" );
                } else {

                    $this->unit->run( $testResultSecond['errors'][0], 'The paypalEmail field must contain a valid email address.', "To verify paypalEmail update invalid", "To verify paypalEmail is invalid" );
                }
            }
        }

        // To verify update info payment is valid
        $testResultThird = $this->payment->payUpdate( $login['playerId'], $data );
        
        if ( is_array($testResultThird) && isset ($testResultThird['message']) && $testResultThird['message'] === 'OK' ) {

            // To verify email return must be equal emal login 
            $this->unit->run($testResultThird['accountData']['email'], $dataLogin['email'], "To verify update info payment is valid", "To verify email return must be equal emal login");
            
            // To verify paypalEmal return must be equal paypalEmail input 
            $this->unit->run($testResultThird['payPalEmail'], $data['paypalEmail'], "To verify update info payment is valid", "To verify paypalEmal return must be equal paypalEmail input ");
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