<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
/**
 * Testing facebookintives 
 * 
 */
class test_facebookintives extends CI_Controller {
  
    function __construct() {

        parent::__construct();

        // loading model map
        $this->load->model( 'facebookinvite' );

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

    function testAddFacebookInvite() {

        $data = array(
            'friendFacebookId' => 'abc000001'.substr(str_shuffle(md5(time())), 0, 100),
            'responseId' => 'eeee00001'
            );

        // To verify add facebook invite is invalid
        // =========================================
        
        // To verify id player is invalid
        $player = $this->player->limit(1)->order_by( 'id', 'DESC' )->get_all();
        $playerIdExit = !empty( $player ) ? $player[0]->id : 0 ;
        $playerIdNotExit = ($playerIdExit + 1); 
        $playIdInvalid = array(null, 'abc', 0, $playerIdNotExit, -1 );

        // To verify player is invalid
        foreach ( $playIdInvalid as $key => $value ) {
            
            $testResultFirst = $this->facebookinvite->add( $value , $data);
            if ( is_array( $testResultFirst ) && isset( $testResultFirst['errors'] ) ) {

                // To verify player Id input is invalid
                $this->unit->run( $testResultFirst['errors'], 'Id must be a numeric and greater than zero', 'To verify add facebookinvite return is invalid', 'To verify player Id input is invalid' );
                
            } elseif ( is_array( $testResultFirst ) && isset( $testResultFirst['error'] )) {

                //To verify player is not exist
                $this->unit->run( $testResultFirst['error'] , 'Not authorized', "To verify add facebookinvite return is invalid", "To verify player is invalid" );
            }
        }
        
        // To verify update player return is valid
        $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);

        $login = $this->player->login($dataLogin);
        if ( isset($login['token'])) {

            $this->player->setToken( $login['token'] );
            $playerId = $login['playerId'];

            // To verify friendFacebookId is invalid
        
            $invalid = array(null, str_repeat('a1', 65));
            foreach ($invalid as $key => $value) {
                $dataFacebookInvalid = $data;
                $dataFacebookInvalid['friendFacebookId'] = $value;
        
                // To verify friendFacebookId is invalid
                $testResultSecond = $this->facebookinvite->add( $playerId, $dataFacebookInvalid );

                if  ( is_array( $testResultSecond ) && isset( $testResultSecond['errors'] ) ) {

                    if ( is_null( $value ) ) {

                        // To verify friendFacebookId is null
                        $this->unit->run( $testResultSecond['errors'][0], 'The friendFacebookId field is required.', 'To verify add facebookinvite return is invalid', 'To verify friendFacebookId is null'  );

                    } else {

                        // To verify friendFacebookId is invalid
                        $this->unit->run( $testResultSecond['errors'][0], 'The friendFacebookId field can not exceed 45 characters in length.', 'To verify add facebookinvite return is invalid', 'To verify friendFacebookId is invalid'  );
                    }
                }
            }

            // To verify responseId is invalid
            foreach ($invalid as $key => $value) {
                $dataFacebookInvalid = $data;
                $dataFacebookInvalid['responseId'] = $value;
        
                // To verify responseId is invalid
                $testResultThird = $this->facebookinvite->add( $playerId, $dataFacebookInvalid );

                if  ( is_array( $testResultThird ) && isset( $testResultThird['errors'] ) ) {

                    if ( is_null( $value ) ) {

                        // To verify responseId is null
                        $this->unit->run( $testResultThird['errors'][0], 'The responseId field is required.', 'To verify add facebookinvite return is invalid', 'To verify responseId is null'  );

                    } else {

                        // To verify responseId is invalid
                        $this->unit->run( $testResultThird['errors'][0], 'The responseId field can not exceed 128 characters in length.', 'To verify add facebookinvite return is invalid', 'To verify responseId is invalid'  );
                    }
                }
            }

            // To verify add facebook invite id valid
            // =========================================
            $testResultFourth = $this->facebookinvite->add( $playerId, $data);
            
            if ( is_object( $testResultFourth ) ) {

                // To verify playerId return must be equal playerId input
                $this->unit->run( $testResultFourth->playerId, $playerId, 'To verify add facebook invite id valid', 'To verify playerId return must be equal playerId input');
                
                // To verify friendFacebookId return must be equal friendFacebookId input
                 $this->unit->run( $testResultFourth->friendFacebookId, $data['friendFacebookId'] , 'To verify add facebook invite id valid', 'To verify friendFacebookId return must be equal friendFacebookId input');

                // To verify responseId return must be equal responseId input
                $this->unit->run( $testResultFourth->responseId, $data['responseId'] , 'To verify add facebook invite id valid', 'To verify responseId return must be equal responseId input');

            } else {

                echo "<h4 style='color: red;'>Can't verify add facebookinvite is valid. Pls try test again.</h4>" ;
            }

        } else {
            
            echo "<h4 style='color: red;'>Can't verify add facebookinvite is valid. Pls try test again.</h4>" ;
        }
        
        echo $this->unit->report();
        echo $this->returnResult( $this->unit->result() );
    }
    
    function testGetFriendList() {

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