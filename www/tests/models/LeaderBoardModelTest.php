<?php

class LeaderBoardModelTest extends CIUnit_TestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->CI->load->model('leaderboard');

        $this->leaderboard = $this->CI->leaderboard;

    }

    public function tearDown()
    {
        parent::tearDown();
    }

    function testAddLeaderboard() {

        // To verify leaderboard create is invalid
        //=========================================

        // To verify number of winner is invalid
        $numberInvalid = array( null, 'abc', -1, 0);

        foreach ($numberInvalid as $key => $value) {
            
            $testResultFirst = $this->leaderboard->add( $value );

            if ( is_array( $testResultFirst ) && isset( $testResultFirst['message'] ) ) {

                // To verify number must be is numberic and greater than zero
                $this->assertContains( $testResultFirst['message'], 'Number Of Winners must is a numeric and greater than zero', 'To verify number must be is numberic and greater than zero' );
            } 
            
        }

        // To verify leaderboard create is valid
        // ========================================
        $numberOfWinner = 5;

        $testResultSecond = $this->leaderboard->add( $numberOfWinner );

        if ( is_array( $testResultSecond ) && isset( $testResultSecond['leaderBoards'] ) ) {

            // To verify leaderboard create must be equal number of winner input
            $this->assertEquals( sizeof($testResultSecond['leaderBoards']), $numberOfWinner, 'To verivy leaderboard create must be equal number of winner input');

            foreach ( $testResultSecond['leaderBoards'] as $key => $value ) {

                // To verify all value return on leaderboard is null
                $this->assertEquals( $value->id, ($key + 1), 'To verify Id return is valid' );

                // To verify leaderBoad id null
                $this->assertEmpty( $value->leaderboardId, 'To verify leaderboardId return is valid' );                
                
                // To verify imageURL id null
                $this->assertEmpty( $value->imageURL, 'To verify imageURL return is valid' );
                
                // To verify location id null
                $this->assertEmpty( $value->location, 'To verify location return is valid' );
                
                // To verify screenName id null
                $this->assertEmpty( $value->screenName, 'To verify screenName return is valid' );
                
                // To verify prize id null
                $this->assertEmpty( $value->prize, 'To verify prize return is valid' );
                
            }
            
        }

    }

    function testUpdateLeaderboard() {

        $dataUpdate = array(
            'leaderboardId' => 1,
            'imageURL'      => 'http://path/to/update.img',
            'location'      => 'LA',
            'screenName'    => 'Test',
            'prize'         => 'prize value'
            );
        $getLeaderBoards = $this->leaderboard->getAll( 10,0 );

        if ( is_array( $getLeaderBoards ) && isset( $getLeaderBoards['leaderBoards'] ) && sizeof( $getLeaderBoards['leaderBoards'] ) > 0 ) {
            // To verify update leaderboard is invalid
            // =======================================
            
            // To verify leaderboardId is invalid
            $leaderBoardIdInvalid = array( NULL, 'abc', -1, 0 );
            
            foreach ($leaderBoardIdInvalid as $key => $value) {
                
                $testResultFirst = $this->leaderboard->edit( $value, $dataUpdate );

                if( is_array( $testResultFirst ) && isset( $testResultFirst['message'] ) ) {

                    $this->assertContains( $testResultFirst['message'], 'Id must is a numeric and greater than zero', 'To verify Number Of Winners must is a numeric and greater than zero' );
                }
            }

            // To verify input invalid
            $invalid = $dataUpdate;
            $invalid['imageURL'] = NULL;
            
            $testResultSecond = $this->leaderboard->edit( 1, $invalid );

            if ( is_array( $testResultSecond ) && isset( $testResultSecond['message'] ) ) {

                $this->assertContains( $testResultSecond['message'][0], 'The imageURL field is required.', 'To verify imageURL input invalid' );
            }

            // To verify input invalid
            $invalid             = $dataUpdate;
            $invalid['location'] = NULL;
            
            $testResultThird = $this->leaderboard->edit( 1, $invalid );

            if ( is_array( $testResultThird ) && isset( $testResultThird['message'] ) ) {

                $this->assertContains( $testResultThird['message'][0], 'The location field is required.', 'To verify location input invalid' );
            }

            // To verify input invalid
            $invalid               = $dataUpdate;
            $invalid['screenName'] = NULL;
            
            $testResultFourth      = $this->leaderboard->edit( 1, $invalid );

            if ( is_array( $testResultFourth ) && isset( $testResultFourth['message'] ) ) {

                $this->assertContains( $testResultFourth['message'][0], 'The screenName field is required.', 'To verify screenName input invalid' );
            }

            // To verify input invalid
            $invalid          = $dataUpdate;
            $invalid['prize'] = NULL;
            
            $testResultFifth  = $this->leaderboard->edit( 1, $invalid );
            if ( is_array( $testResultFifth ) && isset( $testResultFifth['message'] ) ) {

                $this->assertContains( $testResultFifth['message'][0], 'The prize field is required.', 'To verify prize input invalid' );
            }
            
            // To verify update leaderboard is valid
            // ======================================
            $id = $getLeaderBoards['leaderBoards'][0]->id;
            $testResultSixth = $this->leaderboard->edit( $id, $dataUpdate );

            if ( is_object( $testResultSixth ) ) {

                // To verify id return must be equal id input
                $this->assertEquals( (int)$testResultSixth->id, (int)$id, 'To verify id return must be equal id input' );

                // To verify leaaderBoardId return must be equal id input
                $this->assertEquals( (int)$testResultSixth->leaderboardId, $dataUpdate['leaderboardId'], 'To verify leaderboardId return must be equal leaderboardId input' );

                // To verify location return must be equal location input
                $this->assertEquals( $testResultSixth->location, $dataUpdate['location'], 'To verify location return must be equal location input' );

                // To verify imageURL return must be equal imageURL input
                $this->assertEquals( $testResultSixth->imageURL, $dataUpdate['imageURL'], 'To verify imageURL return must be equal imageURL input' );

                // To verify prize return must be equal prize input
                $this->assertEquals( $testResultSixth->prize, $dataUpdate['prize'], 'To verify prize return must be equal prize input' );
                
            }

        } else {

            $this->assertTrue( FALSE, "Can't verify update leaderboard. Database empty. Pls try testing add leaderboard before tesing update");
        }
    }

    function testGetById() {

        $test = $this->leaderboard->getAll(10, 0);

        if ( is_array($test) && isset( $test['leaderBoards'] ) && sizeof($test['leaderBoards']) > 0) {

            $leaderBoardTest  = $test['leaderBoards'][0];
            $id               = (int)$leaderBoardTest->leaderboardId;
            $testResultSecond = $this->leaderboard->getById( $id );
            
            // To verify leardboard return is valid
            if ( is_object( $testResultSecond ) ) {

                 // To verify id return must be equal id input
                $this->assertEquals( (int)$testResultSecond->id, (int)$id, 'To verify id return must be equal id input' );

                // To verify leaaderBoardId return must be equal id input
                $this->assertEquals( (int)$testResultSecond->leaderboardId, (int)$leaderBoardTest->leaderboardId, 'To verify leaderboardId return must be equal leaderboardId input' );

                // To verify location return must be equal location input
                $this->assertEquals( $testResultSecond->location, $leaderBoardTest->location, 'To verify location return must be equal location input' );

                // To verify imageURL return must be equal imageURL input
                $this->assertEquals( $testResultSecond->imageURL, $leaderBoardTest->imageURL, 'To verify imageURL return must be equal imageURL input' );

                // To verify prize return must be equal prize input
                $this->assertEquals( $testResultSecond->prize, $leaderBoardTest->prize, 'To verify prize return must be equal prize input' );
            }

        } else {

            $this->assertTrue( FALSE, "Can't verify get leaderboard. Database empty. Pls try testing add leaderboard before tesing get By Id") ;
        } 
    }
}