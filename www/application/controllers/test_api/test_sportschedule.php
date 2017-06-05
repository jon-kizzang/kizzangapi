<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
/**
 * Testing sportschedule 
 * 
 */
class test_sportschedule extends CI_Controller {
  
    function __construct() {

        parent::__construct();

        // loading model sportschedule
        $this->load->model( 'sportschedule' );

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

    function testAddSportSchedule() {
        $data = array(
            'sportCategoryID'   => 2,
            'group'     => 3,
            'dateTime'  => '11-13-2014 00:00:00',
            'team1'     => 1,
            'team2'     => 2
            );
        // To verify add new sport schedule is invalid
        // ===========================================
        
        
        // To verify data is empty
        $dataEmpty = '';
        $testResultFirst = $this->sportschedule->add($dataEmpty);

        if ( is_array($testResultFirst) && isset( $testResultFirst['message'] ) ) {
            
            $this->unit->run($testResultFirst['message'], 'Please the required enter data', 'To verify add new sport schedule is invalid', 'To verify data is empty');
        }

        // To verify sportCategoryID input is invalid
        $dataInvalid = $data;
        $sportCategoryId = array(null, 'abc', -1, 0);

        foreach ($sportCategoryId as $key => $value) {

            $dataInvalid['sportCategoryID'] = $value;
            $testResultSecond = $this->sportschedule->add( $dataInvalid );
            if ( is_array($testResultSecond) && isset( $testResultSecond['message'])) {

                if( empty( $value ) ) {

                    $this->unit->run( $testResultSecond['message'][0], 'The sport Category ID field is required.', 'To verify add new sport schedule is invalid' , 'To verify sportCategoryID input is invalid');

                } else {

                    $this->unit->run( $testResultSecond['message'][0], 'The sport Category ID field must contain a number greater than 0.', 'To verify add new sport schedule is invalid' , 'To verify sportCategoryID input is invalid');
                }
            }
        }
        // To verify group input is invalid
        $dataInvalid = $data;
        $groups = array(null, 'abc', -1, 0);

        foreach ($groups as $key => $value) {

            $dataInvalid['group'] = $value;

            $testResultThirst = $this->sportschedule->add( $dataInvalid );
            if ( is_array($testResultThirst) && isset( $testResultThirst['message'])) {

                if( empty( $value ) ) {

                    $this->unit->run( $testResultThirst['message'][0], 'The Group field is required.', 'To verify add new sport schedule is invalid' , 'To verify group input is invalid');

                } else {
                    
                    $this->unit->run( $testResultThirst['message'][0], 'The Group field must contain a number greater than 0.', 'To verify add new sport schedule is invalid' , 'To verify group input is invalid');
                }
            }
        }

        // To verify team1 invalid
        $dataInvalid = $data;
        $teams = array(null, 'abc', -1, 0);

        foreach ($teams as $key => $value) {

            $dataInvalid['team1'] = $value;

            $testResultFourth = $this->sportschedule->add( $dataInvalid );

            if ( is_array($testResultFourth) && isset( $testResultFourth['message'])) {

                if( empty( $value ) ) {

                    $this->unit->run( $testResultFourth['message'][0], 'The team1 field is required.', 'To verify add new sport schedule is invalid' , 'To verify team1 input is invalid');

                } else {
                    
                    $this->unit->run( $testResultFourth['message'][0], 'The team1 field must contain a number greater than 0.', 'To verify add new sport schedule is invalid' , 'To verify team1 input is invalid');
                }
            }
        }
        // To verify add sport schedule is valid
        $testResultFifth = $this->sportschedule->add( $data ) ;

        if ( is_object( $testResultFifth) ) {

            // To verify sportCategory id return must be equal sportCategory input
            $this->unit->run( (int)$testResultFifth->sportCategoryID, $data['sportCategoryID'], 'To verify add sport schedule is valid', 'To verify sportCategory id return must be equal sportCategory input');
            
            // To verify group id return must be equal group input
            $this->unit->run( (int)$testResultFifth->group, $data['group'], 'To verify add sport schedule is valid', 'To verify Group id return must be equal Group input');

            // To verify team1 id return must be equal team1 input
            $this->unit->run( (int)$testResultFifth->team1, $data['team1'], 'To verify add sport schedule is valid', 'To verify team1 id return must be equal team1 input');            

            // To verify team2 id return must be equal team2 input     
            $this->unit->run( (int)$testResultFifth->team2, $data['team2'], 'To verify add sport schedule is valid', 'To verify team2 id return must be equal team2 input');      
        } else {

            echo "<h4 style='color: red;'> Can't test Add sport schedule. Pls try again.<h4>"; 
        }
    
        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());
    }
    
