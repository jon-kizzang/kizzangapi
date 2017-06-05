<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
/**
 * Testing parlays 
 * 
 */
class test_parlaygames extends CI_Controller {
  
    function __construct() {

        parent::__construct();

        // loading model parlays
        $this->load->model('parlaycategory');

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
    //==============================
    // Test parlay categories    //
    //============================
    function testCategoryAdd() {

        $data = array(
            'name' => 'Big Game 21' . md5(date('Y-m-d H:i:s').rand(1,100)),
        );

        // To verify add Parlay Category is invalid
        //========================================= 
        // To verify data is empty
        $dataInvalid = '';
        $testResultFirst = $this->parlaycategory->add( $dataInvalid );
        if (is_array($testResultFirst) && isset($testResultFirst['message']) ) {

            $this->unit->run( $testResultFirst['message'], 'Please enter the required data', 'To verify add Category is invalid', 'To verify data is empty');
        }

        // To verify data is empty
        $dataInvalid['name'] = '';
        $testResultSecond = $this->parlaycategory->add( $dataInvalid );

        if (is_array($testResultSecond) && isset($testResultSecond['message']) ) {

            $this->unit->run( $testResultSecond['message'][0], 'The name field is required.', 'To verify add Category is invalid', 'To verify data is empty');
        }

        // To verify add category is exist
        $categories = $this->parlaycategory->get_by(array('id !=' => 0));

        if ( !empty( $categories ) ) {

            $dataInvalid['name'] = $categories->name;
            $testResultThird = $this->parlaycategory->add( $dataInvalid ); 

            if (is_array($testResultThird) && isset($testResultThird['message']) ) {

                $this->unit->run( $testResultThird['message'], 'Cannot save a duplicate Parlay Category with name - ' . $dataInvalid['name'] , 'To verify add Parlay Category is invalid', 'To verify add category is exist');
            }
        }

        // To verify add Parlay Category is valid
        //======================================= 
        $nameExpected = $data['name'];
        $testResultFourth = $this->parlaycategory->add( $data );

        if ( is_object($testResultFourth) ) {

            // To verify name returm must be equal name Parlay Category input
            $this->unit->run($testResultFourth->name, $nameExpected, 'To verify add Parlay Category is valid', 'To verify name returm must be equal name Parlay Category input');

        } else {

            echo "<h4 style='color:red;'> Can't verify add category parlay is case valid</h4>";
        }

        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());    
    }

