<?php
/**
 * Testing leaderboard 
 * 
 */
class test_leaderboard extends CI_Controller {
  
    function __construct() {

        parent::__construct();

        // loading model leaderboard
        $this->load->model( 'leaderboard' );

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


    function testAddLeaderboard() {
        // To verify leaderboard create is invalid
        //=========================================

        // To verify number of winner is invalid
        $numberInvalid = array( null, 'abc', -1, 0);

        foreach ($numberInvalid as $key => $value) {
            
            $testResultFirst = $this->leaderboard->add( $value );
            if ( is_array( $testResultFirst ) && isset( $testResultFirst['error'] ) ) {

                // To verify number must be is numberic and greater than zero
                $this->unit->run( $testResultFirst['error'], 'Number Of Winners must is a numeric and greater than zero', 'To verify leaderboard create is invalid', 'To verify number must be is numberic and greater than zero' );
            } 
            
        }
        // To verify leaderboard create is valid
        // ========================================
        $numberOfWinner = 5;

        $testResultSecond = $this->leaderboard->add( $numberOfWinner );
        if ( is_array( $testResultSecond ) && isset( $testResultSecond['leaderBoards'] ) ) {

            // To verify leaderboard create must be equal number of winner input
            $this->unit->run( sizeof($testResultSecond['leaderBoards']), $numberOfWinner, 'To verify leaderboard create is valid', 'To verivy leaderboard create must be equal number of winner input');

            foreach ( $testResultSecond['leaderBoards'] as $key => $value ) {
                // To verify all value return on leaderboard is null
                $this->unit->run( $value->id, ($key + 1), 'To verify leaderboard create is valid', 'To verify Id return is valid' );

                // To verify leaderBoad id null
                $this->unit->run( $value->leaderboardId, 'is_null', 'To verify leaderboard create is valid', 'To verify Id return is valid' );                
                
                // To verify imageURL id null
                $this->unit->run( $value->imageURL, 'is_null' , 'To verify leaderboard create is valid', 'To verify Id return is valid' );
                
                // To verify location id null
                $this->unit->run( $value->location, 'is_null', 'To verify leaderboard create is valid', 'To verify Id return is valid' );
                
                // To verify screenName id null
                $this->unit->run( $value->screenName, 'is_null', 'To verify leaderboard create is valid', 'To verify Id return is valid' );
                
                // To verify prize id null
                $this->unit->run( $value->prize, 'is_null', 'To verify leaderboard create is valid', 'To verify Id return is valid' );
                
            }
            
        }

        echo $this->unit->report();
        echo $this->returnResult( $this->unit->result() );    
    }

    function testUpdateLeaderboard() {

        $dataUpdate = array(
            'leaderboardId' => 1,
            'imageURL' => 'http://path/to/update.img',
            'location' => 'LA',
            'screenName' => 'Test',
            'prize' => 'prize value'
            );
        $getLeaderBoards = $this->leaderboard->getAll( 10,0 );

        if ( is_array( $getLeaderBoards ) && isset( $getLeaderBoards['leaderBoards'] ) && sizeof( $getLeaderBoards['leaderBoards'] ) > 0 ) {
            
            // To verify update leaderboard is invalid
            // =======================================
            
            // To verify leaderboardId is invalid
            $leaderBoardIdInvalid = array( NULL, 'abc', -1, 0 );
            
            foreach ($leaderBoardIdInvalid as $key => $value) {
                
                $testResultFirst = $this->leaderboard->update( $value, $dataUpdate );

                if( is_array( $testResultFirst ) && isset( $testResultFirst['error'] ) ) {

                    $this->unit->run( $testResultFirst['error'], 'Number Of Winners must is a numeric and greater than zero', 'To verify update leaderboard is invalid' , 'To verify Number Of Winners must is a numeric and greater than zero' );
                }
            }

            // To verify input invalid
            $invalid = $dataUpdate;
            $invalid['imageURL'] = NULL;
            
            $testResultSecond = $this->leaderboard->update( 1, $invalid );
            if ( is_array( $testResultSecond ) && isset( $testResultSecond['error'] ) ) {

                $this->unit->run( $testResultSecond['error'][0], 'The imageURL field is required.', 'To verify update leaderboard is invalid', 'To verify imageURL input invalid' );
            }

            // To verify input invalid
            $invalid = $dataUpdate;
            $invalid['location'] = NULL;
            
            $testResultThird = $this->leaderboard->update( 1, $invalid );
            if ( is_array( $testResultThird ) && isset( $testResultThird['error'] ) ) {

                $this->unit->run( $testResultThird['error'][0], 'The location field is required.', 'To verify update leaderboard is invalid', 'To verify location input invalid' );
            }

            // To verify input invalid
            $invalid = $dataUpdate;
            $invalid['screenName'] = NULL;
            
            $testResultFourth = $this->leaderboard->update( 1, $invalid );
            if ( is_array( $testResultFourth ) && isset( $testResultFourth['error'] ) ) {

                $this->unit->run( $testResultFourth['error'][0], 'The screenName field is required.', 'To verify update leaderboard is invalid', 'To verify screenName input invalid' );
            }

            // To verify input invalid
            $invalid = $dataUpdate;
            $invalid['prize'] = NULL;
            
            $testResultFifth = $this->leaderboard->update( 1, $invalid );
            if ( is_array( $testResultFifth ) && isset( $testResultFifth['error'] ) ) {

                $this->unit->run( $testResultFifth['error'][0], 'The prize field is required.', 'To verify update leaderboard is invalid', 'To verify prize input invalid' );
            }
            
            // To verify update leaderboard is valid
            // =======================================
            
            $id = $getLeaderBoards['leaderBoards'][0]->id;
            $testResultSixth = $this->leaderboard->update( $id, $dataUpdate );
            if ( is_object( $testResultSixth ) ) {

                // To verify id return must be equal id input
                $this->unit->run( (int)$testResultSixth->id, (int)$id, 'To verify update leaderboard is valid', 'To verify id return must be equal id input' );

                // To verify leaaderBoardId return must be equal id input
                $this->unit->run( (int)$testResultSixth->leaderboardId, $dataUpdate['leaderboardId'], 'To verify update leaderboard is valid', 'To verify leaderboardId return must be equal leaderboardId input' );

                // To verify location return must be equal location input
                $this->unit->run( $testResultSixth->location, $dataUpdate['location'], 'To verify update leaderboard is valid', 'To verify location return must be equal location input' );

                // To verify imageURL return must be equal imageURL input
                $this->unit->run( $testResultSixth->imageURL, $dataUpdate['imageURL'], 'To verify update leaderboard is valid', 'To verify imageURL return must be equal imageURL input' );

                // To verify prize return must be equal prize input
                $this->unit->run( $testResultSixth->prize, $dataUpdate['prize'], 'To verify update leaderboard is valid', 'To verify prize return must be equal prize input' );
                
            }

        } else {

            echo "<h4 style='color: red;'>Can't verify update leaderboard. Database empty. Pls try testing add leaderboard before tesing update.</h4>" ;
        }

        echo $this->unit->report();
        echo $this->returnResult( $this->unit->result() );
    }

    function testGetById() {
        $test = $this->leaderboard->getAll(10, 0);
            
        if ( is_array($test) && isset( $test['leaderBoards'] ) && sizeof($test['leaderBoards']) > 0) {

            // To verify leardboard return is invalid
            $invalId =  -1;
            $testResultFirst = $this->leaderboard->getById( $invalId );
            if (is_array( $testResultFirst ) && isset( $testResultFirst['error'] ) ) {

                // To verify leaderboard invalid
                $this->unit->run( $testResultFirst['error'], 'LeaderBoard Not Found', 'To verify get leaderboard invalid', 'To verify leaderboard id invalid' );
            }

            $leaderBoardTest = $test['leaderBoards'][0];
            $id = $leaderBoardTest->leaderboardId;
            $testResultSecond = $this->leaderboard->getById( $id );
            
            // To verify leardboard return is valid
            if ( is_array( $testResultSecond ) && isset( $testResultSecond['leaderBoards'] ) ) {

                foreach ($testResultSecond['leaderBoards'] as $key => $value) {
                    
                    // To verify leaderboard is invalid
                    $this->unit->run( (int)$value->leaderboardId, (int)$id, 'To verify leardboard return is valid', 'To verify leaderboard is invalid');
                }
            }

        } else {

            echo "<h4 style='color: red;'>Can't verify get leaderboard. Database empty. Pls try testing add leaderboard before tesing get By Id.</h4>" ;
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