    function testUpdateSportSchedule() {
        $dataUpdate = array(
            'sportCategoryID'   => 3,
            'group'     => 5,
            'dateTime'  => '11-13-2014 00:00:00',
            'team1'     => 2,
            'team2'     => 3
            );
        $count = $this->sportschedule->get_all();

        if ( $count > 0 ) {

            $sportschedule = $this->sportschedule->limit(1)->get_all();
            $id = $sportschedule[0]->id;
            $IdInvalid = array(null, 'abc', 0, -1 );

            // To verify update sport schedule is invalid
            //============================================
            // To verify id input invalid
            foreach ( $IdInvalid as $key => $value ) {
                
                $testResultFirst = $this->sportschedule->edit( $value, $dataUpdate );
                if ( is_array( $testResultFirst ) && isset( $testResultFirst['error'] ) ) {

                // To verify player Id input is invalid
                $this->unit->run( $testResultFirst['error'], 'Id must is a numeric and greater than zero', 'To verify get winner confirmed is invalid', 'To verify player Id input is invalid' );
                }  
                
            }    

            // To verify data input invalid 
            $dataEmpty = '';
            $testResultSecond = $this->sportschedule->edit( $id , $dataEmpty );

            if ( is_array($testResultSecond) && isset( $testResultSecond['message'] ) ) {
                
                $this->unit->run($testResultSecond['message'], 'Please the required enter data', 'To verify add new sport schedule is invalid', 'To verify data is empty');
            }

            // To verify sportCategoryID input is invalid
            $sportCategoryId = array(null, 'abc', -1, 0);

            foreach ($sportCategoryId as $key => $value) {

                $dataInvalid['sportCategoryID'] = $value;
                $testResultSecond = $this->sportschedule->edit( $id , $dataInvalid );

                if ( is_array($testResultSecond) && isset( $testResultSecond['message'])) {

                    if( empty( $value ) ) {

                        $this->unit->run( $testResultSecond['message'][0], 'The sport Category ID field is required.', 'To verify add new sport schedule is invalid' , 'To verify sportCategoryID input is invalid');

                    } else {

                        $this->unit->run( $testResultSecond['message'][0], 'The sport Category ID field must contain a number greater than 0.', 'To verify add new sport schedule is invalid' , 'To verify sportCategoryID input is invalid');
                    }
                }
            }
            // To verify team2 input is invalid
            $team2 = array(null, 'abc', -1, 0);

            foreach ($team2 as $key => $value) {

                $dataInvalidTeam['team2'] = $value;
                $testResultThirst = $this->sportschedule->edit( $id , $dataInvalidTeam );

                if ( is_array($testResultThirst) && isset( $testResultThirst['message'])) {

                    if( empty( $value ) ) {

                        $this->unit->run( $testResultThirst['message'][0], 'The team2 field is required.', 'To verify update sport schedule is invalid' , 'To verify team2 input is invalid');

                    } else {

                        $this->unit->run( $testResultThirst['message'][0], 'The team2 field must contain a number greater than 0.', 'To verify update sport schedule is invalid' , 'To verify team2 input is invalid');
                    }
                }
            }

            // To verify update sport schedule is valid
            $testResultFourth = $this->sportschedule->edit( $id, $dataUpdate );

            if ( is_object( $testResultFourth ) ){

                // To verify id return must be equal input
                $this->unit->run( $testResultFourth->id, $id , 'To verify add sport schedule is valid', 'To verify sportCategory id return must be equal sportCategory input');

                // To verify sportCategory id return must be equal sportCategory input
                $this->unit->run( (int)$testResultFourth->sportCategoryID, $dataUpdate['sportCategoryID'], 'To verify add sport schedule is valid', 'To verify sportCategory id return must be equal sportCategory input');
                
                // To verify group id return must be equal group input
                $this->unit->run( (int)$testResultFourth->group, $dataUpdate['group'], 'To verify add sport schedule is valid', 'To verify Group id return must be equal Group input');

                // To verify team1 id return must be equal team1 input
                $this->unit->run( (int)$testResultFourth->team1, $dataUpdate['team1'], 'To verify add sport schedule is valid', 'To verify team1 id return must be equal team1 input');            

                // To verify team2 id return must be equal team2 input     
                $this->unit->run( (int)$testResultFourth->team2, $dataUpdate['team2'], 'To verify add sport schedule is valid', 'To verify team2 id return must be equal team2 input'); 
            } else {

            }
 
        } else {

            echo "<h4 style='color: red;'> Can't test update sport schedule. Database is empty.<h4>";   
        }

        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());
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