    function testCategoryUpdate() {

        $category = $this->parlaycategory->order_by('id', 'DESC')->get_by(array('id !='=> 0));

        if( !empty($category) ) {
            $id = $category->id;
            $data['name'] = $category->name . "Update";
            // To verify update Parlay Category is invalid
            //=========================================
            // To verify data is empty
            $dataEmpty = '';

            $testResultFirst = $this->parlaycategory->edit( $id , $dataEmpty );

            if ( is_array( $testResultFirst ) && isset( $testResultFirst['message'] ) ) {

                // To verify data is empty
                $this->unit->run( $testResultFirst['message'], 'Please enter the required data', 'To verify update category result is invalid', 'To verify data is empty' );
            }

            // To verify id is invalid
            $idInvalid = array('', NULL, 'abc', 0, -1);
            foreach ($idInvalid as $key => $value) {
                $testResultSecond = $this->parlaycategory->edit( $value, $data );

                if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

                    if ( !empty( $value ) ) {

                        $this->unit->run( $testResultSecond['message'][0], 'The Id field must contain a number greater than 0.', 'To verify update parlay category is invalid', 'To verify id is invalid' );
                    } else {
                        $this->unit->run( $testResultSecond['message'][0], 'The final Match Id field is required.', 'To verify update parlay category is invalid', 'To verify id is invalid' );
                    }
                }  
            } 
            // To verify update Parlay Category is valid
            //========================================= 
            $testResultThird = $this->parlaycategory->edit( $id, $data );

            if ( is_object($testResultThird) ) {

                // To verify name returm must be equal name Parlay Category input
                $this->unit->run( $testResultThird->name, $data['name'] , 'To verify add Parlay Category is valid', 'To verify name returm must be equal name Parlay Category input');

            } else {

                echo "<h4 style='color:red;'> Can't verify update category parlay is case valid</h4>";
            }

        } else {

            echo "<h4 style='color:red;'> Can't verify update category parlay is case valid. Please testing add Parlay Category before testing update.</h4>";
        }
    }

    function testCategoryGetAllByDate() {

        // To verify get all by Date Parlay Category is invalid
        //========================================= 
        
        // To verify get all by Date Parlay Category is valid
        //========================================= 
    }

    function testCategoryGetAllByCategory() {

        // To verify get all by Category Parlay Category is invalid
        //========================================= 
        
        // To verify get all by Category Parlay Category is valid
        //========================================= 
    }

    function testCategoryDelete() {

        // To verify delete Parlay Category is invalid
        //========================================= 
        
        // To verify delete Parlay Category is valid
        //========================================= 
    }

    
    //=======================
    // Test parlay teams   //
    //======================
    function testTeamAdd() {
        $data = array(
            'sportCategoryID' => 1,
            'team1'           => 2,
            'team2'           => 3,
            'dateTime'        => '11-20-2014 00:00:00'
        );

        $this->load->model('parlayteam');
        // To verify add Parlay Team is invalid
        //========================================= 
        // To verify data is empty
        $dataInvalid = '';
        $testResultFirst = $this->parlayteam->add( $dataInvalid );

        if (is_array($testResultFirst) && isset($testResultFirst['message']) ) {

            $this->unit->run( $testResultFirst['message'], 'Please the required enter data', 'To verify add Team is invalid', 'To verify data is empty');
        }

        // To verify Team catagoryId is invalid
        $categoryIdInvalid = array('', null, 0, -1);
        foreach ($categoryIdInvalid as $value) {
            $category                    = $data;
            $category['sportCategoryID'] = $value;
            $testResultSecond            = $this->parlayteam->add( $category );
            if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

                if ( !empty( $value ) ) {

                    $this->unit->run( $testResultSecond['message'][0], 'The sport Category ID field must contain a number greater than 0.', 'To verify add Team is invalid', 'To verify catagoryId is invalid' );
                } else {
                    
                    $this->unit->run( $testResultSecond['message'][0], 'The sport Category ID field is required.', 'To verify add Team is invalid', 'To verify catagoryId is invalid' );
                }
            }   
        }

        // To verify Team team1 is invalid
        $team1Invalid = array('', null, 0, -1);
        foreach ($team1Invalid as $value) {
            $team1            = $data;
            $team1['team1']   = $value;
            $testResultThird  = $this->parlayteam->add( $team1 );
            if( is_array($testResultThird) && isset($testResultThird['message'])) {

                if ( !empty( $value ) ) {
                    $this->unit->run( $testResultThird['message'][0], 'The team1 field must contain a number greater than 0.', 'To verify add Team is invalid', 'To verify team1 is invalid' );
                } else {
                    $this->unit->run( $testResultThird['message'][0], 'The team1 field is required.', 'To verify add Team is invalid', 'To verify team1 is invalid' );
                }
            }   
        }

        // To verify Teame team2 is invalid
        $team1Invalid = array('', null, 0, -1);
        foreach ($team1Invalid as $value) {
            $team2            = $data;
            $team2['team2']   = $value;
            $testResultFourth = $this->parlayteam->add( $team2 );
            if( is_array($testResultFourth) && isset($testResultFourth['message'])) {

                if ( !empty( $value ) ) {
                    $this->unit->run( $testResultFourth['message'][0], 'The team2 field must contain a number greater than 0.', 'To verify add Team is invalid', 'To verify team2 is invalid' );
                } else {
                    $this->unit->run( $testResultFourth['message'][0], 'The team2 field is required.', 'To verify add Team is invalid', 'To verify team2 is invalid' );
                }
            }   
        }

        // Toverify CategoryId , team1, team2 is not exist


        // To verify add Parlay Team is valid
        //========================================= 
        

        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());    
    }

    function testTeamUpdate() {

        // To verify update Parlay Team is invalid
        //========================================= 
        
        // To verify update Parlay Team is valid
        //========================================= 
    }

    function testTeamGetAllByDate() {

        // To verify get all by Date Parlay Team is invalid
        //========================================= 
        
        // To verify get all by Date Parlay Team is valid
        //========================================= 
    }

    function testTeamGetAllByCategory() {

        // To verify get all by Category Parlay Team is invalid
        //========================================= 
        
        // To verify get all by Category Parlay Team is valid
        //========================================= 
    }

    function testTeamDelete() {

        // To verify delete Parlay Team is invalid
        //========================================= 
        
        // To verify delete Parlay Team is valid
        //========================================= 
    }

    //========================
    // Test parlay schedule //
    //=======================
    
    function testScheduleAdd() {
        $data = array(
            'sportCategoryID' => 1,
            'team1'           => 2,
            'team2'           => 3,
            'dateTime'        => '11-20-2014 00:00:00'
        );

        $this->load->model('parlayschedule');
        // To verify add Parlay Schedule is invalid
        //========================================= 
        // To verify data is empty
        $dataInvalid = '';
        $testResultFirst = $this->parlayschedule->add( $dataInvalid );

        if (is_array($testResultFirst) && isset($testResultFirst['message']) ) {

            $this->unit->run( $testResultFirst['message'], 'Please the required enter data', 'To verify add schedule is invalid', 'To verify data is empty');
        }

        // To verify schedule catagoryId is invalid
        $categoryIdInvalid = array('', null, 0, -1);
        foreach ($categoryIdInvalid as $value) {
            $category                    = $data;
            $category['sportCategoryID'] = $value;
            $testResultSecond            = $this->parlayschedule->add( $category );
            if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

                if ( !empty( $value ) ) {

                    $this->unit->run( $testResultSecond['message'][0], 'The sport Category ID field must contain a number greater than 0.', 'To verify add schedule is invalid', 'To verify catagoryId is invalid' );
                } else {
                    
                    $this->unit->run( $testResultSecond['message'][0], 'The sport Category ID field is required.', 'To verify add schedule is invalid', 'To verify catagoryId is invalid' );
                }
            }   
        }

        // To verify schedule team1 is invalid
        $team1Invalid = array('', null, 0, -1);
        foreach ($team1Invalid as $value) {
            $team1            = $data;
            $team1['team1']   = $value;
            $testResultThird  = $this->parlayschedule->add( $team1 );
            if( is_array($testResultThird) && isset($testResultThird['message'])) {

                if ( !empty( $value ) ) {
                    $this->unit->run( $testResultThird['message'][0], 'The team1 field must contain a number greater than 0.', 'To verify add schedule is invalid', 'To verify team1 is invalid' );
                } else {
                    $this->unit->run( $testResultThird['message'][0], 'The team1 field is required.', 'To verify add schedule is invalid', 'To verify team1 is invalid' );
                }
            }   
        }

        // To verify schedulee team2 is invalid
        $team1Invalid = array('', null, 0, -1);
        foreach ($team1Invalid as $value) {
            $team2            = $data;
            $team2['team2']   = $value;
            $testResultFourth = $this->parlayschedule->add( $team2 );
            if( is_array($testResultFourth) && isset($testResultFourth['message'])) {

                if ( !empty( $value ) ) {
                    $this->unit->run( $testResultFourth['message'][0], 'The team2 field must contain a number greater than 0.', 'To verify add schedule is invalid', 'To verify team2 is invalid' );
                } else {
                    $this->unit->run( $testResultFourth['message'][0], 'The team2 field is required.', 'To verify add schedule is invalid', 'To verify team2 is invalid' );
                }
            }   
        }

        // Toverify CategoryId , team1, team2 is not exist


        // To verify add Parlay Schedule is valid
        //========================================= 
        

        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());    
    }

    function testScheduleUpdate() {

        // To verify update Parlay Schedule is invalid
        //========================================= 
        
        // To verify update Parlay Schedule is valid
        //========================================= 
    }

    function testScheduleGetAllByDate() {

        // To verify get all by Date Parlay Schedule is invalid
        //========================================= 
        
        // To verify get all by Date Parlay Schedule is valid
        //========================================= 
    }

    function testScheduleGetAllByCategory() {

        // To verify get all by Category Parlay Schedule is invalid
        //========================================= 
        
        // To verify get all by Category Parlay Schedule is valid
        //========================================= 
    }

    function testScheduleDelete() {

        // To verify delete Parlay Schedule is invalid
        //========================================= 
        
        // To verify delete Parlay Schedule is valid
        //========================================= 
    }

    //=======================
    // Test parlay config  // 
    //=======================

    //=======================
    // Test parlay card    //
    //=======================

    //=======================
    // Test parlay results //
    //=======================

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