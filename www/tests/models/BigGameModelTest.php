<?php
class BigGameModelTest extends CIUnit_TestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->CI->load->model(array('bgresult', 'bgquestion', 'bganswer', 'bgplayercard', 'bgpick', 'gamecount'));
        $this->bgquestion = $this->CI->bgquestion;
        $this->bganswer = $this->CI->bganswer;
        $this->bgresult = $this->CI->bgresult;
        $this->bgplayercard = $this->CI->bgplayercard;
        $this->bgpick = $this->CI->bgpick;
        $this->gamecount = $this->CI->gamecount;

        $this->bgplayercard->executeTesting = TRUE;
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    function testAddQuestion() {

        $data = array(
            'categoryId' => 3,
            'question'   => 'Which team will have more rushing yards?',
            'rule'       => 'TEST',
            'startDate'  => '11-01-2014 00:00:00',
            'endDate'    => '12-01-2014 00:00:00',
            'answer1'    => 'Yes',
            'answer2'    => 'No'
            );
        // To verify add question is invalid
        // To verify data is empty
        $dataInvalid     = '';
        $testResultFirst = $this->bgquestion->add( $dataInvalid );

        if (is_array($testResultFirst) && isset($testResultFirst['message']) ) {

            $this->assertContains( $testResultFirst['message'], 'Please the required enter data', 'To verify data is empty');
        }

        // To verify catagoryId is invalid
        $categoryIdInvalid = array('', null, 0, -1);

        foreach ($categoryIdInvalid as $value) {
            $category               = $data;
            $category['categoryId'] = $value;
            $testResultSecond       = $this->bgquestion->add( $category );
            if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

                if ( !empty( $value ) ) {
                    $this->assertContains( $testResultSecond['message'][0], 'The categoryId field must contain a number greater than 0.', 'To verify catagoryId is invalid' );
                } else {
                    $this->assertContains( $testResultSecond['message'][0], 'The categoryId field is required.', 'To verify catagoryId is invalid' );
                }
            }
        }

        // To verify question is invalid
        $questionInvalid             = $data;
        $questionInvalid['question'] = '';
        $testResultThird             = $this->bgquestion->add( $questionInvalid );

        if( is_array($testResultThird) && isset($testResultThird['message'])) {

                $this->assertContains( $testResultThird['message'][0], 'The question field is required.', 'To verify question is invalid' );
        }

        // To verify rule is invalid
        $ruleInvalid         = $data;
        $ruleInvalid['rule'] = '';
        $testResultFourth    = $this->bgquestion->add( $ruleInvalid );

        if( is_array($testResultFourth) && isset($testResultFourth['message'])) {

                $this->assertContains( $testResultFourth['message'][0], 'The rule field is required.', 'To verify add rule is invalid', 'To verify rule is invalid' );
        }
        // To verify add question is valid
        $testResultFifth = $this->bgquestion->add( $data );

        if ( is_object($testResultFifth )) {

            // To verify categoryId return must equal category from input
            $this->assertEquals((int)$testResultFifth->categoryId, $data['categoryId'], 'To verify categoryId return must equal category from input');

            // To verify question return must equal question from input
            $this->assertEquals($testResultFifth->question, $data['question'], 'To verify question return must equal category from input');

            // To verify rule return must equal rule from input
            $this->assertEquals($testResultFifth->rule, $data['rule'], 'To verify rule return must equal category from input');

            foreach ($testResultFifth->answers as $key => $value) {

                if ( $key == 0) {

                    // To verify answer return must equal answer from input
                    $this->assertEquals($value->answer, $data['answer1'], 'To verify answer return must equal category from input');

                } elseif($key == 1) {

                    // To verify answer return must equal answer from input
                    $this->assertEquals($value->answer, $data['answer2'], 'To verify answer return must equal category from input');
                }
            }

        } else {

            $this->assertTrue(FALSE, " Cant't verify add question is case valid.");
        }

    }

    function testUpdateQuestion() {

        $dataUpdate      = array(
            'categoryId' => 4,
            'question'   => 'Which team will have more rushing yards?',
            'rule'       => 'Update',
            'startDate'  => '11-01-2014 00:00:00',
            'endDate'    => '12-01-2014 00:00:00',
            'answer1'    => 'No',
            'answer2'    => 'Yes'
            );
        $question = $this->bgquestion->limit(1)->order_by('id', 'DESC')->get_all();

        if( !empty($question) ) {

            $id = $question[0]->id;
            // To verify update question is invalid
            $idInvalid = array('', null, 0, -1) ;

            foreach ($idInvalid as $key => $value) {

                $testResultFirst = $this->bgquestion->edit($value, $dataUpdate);

                if ( is_array($testResultFirst) && isset($testResultFirst['message']) ) {

                    $this->assertContains($testResultFirst['message'][0], 'Id must to be a numeric and greater than zero', 'To verify Id input invalid');
                }
            }

            // To verify data is empty
            $testResultSecond = $this->bgquestion->edit( $id, '');

            if ( is_array($testResultSecond) && isset($testResultSecond['message']) ) {

                $this->assertContains($testResultSecond['message'][0], 'Please the required enter data', 'To verify Id input invalid');
            }
            // To verify catagoryId is invalid
            $categoryIdInvalid = array('', null, 0, -1);

            foreach ($categoryIdInvalid as $value) {

                $category               = $dataUpdate;
                $category['categoryId'] = $value;
                $testResultSecond       = $this->bgquestion->edit( $id, $category );
                if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

                    if ( !empty( $value ) ) {

                        $this->assertContains( $testResultSecond['message'][0], 'The categoryId field must contain a number greater than 0.', 'To verify catagoryId is invalid' );
                    } else {

                        $this->assertContains( $testResultSecond['message'][0], 'The categoryId field is required.', 'To verify catagoryId is invalid' );
                    }
                }
            }
            // To verify question is invalid
            $questionInvalid             = $dataUpdate;
            $questionInvalid['question'] = '';
            $testResultThird             = $this->bgquestion->edit( $id, $questionInvalid );

            if( is_array($testResultThird) && isset($testResultThird['message'])) {

                $this->assertContains( $testResultThird['message'][0], 'The question field is required.', 'To verify question is invalid' );
            }

            // To verify rule is invalid
            $ruleInvalid         = $dataUpdate;
            $ruleInvalid['rule'] = '';
            $testResultFourth    = $this->bgquestion->edit( $id, $ruleInvalid );

            if( is_array($testResultFourth) && isset($testResultFourth['message'])) {

                $this->assertContains( $testResultFourth['message'][0], 'The rule field is required.', 'To verify rule is invalid' );
            }

            // To verify only update answer
            // $answer = array(
            //     'answer1' => 'No',
            //     'answer2' => 'Yes'
            //     );
            // $testResultThird = $this->bgquestion->edit( $id, $answer);
            // // To verify update question is valid

            // if ( is_object( $testResultThird ) ) {

            //     foreach ($testResultThird->answers as $key => $value) {
            //         if ( $key == 0) {

            //             // To verify answer return must equal answer from input
            //             $this->assertEquals($value->answer, $answer['answer1'], 'To verify answer return must equal category from input');

            //         } elseif($key == 1) {

            //             // To verify answer return must equal answer from input
            //             $this->assertEquals($value->answer, $answer['answer2'], 'To verify answer return must equal category from input');
            //         }
            //     }
            // } else {

            //     $this->assertTrue( FALSE, "Cant't verify update incase valid");
            // }

        } else {

           $this->assertTrue(FALSE, "Cant't verify update question . Pls try run add new question before run test update");
        }

    }

    function testDeleteQuestion() {

        $question = $this->bgquestion->with( 'answers' )->limit(1)->order_by('id', 'DESC')->get_all();

        if ( !empty($question) ) {

            // To verify delete question is invalid
            //=====================================

            // To verify id input is invalid
            $idInvalid = array('', null, 0, -1) ;

            foreach ($idInvalid as $key => $value) {

                $testResultFirst = $this->bgquestion->destroy( $value );

                if ( is_array($testResultFirst) && isset($testResultFirst['message']) ) {

                    $this->assertContains($testResultFirst['message'], 'Id must is a numeric and greater than zero', 'To verify Id input invalid');
                }
            }

            $id = $question[0]->id;

            // To verify delete question is valid
            $testResultSecond = $this->bgquestion->destroy( $id );

            if ( is_array($testResultSecond) && isset($testResultSecond['statusCode']) && $testResultSecond['statusCode'] == 204) {

                $resultExpect = ($this->bgquestion->getById($id));

                // To verify content return is null
                $this->assertEmpty( $testResultSecond[0], 'To verify content return is null' );

                // To verify statuscde return is 204
                $this->assertEquals( $testResultSecond['statusCode'], 204, 'To verify statuscde return is 204' );

                // To verify id not exist in database when delete
                $this->assertContains( $resultExpect['message'], 'Question Not Found', 'To verify id not exist in database when delete' );

            }
            else {

                $this->assertTrue( FALSE, "Cant't verify delete question in case valid" );
            }

        } else {

            $this->assertTrue( FALSE, "Cant't verify delete question . Pls try run add new question before run test delete");
        }

    }

    function testAddResults() {

        $result = $this->bgquestion->with('answers')->limit(1)->order_by(array('id'=>'DESC', 'categoryId' => 'DESC') )->get_all();

        if ( !empty($result) ) {

            $question = $this->bgquestion->with('answers')->get_by(array('rule !=' => 'LessThan'));
            $id         = $question->id;
            $answer     = $question->answers[0]->answer;
            $answerId   = $question->answers[0]->id;
            $categoryId = $question->categoryId;
            $data       = array(
                 'questionId' => $id,
                 'answer'     => "$answer"
                );


            //=================================
            $dataEmpty       = '';
            $testResultFirst = $this->bgresult->add($dataEmpty);

            if ( is_array( $testResultFirst ) && isset( $testResultFirst['message'] ) ) {

                // To verify data is empty
                $this->assertContains( $testResultFirst['message'], 'Please enter the required data', 'To verify data is empty' );
            }

            // To verify input data is invalid
            $questionIdInvalid = array('', 'abc', null, 0, -1);

            foreach ($questionIdInvalid as $key => $value) {

                $dataIdInvalid               = $data;
                $dataIdInvalid['questionId'] = $value;
                $testResultSecond            = $this->bgresult->add( $dataIdInvalid);

                if (is_array($testResultSecond) && isset( $testResultSecond['message'])) {

                    if ( empty($value) ) {

                        $this->assertContains( $testResultSecond['message'][0], 'The Question Id field is required.', 'To verify input data is invalid' );

                    } else {

                        $this->assertContains( $testResultSecond['message'][0], 'The Question Id field must contain a number greater than 0.', 'To verify input data is invalid' );
                    }
                }
            }

            $answerInvalid           = $data;
            $answerInvalid['answer'] = '';
            $testResultThird         = $this->bgresult->add( $answerInvalid );

            if (is_array($testResultThird) && isset( $testResultThird['message'])) {

                $this->assertContains( $testResultThird['message'][0], 'The Answer field is required.', 'To verify input data is invalid' );
            }

            $answerIncorrect           = $data;
            $answerIncorrect['answer'] = $answer.'1';
            $testResultFourth          = $this->bgresult->add( $answerIncorrect );

            if ( is_array($testResultFourth) && isset($testResultFourth['message']) ) {
                $this->assertContains( $testResultFourth['message'], 'Big Game 21 Result Not Found', 'To verify answer is incorrect' );
            }

            // To verify add answer is valid
            //=================================
            $testResultFifth = $this->bgresult->add( $data );
            if ( is_array($testResultFifth) && isset($testResultFifth['results'])) {

                // To verify questionId return must equal questionId input
                $this->assertEquals( $testResultFifth['results'][0]->questionId, $data['questionId'], 'To verify questionId return must equal questionId input' );

                // To verify answerId return must equal answerId input
                $this->assertEquals( $testResultFifth['results'][0]->answerId, $answerId, 'To verify answerId return must equal answerId input' );

                // To verify categoryId return must equal categoryId input
                $this->assertEquals( $testResultFifth['results'][0]->bgCategoryId, $categoryId, 'To verify categoryId return must equal categoryId input' );

            } elseif( is_array( $testResultFifth ) && isset( $testResultFifth['message']) ) {

                $errorMessage = $this->CI->db->_error_message();

                $this->assertContains($testResultFifth['message'], $errorMessage, 'To verify {$errorMessage}');

            } else {

                $this->assertTrue("Cant't verify add result answers. Because question is empty.");

            }

            $answerSecond     = $question->answers[1]->answer;
            $answerIdSecond   = $question->answers[1]->id;
            $categoryIdSecond = $question->categoryId;
            $dataSecond       = array(
                'questionId' => $id,
                'answer'     => "$answerSecond"
                );

            $testResultSixth = $this->bgresult->add($dataSecond);
            if ( is_array($testResultSixth) && isset($testResultSixth['results'])) {

                // To verify questionId return must equal questionId input
                $this->assertEquals( $testResultSixth['results'][1]->questionId, $data['questionId'], 'To verify questionId return must equal questionId input' );

                // To verify answerId return must equal answerId input
                $this->assertEquals( $testResultSixth['results'][1]->answerId, $answerIdSecond, 'To verify answerId return must equal answerId input' );

                // To verify categoryId return must equal categoryId input
                $this->assertEquals( $testResultSixth['results'][1]->bgCategoryId, $categoryIdSecond, 'To verify categoryId return must equal categoryId input' );

            } elseif( is_array( $testResultSixth ) && isset( $testResultSixth['message']) ) {

                $errorMessage = $this->CI->db->_error_message();

                $this->assertContains($testResultSixth['message'], $errorMessage, 'To verify {$errorMessage}', 'To verify {$errorMessage}');

            } else {

                $this->assertTrue( FALSE, "Cant't verify add result answers. Because question is empty.");

            }

            $dataQuestion = array(
            'categoryId' => 3,
            'question'   => 'Which team will have more rushing yards?',
            'rule'       => 'LessThan',
            'startDate'  => '11-01-2014 00:00:00',
            'endDate'    => '12-01-2014 00:00:00',
            'answer1'    => '34',
            'answer2'    => '50',
            'answer3'    => '89'
            );

            // To verify add result with answer is LessThan
            $questionExpected = $this->bgquestion->add($dataQuestion);

            if ( is_object( $questionExpected ) ) {

                $idQuestion   = $questionExpected->id;
                $answer       = (int)($questionExpected->answers[2]->answer) + 3;
                $dataLessThan = array(
                    'questionId' => $idQuestion,
                    'answer' => "$answer"
                    );

                $testResultLessThan = $this->bgresult->add($dataLessThan);

                // To verify result return of question Id return must be is 3
                $this->assertEquals( sizeof($testResultLessThan['results']), 3, 'To verify result return of question Id return must be is 3');

                // To verify categoryId return must be equal category of question
                foreach ($testResultLessThan['results'] as $value) {

                    $this->assertEquals( (int)$value->bgCategoryId, $dataQuestion['categoryId'], 'To verify categoryId return must be equal category of question');
                }
            }

        } else {

            $this->assertTrue(FALSE, "Cant't verify add result answers. Because question is empty.");
        }

    }

    function testGetAllResults() {

        $results = $this->bgresult->limit(1)->order_by( 'bgCategoryId', 'DESC' )->get_all();

        if ( !empty($results) ) {

            $categoryId = $results[0]->bgCategoryId;

            // To verify get all results answer is invalid
            //============================================
            // To verify categoryId input is invalid
            $idInvalid = array('', null, 0, -1, 'abc');

            foreach ($idInvalid as $key => $value) {

                $testResultFirst = $this->bgresult->getAll( $value );

                if (is_array($testResultFirst) && isset( $testResultFirst['message'])) {


                    $this->assertContains( $testResultFirst['message'], 'Please enter a valid category id', 'To verify input data is invalid' );
                }
            }
            // To verify get all results answer is valid
            $testResultSecond = $this->bgresult->getAll( $categoryId );

            if (is_array($testResultSecond) && isset($testResultSecond['results'])) {

                foreach ($testResultSecond['results'] as $result) {

                    // To verify categoriId return must be equal categoriId input
                    $this->assertEquals($result->bgCategoryId, $categoryId, 'To verify categoriId return must be equal categoriId input');
                }
            }

        } else {

            $this->assertTrue( FALSE, "Cant't verify get all result answers.");
        }

    }

    function testUpdateResults() {

        $result = $this->bgresult->limit(1)->order_by('questionId', 'ASC')->get_all();

        if( !empty($result) ) {

            $questionId           = $result[0]->questionId;
            $answers              = $this->bgquestion->with('answers')->get_by(array('rule !=' => 'LessThan', 'id' => $questionId));
            $dataUpdate['answer'] = $answers->answers[0]->answer;
            $answerId             = $answers->answers[0]->id;
            $categoryId           = $answers->categoryId;

            // To verify update answer results is invalid
            //============================================

            // To verify questionId input return invalid
            $idInvalid = array('', null, 0, -1, 'abc');

            foreach ($idInvalid as $key => $value) {

                $testResultFirst = $this->bgresult->edit( $value ,$dataUpdate );
                if (is_array($testResultFirst) && isset( $testResultFirst['message'])) {


                    $this->assertContains( $testResultFirst['message'], 'Id must be a numeric and greater than zero', 'To verify input data is invalid' );
                }
            }

            // To verify data is empty
            $testResultSecond = $this->bgresult->edit( $questionId , '' );

            if (is_array($testResultSecond) && isset( $testResultSecond['message'])) {

                $this->assertContains( $testResultSecond['message'], 'The answer field is required', 'To verify data is empty ' );
            }

            // To verify update answer results is valid
            //=========================================
            $testResultThird = $this->bgresult->edit($questionId, $dataUpdate);

            if( is_array($testResultThird) && isset( $testResultThird['results']) ) {

                 // To verify questionId return must equal questionId input
                $this->assertEquals( $testResultThird['results'][0]->questionId, $questionId, 'To verify questionId return must equal questionId input' );

                // To verify answerId return must equal answerId input
                $this->assertEquals( $testResultThird['results'][0]->answerId, $answerId, 'To verify answerId return must equal answerId input' );

                // To verify categoryId return must equal categoryId input
                $this->assertEquals( $testResultThird['results'][0]->bgCategoryId, $categoryId, 'To verify categoryId return must equal categoryId input' );

            }

        } else {

            $this->assertTrue( FALSE, "Cant't verify update answers of question");
        }

    }

    function testDeleteResults() {

        $result = $this->bgresult->count_all();
        if ( $result > 0) {

            // To verify delete answer results is invalid
            //============================================
            // To verify questionId input return invalid
            $idInvalid = array('', null, 0, -1, 'abc');

            foreach ($idInvalid as $key => $value) {

                $testResultFirst = $this->bgresult->destroy( $value );

                if (is_array($testResultFirst) && isset( $testResultFirst['message'])) {

                    $this->assertContains( $testResultFirst['message'], 'Id must be a numeric and greater than zero', 'To verify id question is invalid' );
                }
            }

            // To verify delete answer results is valid
            //============================================
            $dataExpected     = $this->bgresult->limit(1)->get_all();
            $questionId       = $dataExpected[0]->questionId;

            $testResultSecond = $this->bgresult->destroy( $questionId );

            if (is_array($testResultSecond) && isset( $testResultSecond['statusCode'] ) && $testResultSecond['statusCode'] == 204) {


                $this->assertEmpty( $testResultSecond[0], 'To verify data return is null ' );

            } else {

                $this->assertTrue( FALSE, "Cant't verify delete answers of question");

            }

        } else {

            $this->assertTrue( FALSE,  "Cant't verify delete answers of question.");
        }

    }

    function testSaveBigGame() {

        $questions = $this->bgquestion->limit(1)->get_all();
        
        if ( !empty( $questions) ) {

            $question = $this->bgquestion->with('answers')->get_by(array('rule !=' => 'LessThan'));
            $id         = $question->id;
            $answer     = $question->answers[0]->answer;
            $answerId   = $question->answers[0]->id;
            $parlayCardId = $question->parlayCardId;

            // update default parlayCardId in BGQuestion table
            $a = $this->CI->db->set('parlayCardId', 1)
                    ->update('BGQuestions');

            // truncate table BGPlayerCard
            $this->bgplayercard->truncate();

            $picksHashKey = "KEY-BigGame21-PlayerCard-playerId-1-" . md5( "picksHash:$answerId" );
            $this->bgplayercard->memcacheInstance->delete( $picksHashKey);

            // add one record to BGPlayerCard
            $result = $this->bgplayercard->add(array( 'playerId' => 1, 'picksHash' => $answerId, 'parlayCardId' => $parlayCardId ));

            // get questions by category id
            $bgQuestions = $this->bgquestion->getAnswerIdByParlayCardId( $parlayCardId );

            // get last id in answer table
            $answerLast = $this->bganswer->limit(1)->order_by('id', 'DESC')->get_all();

            $answerIdNotExit = isset( $answerLast->id ) ? (int)$answerLast->id + 10 : 100000;
            
            $dataExpected = array(
                'playerId'     => 1,
                'picksHash'    => "$answerIdNotExit:No",
                'parlayCardId' => $parlayCardId
                );

            // To verify save big game is invalid
            //===================================

            // To verify data is empty
            
            $testResultFirst = $this->bgplayercard->add( '' );    
            /*
            if (is_array($testResultFirst) && isset( $testResultFirst['message'])) {

                $this->assertContains( $testResultFirst['message'], 'Please enter the required data', 'To verify data is empty ' );
            }*/

            //print_r($testResultFirst); die();
            // To verify data input is invalid
            // To verify playerId invalid
            $playerIdInvalid = array('', NULL, 0, -1, 'abc');
            
            foreach ($playerIdInvalid as $key => $value) {

                $dataIdInvalid             = $dataExpected;
                $dataIdInvalid['playerId'] = $value;

                $testResultSecond          = $this->bgplayercard->add( $dataIdInvalid );

                if (is_array($testResultSecond) && isset( $testResultSecond['message'])) {

                    if ( empty($value) ) {

                        $this->assertContains( $testResultSecond['message'][0], 'The playerId field is required.',  'To verify playerId is invalid' );
                    } else {

                        $this->assertContains( $testResultSecond['message'][0], 'The playerId field must contain a number greater than 0.',  'To verify playerId is invalid' );
                    }
                }
            }

             // To verify parlayCardId invalid
            $bgCategoryIdInvalid = array('', NULL, 0, -1, 'abc');

            foreach ($bgCategoryIdInvalid as $key => $value) {

                $dataIdInvalid                 = $dataExpected;
                $dataIdInvalid['parlayCardId'] = $value;
                $testResultSecond              = $this->bgplayercard->add( $dataIdInvalid );

                if (is_array($testResultSecond) && isset( $testResultSecond['message']) 
                    && isset( $testResultSecond['code'] ) && $testResultSecond['code'] == 2 ) {

                    if ( empty($value) ) {
                        $this->assertContains( $testResultSecond['message'][0], 'The parlayCardId field is required.',  'To verify parlayCardId is invalid' );
                    } else {

                        $this->assertContains( $testResultSecond['message'][0], 'The parlayCardId field must contain a number greater than 0.',  'To verify bgCategoryId is invalid' );
                    }
                }
            }

            // To verify picksHack is empty
            $dataPickhaskInvalid              = $dataExpected;
            $dataPickhaskInvalid['picksHash'] = '';
            $testResultThird                  = $this->bgplayercard->add( $dataPickhaskInvalid );

            if (is_array($testResultThird) && isset( $testResultThird['message'])) {

                $this->assertContains( $testResultThird['message'][0], 'The picksHash field is required.',  'To verify picksHash is invalid' );

            }

            // To verify picksHack is exist on database
            $bgplayercard = $this->bgplayercard->get_all();

            if ( !empty($bgplayercard) ) {

                $picksHash                  = $bgplayercard[0]->picksHash;
                $picksHashExit              = $dataExpected;
                $picksHashExit['playerId']  = $bgplayercard[0]->playerId;
                $picksHashExit['picksHash'] = $picksHash;
                $testResultFourth           = $this->bgplayercard->add( $picksHashExit );

                if( is_array($testResultFourth) && $testResultFourth['code'] = 5 ) {

                    $this->assertContains( $testResultFourth['message'], "Your picks were NOT saved. A duplicate parlay card was submitted.",  'To verify picksHack is exist on database' );
                }

            } else {

                $this->assertTrue( FALSE, 'To verify picksHack is exist on database' );
            }

            // To verify picksHack is not exist ( answer not exist)
            $testResultFifth = $this->bgplayercard->add($dataExpected);

            if( isset($testResultFifth['code'] )&& $testResultFifth['code'] == 6) {

                $this->assertContains($testResultFifth['message'], "Answers in ($answerIdNotExit,No) do not exists", 'To verify picksHack is not exist');
            }

            // To verify save big game is valid
            //=================================
            $dataQuestion = array(
            'categoryId' => 3,
            'question'   => 'Which team will have more rushing yards?',
            'rule'       => 'LessThan',
            'startDate'  => '11-01-2014 00:00:00',
            'endDate'    => '12-01-2014 00:00:00',
            'answer1'    => '34',
            'answer2'    => '50',
            );

            // To verify save big game is valid
            // =================================
            $questionExpected = $this->bgquestion->add($dataQuestion);
            if ( is_object( $questionExpected ) ) {

                $idQuestion = $questionExpected->id;
                $answer1    = (int)($questionExpected->answers[0]->id);
                $answer2    = (int)($questionExpected->answers[1]->id);

                $dataExpected = array(
                    'playerId'     => 1,
                    'picksHash'    => "$answer1:$answer2",
                    'palayCardId' => 3
                    );
                // Increment the sports count event - even if the email doesn't get sent
                $dataLogin = array('email' => 'admin@kizzang.com', 'password' => 123456789, 'deviceId'=>1);
                
                $login = $this->CI->player->login($dataLogin);

                $this->CI->player->setToken( $login['token']);
                $this->gamecount->setToken( $login['token'] );

                $testResultSixth = $this->bgplayercard->add($dataExpected);

                if ( is_object( $testResultSixth ) ) {

                    // To verify playerId return must be equal playerId input
                    $this->assertEquals( (int)$testResultSixth->playerId, $dataExpected['playerId'], 'To verify playerId return must be equal playerId input' );

                    // To verify picksHash return must be equal picksHash input
                    $this->assertEquals( $testResultSixth->picksHash, $dataExpected['picksHash'], 'To verify picksHash return must be equal picksHash input' );

                    // To verify palayCardId return must be equal palayCardId input
                    $this->assertEquals( (int)$testResultSixth->palayCardId, $dataExpected['palayCardId'], 'To verify palayCardId return must be equal palayCardId input' );
                }
            }
        }
        else {

            $this->assertTrue( FALSE, "Cant't verify save game 21. Question not found.");
        }
    }

    function testGetBigGameCurrent() {

        $date = date('Y-m-d');

        //print_r("HERE"); die();
        $game = $this->bgplayercard->get_by(array( 'date_format(dateTime,"%Y-%m-%d")' => array( 'isRaw' => "'$date'" ) ));

        if( !empty($game)) {
            // To verify get big game current is invalid
            // =========================================
            // To verify playerId input is invalid
            $playerIdInvalid = array('', NULL, 0, -1, 'abc');

            foreach ($playerIdInvalid as $key => $value) {

                $testResultFirst = $this->bgplayercard->getCurrent( $value );

                if (is_array($testResultFirst) && isset( $testResultFirst['message'])) {

                    $this->assertContains( $testResultFirst['message'], 'Id player must be a numeric and greater than zero', 'To verify playerId is invalid' );
                } 
            }

            $playerIdExpected = $game->playerId;

            // To verify get big game current is valid
            // =========================================
            $testResultSecond = $this->bgplayercard->getCurrent( $playerIdExpected);

            if( isset($testResultSecond['code']) && $testResultSecond['code'] == 0 ) {

                foreach ($testResultSecond['cards'] as $key => $card) {
                    
                    $dateResults = date('Y-m-d', strtotime( str_replace( '-', '/', $card['dateTime'] )));

                    // To verify playerId return must be equal playerId input
                    // $this->assertEquals($playerIdExpected, $card->playerId, 'To verify playerId return must be equal playerId input ');

                    // To verify datetime return must be equal datetime input
                    $this->assertEquals( $date, $dateResults, 'To verify datetime return must be equal datetime input ');

                }
            }
        } 
        else {

            $this->assertTrue( FALSE, "Cant't verify get big game 21 current.");
        }
    }

    function testAddEvalution() {
        
        // To verify add evalution is invalid
        // ==================================
        // To verify date input is invalid
        $dateInvalid = array('', NULL, 0, -1, 'abc');
        
        foreach ($dateInvalid as $key => $value) {

            $testResultFirst = $this->bgpick->evaluation( $value );

            if (is_array($testResultFirst) && isset( $testResultFirst['message'])) {

                $this->assertContains( $testResultFirst['message'], 'The date field must contain a valid date (m-d-Y)', 'To verify date is invalid' );
            } 
        }

        // To verify add evalution is valid 
        // ==================================
        $pick = $this->bgplayercard->order_by('dateTime', 'DESC')->get_by( array('wins' => 0, 'losses' => 0, 'rank' => 0));

        if ( !empty( $pick ) ) {

            $date = date('m-d-Y', strtotime(str_replace('-', '/', $pick->dateTime)));

            $testResultSecond = $this->bgpick->evaluation($date);

            if ( isset( $testResultSecond['code'] ) &&  $testResultSecond['code'] == 0 ) {

                if ( empty($testResultSecond['winners'])) {

                    $this->assertEmpty($testResultSecond['winners'], 'To verify winners return is null');
                } 
                else {

                    $winners = $testResultSecond['winners'];

                    foreach ($winners as $winner) {

                        //To verify dateTime return is equal dateTime input
                       // $this->assertEquals( $pick->dateTime, $winner['dateTime'], 'To verify dateTime return is equal dateTime input' );
                    }
                }

            } else {

                $this->assertEquals( FALSE, "Cant't verify add Evalition big game 21. Big Game PlayerCard is empty" );

            }

        } else {

            $this->assertEquals( FALSE, "Cant't verify add Evalition big game 21. Big Game PlayerCard is empty" );
        }
    }

}