<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
/**
 * Testing finalgames 
 * 
 */
class test_finalgames extends CI_Controller {
  
    function __construct() {

        parent::__construct();

        // loading model finalgames
        $this->load->model( 'finalresult' );
        $this->load->model( 'finalmatch' );
        $this->load->model( 'finalplayercard' );

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
    
    function testAddFinal() {

        $final = $this->finalmatch->limit(1)->order_by('finalCategoryId', 'DESC')->get_all();
        $finalCategoryId = !empty($final) ? ((int)$final[0]->finalCategoryId + 1): 1 ;
        $data = array(
            'team1' => 1,
            'team2' => 2,
            'name' => 'semi1',
            'finalCategoryId' => $finalCategoryId,
            );

        //To verify add final is invalid
        //==============================
        // To verify data is empty
        $dataInvalid = '';
        $testResultFirst = $this->finalmatch->add( $dataInvalid );

        if (is_array($testResultFirst) && isset($testResultFirst['message']) ) {

            $this->unit->run( $testResultFirst['message'], 'Please the required enter data', 'To verify add final is invalid', 'To verify data is empty');
        }

        // To verify finale catagoryId is invalid
        $categoryIdInvalid = array('', null, 0, -1);
        foreach ($categoryIdInvalid as $value) {
            $category               = $data;
            $category['finalCategoryId'] = $value;
            $testResultSecond       = $this->finalmatch->add( $category );
            if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

                if ( !empty( $value ) ) {
                    $this->unit->run( $testResultSecond['message'][0], 'The finalCategoryId field must contain a number greater than 0.', 'To verify add final is invalid', 'To verify catagoryId is invalid' );
                } else {

                    $this->unit->run( $testResultSecond['message'][0], 'The finalCategoryId field is required.', 'To verify add final is invalid', 'To verify catagoryId is invalid' );
                }
            }   
        }

        // To verify finale team1 is invalid
        $team1Invalid = array('', null, 0, -1);
        foreach ($team1Invalid as $value) {
            $team1            = $data;
            $team1['team1']   = $value;
            $testResultThird = $this->finalmatch->add( $team1 );
            if( is_array($testResultThird) && isset($testResultThird['message'])) {

                if ( !empty( $value ) ) {
                    $this->unit->run( $testResultThird['message'][0], 'The team1 field must contain a number greater than 0.', 'To verify add final is invalid', 'To verify team1 is invalid' );
                } else {
                    $this->unit->run( $testResultThird['message'][0], 'The team1 field is required.', 'To verify add final is invalid', 'To verify team1 is invalid' );
                }
            }   
        }

        // To verify finale team2 is invalid
        $team1Invalid = array('', null, 0, -1);
        foreach ($team1Invalid as $value) {
            $team2           = $data;
            $team2['team2']  = $value;
            $testResultFourth= $this->finalmatch->add( $team2 );
            if( is_array($testResultFourth) && isset($testResultFourth['message'])) {

                if ( !empty( $value ) ) {
                    $this->unit->run( $testResultFourth['message'][0], 'The team2 field must contain a number greater than 0.', 'To verify add final is invalid', 'To verify team2 is invalid' );
                } else {
                    $this->unit->run( $testResultFourth['message'][0], 'The team2 field is required.', 'To verify add final is invalid', 'To verify team2 is invalid' );
                }
            }   
        }

        // To verify name is invalid
        $nameInvalid         = $data;
        $nameInvalid['name'] = '';
        $testResultFifth     = $this->finalmatch->add( $nameInvalid );

        if( is_array($testResultFifth) && isset($testResultFifth['message'])) {

            $this->unit->run( $testResultFifth['message'][0], 'The name field is required.', 'To verify add final is invalid', 'To verify name is invalid' );
        }

        // To verify add final is valid
        $testResultSixth = $this->finalmatch->add( $data );

        if ( is_object( $testResultSixth ) ) {

            // To verify categoryId return must equal category from input
            $this->unit->run((int)$testResultSixth->finalCategoryId, $data['finalCategoryId'], 'To verify add final is valid', 'To verify finalCategoryId return must equal category from input');

            // To verify name return must equal name from input
            $this->unit->run($testResultSixth->name, $data['name'], 'To verify add final is valid', 'To verify final return must equal name from input');

            // To verify team1 return must equal team1 from input
            $this->unit->run((int)$testResultSixth->team1, $data['team1'], 'To verify add final is valid', 'To verify team1 return must equal team1 from input');

            // To verify team2 return must equal team2 from input
            $this->unit->run((int)$testResultSixth->team2, $data['team2'], 'To verify add final is valid', 'To verify team2 return must equal team2 from input');
        } else {

            echo "<h4 style='color:red;'> Cant't verify add final in case valid.</h4>";
        }
        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());    
    }

    function testUpdateFinal() {
        $dataUpdate = array(
            'team1' => 3,
            'team2' => 4,
            'name' => 'semi1',
            'finalCategoryId' => 4,
            );
        $final = $this->finalmatch->limit(1)->order_by('id', 'DESC')->get_all();

        if ( !empty( $final ) ) {

            $id = $final[0]->id;

            //To verify udpate final is invalid
            //==============================
            // To verify data is empty
            $dataInvalid = '';
            $testResultFirst = $this->finalmatch->edit( $id, $dataInvalid );

            if (is_array($testResultFirst) && isset($testResultFirst['message']) ) {

                $this->unit->run( $testResultFirst['message'][0], 'Please the required enter data', 'To verify update final is invalid', 'To verify data is empty');
            }
            // To verify finale id is invalid
            $idInvalid = array('', null, 0, -1);
            foreach ($idInvalid as $value) {

                $testResultSecond = $this->finalmatch->edit( $value, $dataUpdate );
                if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

                    $this->unit->run( $testResultSecond['message'][0], 'Id must to be a numeric and greater than zero', 'To verify update final is invalid', 'To verify id is invalid' );

                }   
            }

            // To verify finale categoryId is invalid
            $categoryIdInvalid = array('', null, 0, -1);
            foreach ($categoryIdInvalid as $value) {
                $category               = $dataUpdate;
                $category['finalCategoryId'] = $value;
                $testResultSecond       = $this->finalmatch->edit( $id, $category );
                if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

                    if ( !empty( $value ) ) {
                        $this->unit->run( $testResultSecond['message'][0], 'The finalCategoryId field must contain a number greater than 0.', 'To verify update final is invalid', 'To verify categoryId is invalid' );
                    } else {

                        $this->unit->run( $testResultSecond['message'][0], 'The finalCategoryId field is required.', 'To verify update final is invalid', 'To verify categoryId is invalid' );
                    }
                }   
            }

            // To verify finale team1 is invalid
            $team1Invalid = array('', null, 0, -1);
            foreach ($team1Invalid as $value) {
                $team1            = $dataUpdate;
                $team1['team1']   = $value;
                $testResultThird = $this->finalmatch->edit( $id, $team1 );
                if( is_array($testResultThird) && isset($testResultThird['message'])) {

                    if ( !empty( $value ) ) {
                        $this->unit->run( $testResultThird['message'][0], 'The team1 field must contain a number greater than 0.', 'To verify update final is invalid', 'To verify team1 is invalid' );
                    } else {
                        $this->unit->run( $testResultThird['message'][0], 'The team1 field is required.', 'To verify update final is invalid', 'To verify team1 is invalid' );
                    }
                }   
            }

            // To verify finale team2 is invalid
            $team1Invalid = array('', null, 0, -1);
            foreach ($team1Invalid as $value) {
                $team2           = $dataUpdate;
                $team2['team2']  = $value;
                $testResultFourth= $this->finalmatch->edit( $id, $team2 );
                if( is_array($testResultFourth) && isset($testResultFourth['message'])) {

                    if ( !empty( $value ) ) {
                        $this->unit->run( $testResultFourth['message'][0], 'The team2 field must contain a number greater than 0.', 'To verify update final is invalid', 'To verify team2 is invalid' );
                    } else {
                        $this->unit->run( $testResultFourth['message'][0], 'The team2 field is required.', 'To verify update final is invalid', 'To verify team2 is invalid' );
                    }
                }   
            }

            // To verify name is invalid
            $nameInvalid         = $dataUpdate;
            $nameInvalid['name'] = '';
            $testResultFifth     = $this->finalmatch->edit( $id, $nameInvalid );

            if( is_array($testResultFifth) && isset($testResultFifth['message'])) {

                $this->unit->run( $testResultFifth['message'][0], 'The name field is required.', 'To verify update final is invalid', 'To verify name is invalid' );
            }

            // To verify update final is valid
            $testResultSixth = $this->finalmatch->edit( $id, $dataUpdate );

            if ( is_object( $testResultSixth ) ) {

                // To verify id return must equal category from input
                $this->unit->run($testResultSixth->id, $id , 'To verify update final is valid', 'To verify id return must equal id from input');

                // To verify categoryId return must equal category from input
                $this->unit->run((int)$testResultSixth->finalCategoryId, $dataUpdate['finalCategoryId'], 'To verify update final is valid', 'To verify finalCategoryId return must equal category from input');

                // To verify name return must equal name from input
                $this->unit->run($testResultSixth->name, $dataUpdate['name'], 'To verify update final is valid', 'To verify final return must equal name from input');

                // To verify team1 return must equal team1 from input
                $this->unit->run((int)$testResultSixth->team1, $dataUpdate['team1'], 'To verify update final is valid', 'To verify team1 return must equal team1 from input');

                // To verify team2 return must equal team2 from input
                $this->unit->run((int)$testResultSixth->team2, $dataUpdate['team2'], 'To verify update final is valid', 'To verify team2 return must equal team2 from input');
            } else {

                echo "<h4 style='color:red;'> Cant't verify update final in case valid.</h4>";
            }
        } else {
            echo "<h4 style='color:red;'> Cant't verify update final in case valid.</h4>";
        }

        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());  
    }

    function testDeleteFinal() {
        $final = $this->finalmatch->limit(1)->order_by('id', 'DESC')->get_all();

        if ( !empty($final) ) {

            $id = $final[0]->id;
            // To verify delete final is invalid
            // ===================================
            
            // To verify finale id is invalid
            $idInvalid = array('', null, 0, -1, 'abc' );
            foreach ($idInvalid as $value) {

                $testResultFirst = $this->finalmatch->destroy( $value );
                if( is_array($testResultFirst) && isset($testResultFirst['message'])) {

                    $this->unit->run( $testResultFirst['message'][0], 'Id must be a numeric and greater than zero', 'To verify delete final is invalid', 'To verify id is invalid' );

                }   
            }

            // To verify delete final is valid
            // ===================================
            $testResultSecond = $this->finalmatch->destroy( $id );

            if ( is_array($testResultSecond) && isset($testResultSecond['statusCode']) && $testResultSecond['statusCode'] == 204) {

                $resultExpect = ($this->finalmatch->getById($id));

                // To verify content return is null
                $this->unit->run( $testResultSecond[0], 'is_null', 'To verify content return is null', 'To verify delete final is valid' ); 
                
                // To verify statuscde return is 204
                $this->unit->run( $testResultSecond['statusCode'], 204, 'To verify statuscde return is 204', 'To verify delete final is valid' );

                // To verify id not exist in database when delete
                $this->unit->run( $resultExpect['message'], 'Final Match Not Found', 'To verify id not exist in database when delete', 'To verify delete final is valid' );

            } 
            else {

                echo "<h4 style='color:red;'> Cant't verify delete final in case valid. </h4>"; 
            }

        } else {

            echo "<h4 style='color:red;'> Cant't verify delete final in case valid.</h4>";
        }

        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());  
    }

    public function addFinal() {

        $final = $this->finalmatch->limit(1)->order_by('finalCategoryId', 'DESC')->get_all();
        $finalCategoryId = !empty($final) ? ( (int)$final[0]->finalCategoryId + 1 ) : 1; 
        $dataSemi1  = array(
            'team1' => 1,
            'team2' => 2,
            'name'  => 'semi1',
            'finalCategoryId' => $finalCategoryId,
        );
        $dataSemi2  = array(
            'team1' => 3,
            'team2' => 4,
            'name'  => 'semi2',
            'finalCategoryId' => $finalCategoryId,
        );
        $this->finalmatch->add( $dataSemi1 );
        $this->finalmatch->add( $dataSemi2 );

        $dataFinals = $this->finalmatch->getByCategoryId( $finalCategoryId );

        if ( !empty($dataFinals) && $dataFinals['code'] == 0) {

            $listIds = array();
            foreach ($dataFinals['matches'] as $dataFinal ) {
                $name = $dataFinal->name;
                $listIds["$name"] = $dataFinal->id;
            }
            
            return $listIds;

        } else {

            echo "<h4 style='color:red;'> Cant't add Finale Game.</h4>";
        }
    }

    function testAddResult() {
        $ids = $this->addFinal();
        if ( sizeof($ids) == 3 ) {
            $data = array(
                'finalMatchId' => 1,
                'score1' => 44,
                'score2' => 41,
                );

            // To verify add result Game Final is invalid
            // ================================
            // To verify data is empty
            $dataEmpty = '';

            $testResultFirst = $this->finalresult->add($dataEmpty);

            if ( is_array( $testResultFirst ) && isset( $testResultFirst['message'] ) ) {

                // To verify data is empty
                $this->unit->run( $testResultFirst['message'], 'Please enter the required data', 'To verify add final result is invalid', 'To verify data is empty' );
            }

            // To verify finalMatchId is invalid
            $idInvalid = array('', NULL, 'abc', 0, -1);
            foreach ($idInvalid as $key => $value) {
                $dataIdInvalid = $data;
                $dataIdInvalid['finalMatchId'] = $value; 
                $testResultSecond = $this->finalresult->add( $dataIdInvalid );

                if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

                    if ( !empty( $value ) ) {

                        $this->unit->run( $testResultSecond['message'][0], 'The final Match Id field must contain a number greater than 0.', 'To verify add final is invalid', 'To verify finalMatchId is invalid' );
                    } else {
                        $this->unit->run( $testResultSecond['message'][0], 'The final Match Id field is required.', 'To verify add final is invalid', 'To verify finalMatchId is invalid' );
                    }
                }  
            } 

            // To verify score1 is invalid
            $dataScore1 = $data;
            $score1 = array('', NULL, 'abc', 0, -1);
            foreach ($score1 as $key => $value) {
                    
                $dataScore1['score1'] = $value; 
                $testResultThird = $this->finalresult->add( $dataScore1 );
         
                if( is_array($testResultThird) && isset($testResultThird['message'])) {

                    if ( !empty( $value ) ) {

                        $this->unit->run( $testResultThird['message'][0], 'The score1 field must contain a number greater than 0.', 'To verify add final is invalid', 'To verify score1 is invalid' );
                    } else {

                        $this->unit->run( $testResultThird['message'][0], 'The score1 field is required.', 'To verify add final is invalid', 'To verify score1 is invalid' );
                    }
                }  
            } 

            // To verify score2 is invalid
            $dataScore2 = $data;
            $score2 = array('', NULL, 'abc', 0, -1);
            foreach ($score2 as $key => $value) {
                    
                $dataScore2['score2'] = $value; 
                $testResultFourth = $this->finalresult->add( $dataScore2 );
         
                if( is_array($testResultFourth) && isset($testResultFourth['message'])) {

                    if ( !empty( $value ) ) {

                        $this->unit->run( $testResultFourth['message'][0], 'The score2 field must contain a number greater than 0.', 'To verify add final is invalid', 'To verify score2 is invalid' );
                    } else {

                        $this->unit->run( $testResultFourth['message'][0], 'The score2 field is required.', 'To verify add final is invalid', 'To verify score2 is invalid' );
                    }
                }  
            } 

            // To verify finalMatchId is exits
            $finalExit = $this->finalresult->limit(1)->get_all();
            
            if ( !empty( $finalExit ) ) {

                $finalId = $finalExit[0]->finalMatchId;
                $dataExist = $data;
                $dataExist['finalMatchId'] = $finalId;
                $testResultFifth = $this->finalresult->add( $dataExist );


                if ( is_array( $testResultFifth ) && isset( $testResultFifth['message'] ) ) {

                    $this->unit->run( $testResultFifth['message'], $this->db->_error_message(), 'To verify add final result is invalid', 'To verify finalMatchId is exits' );
                }
            }

            // To verify add result Game Final is valid
            // ================================
            $dataSemi1 = $data;
            $dataSemi1['finalMatchId'] = $ids['semi1'];
            $testResultFifth = $this->finalresult->add( $dataSemi1 );

            if( is_object( $testResultFifth ) ) {

                // To verify finalMatchId return must be equal id input
                $this->unit->run($testResultFifth->finalMatchId, $ids['semi1'], 'To verify add result Game Final is valid', 'To verify finalMatchId return must be equal id input' );
                
                // To verify score1 return must be equal score1 input
                $this->unit->run($testResultFifth->score1, $dataSemi1['score1'], 'To verify add result Game Final is valid', 'To verify score1 return must be equal score1 input' );
                
                // To verify score2 return must be equal score2 input
                $this->unit->run($testResultFifth->score2, $dataSemi1['score2'], 'To verify add result Game Final is valid', 'To verify score2 return must be equal score2 input' );

                // To verify winner must be is 2
                $this->unit->run((int)$testResultFifth->winner, 2 , 'To verify add result Game Final is valid', 'To verify winner must be is 2' );
            } else {

                echo "<h4 style='color:red;'> Can't verify Add results in case valid. </h4>";
            } 

        } else {

            echo "<h4 style='color:red;'> Can't verify Add results. Final Game is empty.</h4>";
        }
        
        echo $this->unit->report();
        echo $this->returnResult($this->unit->result()); 
    }

    function testUpdateResult() {

        $final = $this->finalresult->order_by('finalMatchId', 'DESC')->get_by( array('winner !=' => 0));
        
        if( !empty( $final ) ) {
            $id             = $final->finalMatchId;
            $winnerExpected = $final->winner;
            $scoreExpected1 = $final->score1;
            $scoreExpected2 = $final->score2;
            $dataUpdate     = array(
                'score1'    => $scoreExpected2,
                'score2'    => $scoreExpected1
            );
            // To verify update result Game Final is invalid
            // ================================
            // To verify final Id input return invalid
            $idInvalid = array('', null, 0, -1, 'abc');

            foreach ($idInvalid as $key => $value) {
                
                $testResultFirst = $this->finalresult->edit( $value ,$dataUpdate );
                if (is_array($testResultFirst) && isset( $testResultFirst['message'])) {


                    $this->unit->run( $testResultFirst['message'], 'Id must to be a numeric and greater than zero', 'To verify update  is invalid', 'To verify input data is invalid' );
                } 
            }

            // To verify data is empty     
            $testResultSecond = $this->finalresult->edit( $id , '' );
            if (is_array($testResultSecond) && isset( $testResultSecond['message'])) {

                $this->unit->run( $testResultSecond['message'], 'Please enter the required data', 'To verify update final result is invalid', 'To verify data is empty ' );
            }

            // To verify score1 is invalid
            $dataScore1 = $dataUpdate;
            $score1 = array('', NULL, 'abc', 0, -1);
            foreach ($score1 as $key => $value) {
                    
                $dataScore1['score1'] = $value; 
                $testResultThird = $this->finalresult->edit( $id, $dataScore1 );
         
                if( is_array($testResultThird) && isset($testResultThird['message'])) {

                    if ( !empty( $value ) ) {

                        $this->unit->run( $testResultThird['message'][0], 'The score1 field must contain a number greater than 0.', 'To verify update final is invalid', 'To verify score1 is invalid' );
                    } else {

                        $this->unit->run( $testResultThird['message'][0], 'The score1 field is required.', 'To verify update final is invalid', 'To verify score1 is invalid' );
                    }
                }  
            } 

            // To verify score2 is invalid
            $dataScore2 = $dataUpdate;
            $score2 = array('', NULL, 'abc', 0, -1);
            foreach ($score2 as $key => $value) {
                    
                $dataScore2['score2'] = $value; 
                $testResultFourth = $this->finalresult->edit( $id, $dataScore2 );
         
                if( is_array($testResultFourth) && isset($testResultFourth['message'])) {

                    if ( !empty( $value ) ) {

                        $this->unit->run( $testResultFourth['message'][0], 'The score2 field must contain a number greater than 0.', 'To verify update final is invalid', 'To verify score2 is invalid' );
                    } else {

                        $this->unit->run( $testResultFourth['message'][0], 'The score2 field is required.', 'To verify update final is invalid', 'To verify score2 is invalid' );
                    }
                }  
            } 

            // To verify update result Game Final is valid
            // ================================

            $testResultFifth = $this->finalresult->edit( $id, $dataUpdate );

            if ( is_object($testResultFifth) ) {

                // To verify finalMatchId return must be equal id input
                $this->unit->run($testResultFifth->finalMatchId, $id, 'To verify update result Game Final is valid', 'To verify finalMatchId return must be equal id input' );
                
                // To verify score1 return must be equal score1 input
                $this->unit->run($testResultFifth->score1, $dataUpdate['score1'], 'To verify update result Game Final is valid', 'To verify score1 return must be equal score1 input' );
                
                // To verify score2 return must be equal score2 input
                $this->unit->run($testResultFifth->score2, $dataUpdate['score2'], 'To verify update result Game Final is valid', 'To verify score2 return must be equal score2 input' );

                // To verify winner must be is 2
                $this->unit->run( ($testResultFifth->winner != $winnerExpected), 'is_true' , 'To verify update result Game Final is valid', 'To verify winner return different winner first.' );

            } else {

                echo "<h4 style='color:red;'> Can't verify Update results. Final Game Result is empty.</h4>";  
            }
        } else {

            echo "<h4 style='color:red;'> Can't verify Update results. Final Game Result is empty.</h4>";   
        }
        
        echo $this->unit->report();
        echo $this->returnResult($this->unit->result()); 
    }

    function testGetResultByCategory() {
       
        $final = $this->finalresult->order_by('finalMatchId', 'DESC')->get_by( array('winner !=' => 0));

        if ( !empty( $final ) ) {
            $finalId = $final->finalMatchId;
            $finalMatch = $this->finalmatch->getById( $finalId );
            $finalCategoryId = $finalMatch->finalCategoryId;
            $resultsExpected = $this->db->select( 'FinalResults.*' )
                ->join( 'FinalMatches', 'FinalMatches.id = FinalResults.finalMatchId' )
                ->where( 'finalCategoryId', $finalCategoryId )
                ->get( 'FinalResults' )
                ->result();

            // To verify get byCategory result Game Final is invalid
            // ================================
            // To verify categoryId input is invalid
            $idInvalid = array('', null, 0, -1, 'abc');

            foreach ($idInvalid as $key => $value) {
                
                $testResultFirst = $this->finalresult->getByCategoryId( $value );
                if (is_array($testResultFirst) && isset( $testResultFirst['message'])) {

                    $this->unit->run( $testResultFirst['message'], 'Category Id must to be is numuric and greater than zero', 'To verify get by categoryId is invalid', 'To verify categoryId invalid' );
                } 
            }
            
            // To verify get byCategory result Game Final is valid
            // ================================
            $testResultSecond = $this->finalresult->getByCategoryId($finalCategoryId);
            if ( isset($testResultSecond['code']) && $testResultSecond['code'] == 0 ) {

                $results = !empty($testResultSecond['results']) ? $testResultSecond['results'] : array();
                foreach ($results as $key => $result) {

                    if ( array_key_exists($key , $resultsExpected ) ) {

                        // To verify finalMatchId return must be finalMatchId from database
                        $this->unit->run($results["$key"]->finalMatchId, $resultsExpected["$key"]->finalMatchId, 'To verify get byCategory result Game Final is valid', 'To verify finalMatchId return must be finalMatchId from database');  
                         
                        // To verify score1 return must be score1 from database
                        $this->unit->run($results["$key"]->score1, $resultsExpected["$key"]->score1, 'To verify get byCategory result Game Final is valid', 'To verify score1 return must be score1 from database'); 

                        // To verify score2 return must be score2 from database
                        $this->unit->run($results["$key"]->score2, $resultsExpected["$key"]->score2, 'To verify get byCategory result Game Final is valid', 'To verify score2 return must be score2 from database'); 

                        // To verify winner return must be winner from database
                        $this->unit->run($results["$key"]->winner, $resultsExpected["$key"]->winner, 'To verify get byCategory result Game Final is valid', 'To verify winner return must be winner from database'); 
                    }
                }
            } else {

                echo "<h4 style='color:red;'> Can't verify get results by categoryId in case invalid.</h4>"; 
            }
        } else {

            echo "<h4 style='color:red;'> Can't verify get results by categoryId. Final Game Result is empty.</h4>";   
            
        } 
        
        echo $this->unit->report();
        echo $this->returnResult($this->unit->result()); 
    }

    function testDeleteResult() {

        // To verify delete result Game Final is invalid
        // ================================
        
        
        // To verify delete result Game Final is valid
        // ================================
        
        echo $this->unit->report();
        echo $this->returnResult($this->unit->result()); 


    }

    function testSavePlayerCards() {

        $final = $this->finalmatch->order_by('finalCategoryId', 'DESC')->get_by( array('finalCategoryId !=' => 0));

        if ( !empty($final) ) {
            $categoryId = $final->finalCategoryId;
            $dataExpected = array(
                'playerId' => 1,
                'finalCategoryId' => $categoryId,
                'team1' => 1,
                'team2' => 2,
                'team3' => 3,
                'team4' => 4,
                'team5' => 1,
                'team6' => 4,
                'score1' => 12,
                'score2' => 10,
                'score3' => 25,
                'score4' => 27,
                'score5' => 30,
                'score6' => 27
            );

            // To verify save player cards result Game Final is invalid
            // ================================
            
            // To verify data is empty     
            $testResultFirst = $this->finalplayercard->add( '' );
            if (is_array($testResultFirst) && isset( $testResultFirst['message'])) {

                $this->unit->run( $testResultFirst['message'], 'Please enter the required data', 'To verify save player cards result Game Final is invalid', 'To verify data is empty ' );
            } 

            // To verify playerId invalid
            $playerIdInvalid = array('', NULL, 0, -1, 'abc');
            foreach ($playerIdInvalid as $key => $value) {
                $dataIdInvalid = $dataExpected;
                $dataIdInvalid['playerId'] = $value;

                $testResultSecond = $this->finalplayercard->add( $dataIdInvalid );

                if (is_array($testResultSecond) && isset( $testResultSecond['message'])) {

                    if ( empty($value) ) {

                        $this->unit->run( $testResultSecond['message'][0], 'The playerId field is required.', 'To verify save game final is invalid', 'To verify playerId is invalid' );
                    } else {

                        $this->unit->run( $testResultSecond['message'][0], 'The playerId field must contain a number greater than 0.', 'To verify save game final is invalid', 'To verify playerId is invalid' );
                    }
                } 
            }

            // To verify finalCategoryId invalid
            $finalCategoryIdInvalid = array('', NULL, 0, -1, 'abc');

            foreach ($finalCategoryIdInvalid as $key => $value) {
                $dataIdInvalid = $dataExpected;
                $dataIdInvalid['finalCategoryId'] = $value;

                $testResultThird = $this->finalplayercard->add( $dataIdInvalid );

                if (is_array($testResultThird) && isset( $testResultThird['message'])) {

                    if ( empty($value) ) {

                        $this->unit->run( $testResultThird['message'][0], 'The finalCategoryId field is required.', 'To verify save game final is invalid', 'To verify finalCategoryId is invalid' );
                    } else {

                        $this->unit->run( $testResultThird['message'][0], 'The finalCategoryId field must contain a number greater than 0.', 'To verify save game final is invalid', 'To verify finalCategoryId is invalid' );
                    }
                } 
            }

            // To verify choice team5 is invalid
            $team5Invalid = $dataExpected;
            $team5Invalid['score2'] = 16;
            
            $testResultFourth = $this->finalplayercard->add($team5Invalid);

            if( isset($testResultFourth['code']) && $testResultFourth['code'] == 3) {

                $this->unit->run( $testResultFourth['message'][0], "Team5 is 'No Name' the incorrect. You enter score of 'No Name' is 16 greater of 'No Name' is 12", 'To verify save game final is invalid', 'To verify choice team5 is invalid');
            }

            // To verify choice team6 is invalid
            $team6Invalid = $dataExpected;
            $team6Invalid['score3'] = 30;
            
            $testResultFifth = $this->finalplayercard->add($team6Invalid);

            if( isset($testResultFifth['code']) && $testResultFifth['code'] == 3) {
                $this->unit->run( $testResultFifth['message'][0], "Team6 is 'No Name' the incorrect. You enter score of 'No Name' is 30 greater of 'No Name' is 27", 'To verify save game final is invalid', 'To verify choice team6 is invalid');
            }

            // To verify save player cards result Game Final is valid
            // ================================
            
            $testResultSixth = $this->finalplayercard->add($dataExpected);
            if( is_object($testResultSixth)) {

                // To verify playerId return must be equal playerId input
                $this->unit->run((int)$testResultSixth->playerId, $dataExpected['playerId'], 'To verify save player cards result Game Final is valid', 'To verify playerId return must be equal playerId input');

                // To verify finalCategoryId return must be equal finalCategoryId input
                $this->unit->run($testResultSixth->finalCategoryId, $dataExpected['finalCategoryId'], 'To verify save player cards result Game Final is valid', 'To verify finalCategoryId return must be equal finalCategoryId input');
                $score1 = (int)$dataExpected['score1'];
                $team1  = (int)$dataExpected['team1'];
                $score2 = (int)$dataExpected['score2'];
                $team2  = (int)$dataExpected['team2'];
                $score3 = (int)$dataExpected['score3'];
                $team3  = (int)$dataExpected['team3'];
                $score4 = (int)$dataExpected['score4'];
                $team4  = (int)$dataExpected['team4'];
                $team5  = (int)$dataExpected['team5'];
                $team6  = (int)$dataExpected['team6'];
                $pickExpected = array(
                        'final' => array( $team5 => $dataExpected['score5'], $team6 => $dataExpected['score6'] ),
                        'semi1' => array( $team1 => $score1, $team2 => $score2 ),
                        'semi2' => array( $team3 => $score3, $team4 => $score4 ),
                    );
                $picksHash = json_encode( $pickExpected );

                // To verify picksHash return must be equal picksHash input
                $this->unit->run($testResultSixth->picksHash, $picksHash , 'To verify save player cards result Game Final is valid', 'To verify picksHash return must be equal picksHash input');
            } 
            else {

                echo "<h4 style='color:red;'> Can't verify save card results is case invalid</h4>"; 
            }
        } else {

           echo "<h4 style='color:red;'> Can't verify save card results by categoryId. Final Game is empty.</h4>";  
        }

        echo $this->unit->report();
        echo $this->returnResult($this->unit->result()); 
    }

    function testAddEvaluation() {

        // To verify add evalution result Final Game is invalid
        // ================================
        
        
        // To verify add evalution result Final Game is valid
        // ================================
        
        echo $this->unit->report();
        echo $this->returnResult($this->unit->result()); 
    }

    function testGetEvaluation() {

        // To verify add evalution result Final Game is invalid
        // ================================
        
        
        // To verify add evalution result Final Game is valid
        // ================================
        
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