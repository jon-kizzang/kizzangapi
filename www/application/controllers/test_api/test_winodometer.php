<?php

/**
 * Testing winodometer 
 * 
 */
class test_winodometer extends CI_Controller {
  
    function __construct() {
        parent::__construct();

        // loading model winodometer
        $this->load->model( 'winodometer' );

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

    /**
     * Testing get winodometers state by start row and limit 
     * 
     */
    function testGetwinodometerById() {

        $odometerLast = $this->winodometer->limit(1)->order_by('id', 'DESC')->get_all();

        if ( !empty( $odometerLast ) ) {
            // To verify get winodometer is invalid
            //======================================
            
            // To verify id winodometer is invalid

            $idNotExit = !empty($odometerLast) ? ($odometerLast[0]->id + 1) : 1; 
            $invalidId = array( null, -1, 'abc', $idNotExit );

            foreach ($invalidId as $value) {
                $testInvalidResult = $this->winodometer->getById($value);

                if ( is_array( $testInvalidResult ) && isset( $testInvalidResult['errors'] ) ) {

                    $this->unit->run( $testInvalidResult['errors'], "Win Odometer Not Found", "To verify get winodometer is invalid", "To verify id winodometer is invalid" );
                }
            }
            
            // To verify get win odometer is valid
            // =====================================
            $idTest = $odometerLast[0]->id;
            $testResultFirst = $this->winodometer->getById( $idTest );

            // To verify id return must be equal id input
            $this->unit->run( $testResultFirst->id, $odometerLast[0]->id, "To verify get win odometer is valid", "To verify id return must be equal id input");

            // To verify resetAmount return must be equal resetAmount get from Database
            $this->unit->run( $testResultFirst->resetAmount, $odometerLast[0]->resetAmount, "To verify get win odometer is valid", "To verify resetAmount return must be equal resetAmount get from Database"); 

            // To verify currentAmount return must be equal currentAmount get from Databases
            $this->unit->run( $testResultFirst->currentAmount, $odometerLast[0]->currentAmount, "To verify get win odometer is valid", "To verify currentAmount return must be equal currentAmount get from Database");

        } 
        else {

            echo "<h4 style='color: red;'> Can't test because database is empty. Pls try again.<h4>";
        }

        echo $this->unit->report();
        echo $this->returnResult( $this->unit->result() );
    }

    /**
     * Testing retention day map 
     * 
     */
    function testUpdateWinOdometer() {

        $odometerLast = $this->winodometer->limit(1)->order_by('id', 'DESC')->get_all();

        if ( !empty( $odometerLast ) ) {

            $data['resetAmount'] = 3;
            $idTest = $odometerLast[0]->id;
            // To verify update winodometer is invalid
            //======================================
            
            // To verify id winodometer is invalid
            $idNotExit = !empty($odometerLast) ? ($odometerLast[0]->id + 1) : 1; 
            $invalidId = array( null, -1, 'abc', $idNotExit );

            foreach ($invalidId as $value) {
                $testInvalidResult = $this->winodometer->edit($value, $data);

                if ( is_array( $testInvalidResult ) && isset( $testInvalidResult['errors'] ) ) {

                    $this->unit->run( $testInvalidResult['errors'], "Win Odometer Not Found", "To verify get winodometer is invalid", "To verify id winodometer is invalid" );
                }
            }
            
            // To verify data is invalid
            $dataInvalid = NULL;
            $testResultFirst = $this->winodometer->edit( $idTest, $dataInvalid );

            if ( is_array( $testResultFirst ) && isset( $testResultFirst['errors'] ) ) {

                // To verify data is null
                $this->unit->run($testResultFirst['errors'], "Data can not empty", "To verify update winodometer is invalid", "To verify data is null");
            }

            // To verify resetAmount update is invalid
            $dataInvalid = array(null, 'abc', -1);

            foreach ( $dataInvalid as $value ) {

                $dataResetInvalid['resetAmount'] = $value;
                $testResultSecond = $this->winodometer->edit( $idTest, $dataResetInvalid );
                if ( is_array( $testResultSecond ) && isset( $testResultSecond['errors'] ) ) {

                    if ( is_null( $value ) ) {

                        // To verify data is null
                        $this->unit->run($testResultSecond['errors'][0], "The resetAmount field is required.", "To verify update winodometer is invalid", "To verify data is invalid");
                    } else {

                        // To verify data is null
                        $this->unit->run($testResultSecond['errors'][0], "The resetAmount field must contain a number greater than 0.", "To verify update winodometer is invalid", "To verify data is invalid");
                    }
                }
            }
            foreach ( $dataInvalid as $value ) {

                $dataCurrentInvalid['currentAmount'] = $value;
                $testResultThirst = $this->winodometer->edit( $idTest, $dataCurrentInvalid );

                if ( is_array( $testResultThirst ) && isset( $testResultThirst['errors'] ) ) {
                    if ( is_null( $value ) ) {

                        // To verify data is null
                        $this->unit->run($testResultThirst['errors'][0], "The currentAmount field is required.", "To verify update winodometer is invalid", "To verify data is invalid");
                    } else {

                        // To verify data is null
                        $this->unit->run($testResultThirst['errors'][0], "The currentAmount field must contain a number greater than 0.", "To verify update winodometer is invalid", "To verify data is invalid");
                    }
                }
            }
            // To verify updated win odometer is valid
            // =====================================
            
            $testResultValid = $this->winodometer->edit( $idTest , $data );

            // To verify id return must be equal id input
            $this->unit->run( $testResultValid->id, $odometerLast[0]->id, "To verify get win odometer is valid", "To verify id return must be equal id input");

            // To verify resetAmount return must be equal resetAmount get from Database
            $this->unit->run( (int)$testResultValid->resetAmount,  $data['resetAmount'] , "To verify get win odometer is valid", "To verify resetAmount return must be equal resetAmount get from Database"); 

            // To verify currentAmount return must be equal currentAmount get from Databases
            $this->unit->run( $testResultValid->currentAmount, $odometerLast[0]->currentAmount, "To verify get win odometer is valid", "To verify currentAmount return must be equal currentAmount get from Database");

        } 
        else {

            echo "<h4 style='color: red;'> Can't test because database is empty. Pls try again.<h4>";
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