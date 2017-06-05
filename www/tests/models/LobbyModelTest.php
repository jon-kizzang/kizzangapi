<?php

/**
 * @group Model
 */
class LobbyModelTest extends CIUnit_TestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->CI->load->model(array('lobby'));
        $this->lobby = $this->CI->lobby;

    }

    public function tearDown()
    {
        parent::tearDown();
    }

    function testAddLobby() {

        $data = array(
            'gameTypeId'  => 3,
            'name'        => 'BigGame Question',
            'model'       => 'TEST',
            );
        // To verify add question is invalid
        // To verify data is empty
        $dataInvalid     = '';
        $testResultFirst = $this->lobby->add( $dataInvalid );

        if (is_array($testResultFirst) && isset($testResultFirst['message']) ) {

            $this->assertContains( $testResultFirst['message'], 'Please enter the required data', 'To verify data is empty');
        }

        // To verify catagoryId is invalid
        $gameTypeIdInvalid = array('', null, 0, -1);

        foreach ($gameTypeIdInvalid as $value) {

            $gameTypeId               = $data;
            $gameTypeId['gameTypeId'] = $value;
            $testResultSecond         = $this->lobby->add( $gameTypeId );

            if( isset($testResultSecond['code']) && $testResultSecond['code'] == 2) {

                $this->assertContains( $testResultSecond['message'][0], 'The gameTypeId field is required.', 'To verify gameTypeId is invalid' );
            }
        }

        // To verify question is invalid
        $nameInvalid         = $data;
        $nameInvalid['name'] = '';
        $testResultThird     = $this->lobby->add( $nameInvalid );

        if( is_array($testResultThird) && isset($testResultThird['message'])) {

                $this->assertContains( $testResultThird['message'][0], 'The name field is required.', 'To verify name is invalid' );
        }

        // To verify add question is valid
        $testResultFifth = $this->lobby->add( $data );
        if ( $testResultFifth['code'] == 0 ) {

            // To verify name return must equal name from input
            $this->assertEquals($testResultFifth['lobby'], $data['name'], 'To verify name return must equal name from input');

        } else {

            $this->assertTrue(FALSE, " Cant't verify add lobby is case valid.");
        }

    }

    function testUpdateLobby() {

        $lobby = $this->lobby->limit(1)->get_by(array( 'gameTypeId !=' => 0 ));

        $dataUpdate = array(

            'gameTypeId' => 2,
            'name'       => 'Game Update',
        );

        // To verify Update lobby is invalid
        if ( !empty($lobby) ) {

            $id = $lobby->id;

            // To verify data is empty
            $dataInvalid     = '';
            $testResultFirst = $this->lobby->edit( $id, $dataInvalid );

            if (is_array($testResultFirst) && isset($testResultFirst['message']) ) {

                $this->assertContains( $testResultFirst['message'], 'Please enter the required data', 'To verify data is empty');
            }

            // To verify catagoryId is invalid
            $idInvalid = array('', null, 0, -1);

            foreach ($idInvalid as $value) {

                $testResultSecond       = $this->lobby->edit( $value, $dataUpdate );

                if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

                    $this->assertContains( $testResultSecond['message'][0], 'Id must be a numeric and greater than zero', 'To verify id is invalid' );
                }
            }

            // To verify name is invalid
            $nameInvalid         = $dataUpdate;
            $nameInvalid['name'] = '';
            $testResultThird     = $this->lobby->edit( $id, $nameInvalid );

            if( is_array($testResultThird) && isset($testResultThird['message'])) {

                $this->assertContains( $testResultThird['message'][0], 'The name field is required.', 'To verify name is invalid' );
            }

            // To verify catagoryId is invalid
            $gameTypeIdInvalid = array('', null, 0, -1);

            foreach ($gameTypeIdInvalid as $value) {

                $gameTypeId               = $dataUpdate;
                $gameTypeId['gameTypeId'] = $value;
                $testResultFourth         = $this->lobby->edit( $id, $gameTypeId );

                if( is_array($testResultFourth) && isset($testResultFourth['message'])) {

                    if ( !empty( $value ) ) {

                        $this->assertContains( $testResultFourth['message'][0], 'The gameTypeId field must contain a number greater than 0.', 'To verify catagoryId is invalid' );

                    } else {

                        $this->assertContains( $testResultFourth['message'][0], 'The gameTypeId field is required.', 'To verify catagoryId is invalid' );

                    }
                }
            }

            // To verify Update lobby is valid
            $testResultFifth = $this->lobby->edit( $id, $dataUpdate );

            if ( $testResultFifth['code'] == 0 ) {

                // To verify name return must equal name from input
                $this->assertEquals($testResultFifth['lobby'], $dataUpdate['name'], 'To verify name return must equal name from input');

            } else {

                $this->assertTrue(FALSE, " Cant't verify add lobby is case valid.");
            }

        }         


    }

    function testgetAllLobbys() {

        $getLobbys = $this->lobby->limit(3)->get_all();

        $offsetInvalid = array(null, '', 0, 'abc');
        $limitInvalid  = array(null, '', 0, 'abc');

        // verify offset and limit is invalid
        foreach ($limitInvalid as $key => $value) {

            if ( array_key_exists($key, $offsetInvalid) ){

                $testResultFirst = $this->lobby->getAll($value, $value);

                if (is_array($testResultFirst) && isset($testResultFirst['message']))
                    $this->assertContains($testResultFirst['message'], "Lobbys Not Found");
            }
        }

        // In case invalid
        // =========================
        // In case limit is string
        // Testing limit and offset in case is numberic
        $limit             = 2;
        $offset            = 0;
        $testResultSeconds = $this->lobby->getAll($limit, $offset);

        if( $testResultSeconds['code'] == 0 ) {

            //lobbys reponse return must be array
            $this->assertTrue(is_array($testResultSeconds['lobbys']));

            //ID lobby return must be equal ID when offset is zero
            if (isset($getLobby[0]->id)) {

                $this->assertEquals($testResultSeconds['lobbys'][0]->id, $getLobby[0]->id);
            }

            // Testing ID return follow value offset
            $offset = 2;
            $testResultThird = $this->lobby->getAll($limit, $offset);

            //ID lobby return must be equal ID when offset is 2
            if (isset($getLobby[$offset]->id)) {

                $this->assertEquals($testResultThird['lobbys'][0]->id, $getLobby[$offset]->id);
            }

        } elseif( $testResultSeconds['code'] == 1 )   {

            $this->assertTrue( FALSE, $testResultSeconds['message']);
        }

    }

    function testGetByIdLobbys() {

        // To verify get lobby by id is invalid
        $idInvalid  = array(null, '', 0, 'abc');

        // verify type Id is invalid
        foreach ($idInvalid as $key => $value) {

            $testResultFirst = $this->lobby->getById($value);

            if ( $testResultFirst['code'] == 1 ){

                $this->assertContains($testResultFirst['message'], "Id must be a numeric and greater than zero", "To verify type Id is invalid");
            }
        }

        // To verify get lobby id is valid
        $lobby = $this->lobby->get_by( array('id !=' => 0) );


        if( !empty( $lobby) ) {

            $id = $lobby->id;

            $testResultSecond = $this->lobby->getById( $id );

            if( $testResultSecond['code'] == 0 ) {

                foreach ($testResultSecond['response'] as $key => $value) {
                    
                    if ( $value['name'] == "Big Game 21") {

                        if( isset($value['startTime']) ) {

                        } else {
                            
                            // To verify starTime return is null
                            $this->assertEmpty($value['startTime'], 'To verify startTime return is null');

                        }

                        if( isset($value['endTime']) ) {

                        } else {
                            
                            // To verify endTime return is null
                            $this->assertEmpty($value['endTime'], 'To verify endTime return is null');

                        }

                    }  elseif ( $value['name'] == "Final 3" ) {

                        if( isset($value['startTime']) ) {

                        } else{

                            //To verify endTime return is null
                            $this->assertEmpty($value['startTime'], 'To verify starTime return is null');
                                    
                        }
                        if( isset($value['endTime']) ) {

                        } else {

                            // To verify endTime return is null
                            $this->assertEmpty($value['endTime'], 'To verify endTime return is null');
                        }

                    } elseif ($value['name'] == "Daily Showdown") {
                        
                        if( isset($value['startTime']) ) {

                        } else {

                            // To verify endTime return is null
                            $this->assertEmpty($value['startTime'], 'To verify startTime return is null');

                        }

                        if( isset($value['endTime']) ) {


                        } else {

                            // To verify endTime return is null
                            $this->assertEmpty($value['endTime'], 'To verify endTime return is null');

                        }

                    } 
                } 
            }
        }
    }

    function testDeleteLobbys() {

        $getLobby = $this->lobby->get_by(array('gameTypeId !=' => 0));

        if( !empty($getLobby) ) {
            
            // To verify delete Lobby is invalid
            //===============================
            $id = $getLobby->id;

            // To verify id is invalid
            $idInvalid = array('', null, 0, -1);

            foreach ($idInvalid as $value) {

                $testResultFirst = $this->lobby->destroy( $value );

                if( is_array($testResultFirst) && isset($testResultFirst['message'])) {

                    $this->assertContains( $testResultFirst['message'][0], 'Id must be a numeric and greater than zero', 'To verify id is invalid' );
                }
            }

            // To verify delete Lobby is valid
            //===============================
            $testResultSecond = $this->lobby->destroy( $id );

            if( $testResultSecond['statusCode'] == 204) {

                $this->assertEmpty( $testResultSecond[0], 'To verify return no content when delete lobby' );
            }

        }
    }

}
