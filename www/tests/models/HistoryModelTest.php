<?php

class HistoryModelTest extends CIUnit_TestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->CI->load->model(array('history', 'player'));

        $this->history = $this->CI->history;
        $this->player  = $this->CI->player;

        $this->player->executeTesting = TRUE;
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    function testAddHistory() {

        $data = array(

            'gameType'     => 1,
            'history'      => "history",
            'prizeValue'   => "prizeValue",
            'displayToken' => "ABCD"
           );

        // To verify add history return is invalid
        // ========================================
        $player  = $this->player->limit(1)->order_by('id', 'DESC')->get_all();
        !empty( $player ) ? $playerIdExit = $player[0]->id : $playerIdExit = 0 ;
        $playerIdNotExit = ($playerIdExit + 1);
        $playIdInvalid   = array( null, 'abc', 0, -1, $playerIdNotExit );

        // To verify player is invalid
        foreach ( $playIdInvalid as $key => $value ) {

            $testResultFirst = $this->history->add( $value, $data );
            if ( isset( $testResultFirst['statusCode'] ) && $testResultFirst['statusCode'] == 400 ) {

                // To verify player Id input is invalid
                $this->assertContains( $testResultFirst['message'], 'Id must be a numeric and greater than zero' );

            } elseif ( isset( $testResultFirst['statusCode'] ) && $testResultFirst['statusCode'] == 403) {

                //To verify player is not exist
                $this->assertContains( $testResultFirst['message'] , 'Not authorized', "To verify player is invalid" );
            }
        }

        // To verify update player return is valid
        $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);
        $login     = $this->player->login($dataLogin);

        $this->player->setToken( $login['token'] );
        
        // To verify data is invalid
        // To verify data is empty
        $dataEmpty = '';

        $testResultSecond = $this->history->add( $playerIdExit, $dataEmpty );

        if ( is_array( $testResultSecond ) && isset( $testResultSecond['message'] ) ) {

            $this->assertContains( $testResultSecond['message'], 'Please the required enter data','To verify data is empty' );
        }
        // To verify data request is null

        // To verify data gameType is invalid
        $dataInvalid             = $data;
        $dataInvalid['gameType'] = '';
        $testResultThird         = $this->history->add( $playerIdExit, $dataInvalid );

        if ( is_array( $testResultThird ) && isset( $testResultThird['message'] ) ) {

            $this->assertContains( $testResultThird['message'][0], 'The gameType field is required.','To verify data is empty' );
        }

        // To verify data history is invalid
        $dataInvalid            = $data;
        $dataInvalid['history'] = '';
        $testResultFourth       = $this->history->add( $playerIdExit, $dataInvalid );

        if ( is_array( $testResultFourth ) && isset( $testResultFourth['message'] ) ) {

            $this->assertContains( $testResultFourth['message'][0], 'The history field is required.','To verify data is empty' );
        }

        // To verify prizaValie is invalid
        $dataInvalid               = $data;
        $dataInvalid['prizeValue'] = '';
        $testResultFifth           = $this->history->add( $playerIdExit, $dataInvalid );

        if ( is_array( $testResultFifth ) && isset( $testResultFifth['message'] ) ) {

            $this->assertContains( $testResultFifth['message'][0], 'The prizeValue field is required.','To verify data is empty' );
        }

        // To verify displayToken id invalid
        $dataInvalid                 = $data;
        $dataInvalid['displayToken'] = '';
        $testResultSixth             = $this->history->add( $playerIdExit, $dataInvalid );

        if ( is_array( $testResultSixth ) && isset( $testResultSixth['message'] ) ) {

            $this->assertContains( $testResultSixth['message'][0], 'The displayToken field is required.','To verify data is empty' );
        }

        // To verify add history return is valid
        // ========================================
        $testResultSeventh = $this->history->add( $playerIdExit, $data );

        if ( is_object( $testResultSeventh ) && isset( $testResultSeventh->id ) ) {

            // To verify playerId return must be equal playerId input
            $this->assertEquals((int)$testResultSeventh->playerId, (int)$playerIdExit, "To verify playerId return must be equal playerId input");

            // To verify gameType return must be equal gameType input data
            $this->assertEquals((int)$testResultSeventh->gameType, $data['gameType'] , "To verify gameType return must be equal gameType input data");

            // To verify history return must be equal history input data
            $this->assertEquals( $testResultSeventh->history, $data['history'] , "To verify history return must be equal history input data");

            // To verify prizeValue return must be equal prizeValue input data
            $this->assertEquals( $testResultSeventh->prizeValue, $data['prizeValue'] , "To verify prizeValue return must be equal prizeValue input data");
            // To verify displayToken return must be equal displayToken input data
            $this->assertEquals( $testResultSeventh->displayToken, $data['displayToken'] , "To verify displayToken return must be equal displayToken input data");

            // To verify isDeleted return must be equal 0
            $this->assertEquals( (int)$testResultSeventh->isDeleted, 0 , "To verify isDeleted return must be equal 0");
        }

    }

    function testGetById() {

        $history = $this->history->count_all();

        if ( $history > 0 ) {

            // To verify get history return is invalid
            // ========================================
            // To verify player is invalid
            $player          = $this->player->limit(1)->order_by( 'id', 'DESC' )->get_all();
            $playerIdExit    = !empty( $player ) ? $player[0]->id : 0 ;
            $playerIdNotExit = ($playerIdExit + 1); 
            $playIdInvalid   = array(null, 'abc', 0, $playerIdNotExit, -1 );
            $id              = 1;
            // To verify player is invalid
            foreach ( $playIdInvalid as $key => $value ) {
                
                $testResultFirst = $this->history->getById( $value, $id );

                if ( isset( $testResultFirst['statusCode'] ) && $testResultFirst['statusCode'] == 400  ) {

                    // To verify player Id input is invalid
                    $this->assertContains( $testResultFirst['message'], '"Id must be a numeric and greater than zero', 'To verify add history return is invalid' );
                    
                } elseif (isset( $testResultFirst['statusCode'] ) && $testResultFirst['statusCode'] == 403 ) {

                    //To verify player is not exist
                    $this->assertContains( $testResultFirst['message'] , 'Not authorized', "To verify add history return is invalid", "To verify player is invalid" );
                } elseif (isset( $testResultFirst['statusCode'] ) && $testResultFirst['statusCode'] == 404 ) {

                    // To verify player Id input is invalid
                    $this->assertContains( $testResultFirst['message'], 'Player Not Found', 'To verify add history return is invalid' );
                }
            }

            // login
            $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);

            $login = $this->player->login($dataLogin);

            $this->player->setToken( $login['token'] );

            // To verify id is invalid
            $IdInvalid = array(null, 'abc', 0, -1);

            foreach ($IdInvalid as $key => $value) {
                
                $testResultSecond = $this->history->getById( $playerIdExit, $value );
                
                if ( is_array( $testResultSecond ) && isset( $testResultSecond['message'] ) ) {

                    $this->assertContains( $testResultSecond['message'], "Id must is a numeric and greater than zero", "To verify id is invalid" );
                }
            }

            $idTest = $this->CI->db->query( "SELECT * FROM (`History`) WHERE `isDeleted` = 0 AND `playerId` = $playerIdExit LIMIT 1 " )->result();
            if( ! empty( $idTest ) ) {

                 $id = $idTest[0]->id;
                // To verify add history return is valid
                // ========================================
               $testResultThird = $this->history->getById( $playerIdExit, $id );
                    
                if ( is_object( $testResultThird ) && isset( $testResultThird->id ) ) {

                    // To verify playerId return must be equal playerId input
                    $this->assertEquals((int)$testResultThird->playerId, (int)$playerIdExit, "To verify playerId return must be equal playerId input");

                    // To verify gameType return must be equal gameType input data
                    $this->assertEquals((int)$testResultThird->gameType, (int)$idTest[0]->gameType , "To verify gameType return must be equal gameType input data");

                    // To verify history return must be equal history input data
                    $this->assertEquals( $testResultThird->history, $idTest[0]->history , "To verify history return must be equal history input data");

                    // To verify prizeValue return must be equal prizeValue input data
                    $this->assertEquals( $testResultThird->prizeValue, $idTest[0]->prizeValue , "To verify prizeValue return must be equal prizeValue input data");
                    // To verify displayToken return must be equal displayToken input data
                    $this->assertEquals( $testResultThird->displayToken, $idTest[0]->displayToken , "To verify displayToken return must be equal displayToken input data");

                    // To verify isDeleted return must be equal 0
                    $this->assertEquals( (int)$testResultThird->isDeleted, 0 , "To verify isDeleted return must be equal 0");
                }
            } 

            else {
                
                $this->assertTrue( FALSE,  "Can't test by  get by ID: {$playerIdExit}. Pls try again.");

            }
        } 
        else {

            $this->assertTrue( FALSE, "Can't test because database is empty. Pls try again.");
        }
    }

    function testGetByGameType() {

        $offset          = 0;
        $limit           = 10;
        $gameType        = 1;

        // To verify playerId input is invalid
        $player          = $this->player->limit(1)->order_by( 'id', 'DESC' )->get_all();
        $playerIdExit    = !empty( $player ) ? $player[0]->id : 0 ;
        $playerIdNotExit = ($playerIdExit + 1); 
        $playIdInvalid   = array(null, 'abc', 0, $playerIdNotExit, -1 );

        // To verify player is invalid
        foreach ( $playIdInvalid as $key => $value ) {
            
            $testResultFirst = $this->history->getAll( $value, $limit, $offset, $gameType );

            if ( isset( $testResultFirst['statusCode'] ) && $testResultFirst['statusCode'] == 400 ) {

                // To verify player Id input is invalid
                $this->assertContains( $testResultFirst['message'], 'Id must be a numeric and greater than zero', 'To verify player Id input is invalid' );
                
            } elseif ( isset( $testResultFirst['statusCode'] ) && $testResultFirst['statusCode'] == 403) {

                //To verify player is not exist
                $this->assertContains( $testResultFirst['message'] , 'Not authorized', "To verify player is invalid" );

            } elseif ( isset( $testResultFirst['statusCode'] ) && $testResultFirst['statusCode'] == 404 ) {

                //To verify player is not exist
                $this->assertContains( $testResultFirst['message'] , 'Player Not Found', "To verify player is invalid" );
            }
        }

        // login
        $dataLogin = array( 'email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1 );
        $login     = $this->player->login( $dataLogin );
        $this->player->setToken( $login['token'] );

        // To verify get by gameType is invalid
        $gameTypeInvalid = array( 'abc', -1 );
        foreach ($gameTypeInvalid as $key => $value) {
            
            $testResultSecond = $this->history->getAll( $playerIdExit, $limit, $offset, $value );
            if ( is_array( $testResultSecond ) && isset( $testResultSecond['message'] ) ) {

                $this->assertContains( $testResultSecond['message'], 'History Not Found', 'To verify get by game history return is invalid', 'To verify get by gameType is invalid' );
            }
        }
        // To verify offset and limit is invalid
        $offsetInvalid = array( 'abc', -1);

       foreach ($offsetInvalid as $key => $value) {
            
            $testResultThird = $this->history->getAll( $playerIdExit, $limit, $value, $value );
            if ( is_array( $testResultThird ) && isset( $testResultThird['message'] ) ) {

                $this->assertContains( $testResultThird['message'], 'History Not Found', 'To verify get by game history return is invalid', 'To verify offset and limit is invalid' );
            }
        }

        // To verify get by gameType is valid
        $testResultFourth = $this->history->getAll( $playerIdExit, $limit, $offset, $gameType);

        if ( is_array( $testResultFourth ) && isset( $testResultFourth['histories'] ) ) {

            // To verify offset return must be equal offset input
            $this->assertEquals( $testResultFourth['offset'], $offset,"To verify offset return must be equal offset input" );

            // To verify limit return must be equal limit input
            $this->assertEquals( $testResultFourth['limit'], $limit,"To verify limit return must be equal limit input" );

            foreach ($testResultFourth['histories'] as $value) {
                
                // To verify playerId return must be equal playerInput
                $this->assertEquals( (int)$value->playerId, (int)$playerIdExit, 'To verify playerId return must be equal playerInput');

                // To verify gameType return must be equal gameType input
                $this->assertEquals( (int)$value->gameType, $gameType, 'To verify gameType return must be equal playerInput');
            }
            
        }
    }

    function testGetAll() {

        $offset = 0;
        $limit = 10;
        $gameType = null;

        // To verify playerId input is invalid
        $player          = $this->player->limit(1)->order_by( 'id', 'DESC' )->get_all();
        $playerIdExit    = !empty( $player ) ? $player[0]->id : 0 ;
        $playerIdNotExit = ($playerIdExit + 1); 
        $playIdInvalid   = array(null, 'abc', 0, $playerIdNotExit, -1 );

        // To verify player is invalid
        foreach ( $playIdInvalid as $key => $value ) {
            
            $testResultFirst = $this->history->getAll( $playerIdExit, $limit, $offset, $gameType );

            if ( isset( $testResultFirst['statusCode'] ) && $testResultFirst['statusCode'] == 400 ) {

                // To verify player Id input is invalid
                $this->assertContains( $testResultFirst['message'], 'Id must be a numeric and greater than zero', 'To verify player Id input is invalid' );
                
            } elseif ( isset( $testResultFirst['statusCode'] ) && $testResultFirst['statusCode'] == 403) {

                //To verify player is not exist
                $this->assertContains( $testResultFirst['message'] , 'Not authorized', "To verify player is invalid" );
            } 
        }

        // login
        $dataLogin = array( 'email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1 );
        $login     = $this->player->login( $dataLogin );
        $this->player->setToken( $login['token'] );
       
        // To verify offset and limit is invalid
        $offsetInvalid = array( 'abc', -1);

       foreach ($offsetInvalid as $key => $value) {
            
            $testResultThird = $this->history->getAll( $playerIdExit, $limit, $value, $value );
            if ( is_array( $testResultThird ) && isset( $testResultThird['message'] ) ) {

                $this->assertContains( $testResultThird['message'], 'History Not Found', 'To verify get All history return is invalid', 'To verify offset and limit is invalid' );
            }
        }

        // To verify get AllType is valid
        $testResultFourth = $this->history->getAll( $playerIdExit, $limit, $offset, $gameType);

        if ( is_array( $testResultFourth ) && isset( $testResultFourth['histories'] ) ) {

            // To verify offset return must be equal offset input
            $this->assertEquals( $testResultFourth['offset'], $offset,"To verify offset return must be equal offset input" );

            // To verify limit return must be equal limit input
            $this->assertEquals( $testResultFourth['limit'], $limit,"To verify limit return must be equal limit input" );

            foreach ($testResultFourth['histories'] as $value) {
                
                // To verify playerId return must be equal playerInput
                $this->assertEquals( (int)$value->playerId, (int)$playerIdExit, 'To verify playerId return must be equal playerInput');

            }
            
        }
    }

    function testEditHistory() {
        $dataUpdate = array(
            'gameType'     => 2,
            'history'      => "history update",
            'prizeValue'   => "prizeValue update",
            'displayToken' => "ABCDUPDATE"
           );

        // To verify edit history return is invalid
        // ========================================
        $player          = $this->player->limit(1)->order_by('id', 'DESC')->get_all();
        !empty( $player ) ? $playerIdExit = $player[0]->id : $playerIdExit = 0 ;
        $playerIdNotExit = ($playerIdExit + 1); 
        $countHistory    = $this->history->count_by( 'playerId', $playerIdExit );

        if ( $countHistory > 0 ) {

            $history = $this->CI->db->query( "SELECT * FROM (`History`) WHERE `isDeleted` = 0 AND `playerId` = $playerIdExit LIMIT 1 " )->result();

            $id            = $history[0]->id;
            $playIdInvalid = array( null, 'abc', 0, -1, $playerIdNotExit );
            
            // To verify player is invalid
            foreach ( $playIdInvalid as $key => $value ) {
                
                $testResultFirst = $this->history->edit( $value, $id, $dataUpdate );

                if ( isset( $testResultFirst['statusCode'] ) && $testResultFirst['statusCode'] == 400 ) {

                    // To verify player Id input is invalid
                    $this->assertContains( $testResultFirst['message'], 'Id must be a numeric and greater than zero', 'To verify player Id input is invalid' );
                    
                } elseif ( isset( $testResultFirst['statusCode'] ) && $testResultFirst['statusCode'] == 403) {

                    //To verify player is not exist
                    $this->assertContains( $testResultFirst['message'] , 'Not authorized', "To verify player is invalid" );

                } elseif ( isset( $testResultFirst['statusCode'] ) && $testResultFirst['statusCode'] == 404 ) {

                    //To verify player is not exist
                    $this->assertContains( $testResultFirst['message'] , 'Player Not Found', "To verify player is invalid" );
                }
            }

            // To verify update player return is valid
            $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);

            $login = $this->player->login($dataLogin);

            $this->player->setToken( $login['token'] );
            // To verify data is invalid
            // To verify data is empty
            $dataEmpty        = '';
            $testResultSecond = $this->history->edit( $playerIdExit, $id, $dataEmpty );

            if ( is_array( $testResultSecond ) && isset( $testResultSecond['message'] ) ) {

                $this->assertContains( $testResultSecond['message'], 'Please the required enter data', 'To verify data is invalid' ,'To verify data is empty' );
            }
            // To verify data request is null

            // To verify data gameType is invalid
            $dataInvalid['gameType'] = '';
            $testResultThird         = $this->history->edit( $playerIdExit, $id, $dataInvalid );

            if ( is_array( $testResultThird ) && isset( $testResultThird['message'] ) ) {

                $this->assertContains( $testResultThird['message'][0], 'The gameType field is required.', 'To verify data is invalid' ,'To verify data is empty' );
            }

            // To verify data history is invalid
            $dataInvalidHistoRy['history'] = '';
            $testResultFourth              = $this->history->edit( $playerIdExit, $id, $dataInvalidHistoRy );

            if ( is_array( $testResultFourth ) && isset( $testResultFourth['message'] ) ) {

                $this->assertContains( $testResultFourth['message'][0], 'The history field is required.', 'To verify data is invalid' ,'To verify data is empty' );
            }
            // To verify prizaValie is invalid
            $dataInvalidPrize['prizeValue'] = '';

            $testResultFifth                = $this->history->edit( $playerIdExit, $id, $dataInvalidPrize );

            if ( is_array( $testResultFifth ) && isset( $testResultFifth['message'] ) ) {

                $this->assertContains( $testResultFifth['message'][0], 'The prizeValue field is required.', 'To verify data is invalid' ,'To verify data is empty' );
            }

            // To verify displayToken id invalid
            $dataInvalidToken['displayToken'] = '';

            $testResultSixth                  = $this->history->edit( $playerIdExit, $id, $dataInvalidToken );
            if ( is_array( $testResultSixth ) && isset( $testResultSixth['message'] ) ) {

                $this->assertContains( $testResultSixth['message'][0], 'The displayToken field is required.', 'To verify data is invalid' ,'To verify data is empty' );
            }

            // To verify id invalid 
            $idInvalid = array('abc', null, 0, -1);

            foreach ($idInvalid as $key => $value) {
                
                $testResultSeventh = $this->history->getById( $playerIdExit, $value );
                
                if ( is_array( $testResultSeventh ) && isset( $testResultSeventh['message'] ) ) {

                    $this->assertContains( $testResultSeventh['message'], "Id must is a numeric and greater than zero", "To verify id is invalid" );
                }
            }

            // To verify edit history return is valid
            // ========================================
            $testResultEighth = $this->history->edit( $playerIdExit, $id, $dataUpdate );

            if ( is_object( $testResultEighth ) && isset( $testResultEighth->id ) ) {

                // To verify playerId return must be equal playerId input
                $this->assertEquals((int)$testResultEighth->playerId, (int)$playerIdExit, "To verify playerId return must be equal playerId input");

                // To verify gameType return must be equal gameType input data
                $this->assertEquals((int)$testResultEighth->gameType, $dataUpdate['gameType'] , "To verify gameType return must be equal gameType input data");

                // To verify history return must be equal history input data
                $this->assertEquals( $testResultEighth->history, $dataUpdate['history'] , "To verify history return must be equal history input data");

                // To verify prizeValue return must be equal prizeValue input data
                $this->assertEquals( $testResultEighth->prizeValue, $dataUpdate['prizeValue'] , "To verify prizeValue return must be equal prizeValue input data");
                // To verify displayToken return must be equal displayToken input data
                $this->assertEquals( $testResultEighth->displayToken, $dataUpdate['displayToken'] , "To verify displayToken return must be equal displayToken input data");

                // To verify isDeleted return must be equal 0
                $this->assertEquals( (int)$testResultEighth->isDeleted, 0 , "To verify isDeleted return must be equal 0");
            }
        
        } else {

            $this->assertTrue( FALSE, "Can't test edit history by: {$playerIdExit}. Pls try again.");
        }
    }

    function testDeleteHistory() {

        $history = $this->history->count_all();
        if ( $history > 0 ) {

            // To verify delete history return is invalid
            // ========================================
            // To verify player is invalid
            $player          = $this->player->limit(1)->order_by( 'id', 'DESC' )->get_all();
            $playerIdExit    = !empty( $player ) ? $player[0]->id : 0 ;
            $playerIdNotExit = ($playerIdExit + 1); 
            $playIdInvalid   = array(null, 'abc', 0, $playerIdNotExit, -1 );
            $id              = 1;

            // To verify player is invalid
            foreach ( $playIdInvalid as $key => $value ) {
                
                $testResultFirst = $this->history->getById( $value, $id );

                if ( isset( $testResultFirst['statusCode'] ) && $testResultFirst['statusCode'] == 400 ) {

                    // To verify player Id input is invalid
                    $this->assertContains( $testResultFirst['message'], 'Id must be a numeric and greater than zero', 'To verify player Id input is invalid' );
                    
                } elseif ( isset( $testResultFirst['statusCode'] ) && $testResultFirst['statusCode'] == 403) {

                    //To verify player is not exist
                    $this->assertContains( $testResultFirst['message'] , 'Not authorized', "To verify player is invalid" );

                } elseif ( isset( $testResultFirst['statusCode'] ) && $testResultFirst['statusCode'] == 404 ) {

                    //To verify player is not exist
                    $this->assertContains( $testResultFirst['message'] , 'Player Not Found', "To verify player is invalid" );
                }

            }

            // login
            $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);
            $login     = $this->player->login($dataLogin);

            $this->player->setToken( $login['token'] );

            // To verify id is invalid
            $IdInvalid = array(null, 'abc', 0, -1);

            foreach ($IdInvalid as $key => $value) {
                
                $testResultSecond = $this->history->getById( $playerIdExit, $value );
                
                if ( is_array( $testResultSecond ) && isset( $testResultSecond['message'] ) ) {

                    $this->assertContains( $testResultSecond['message'], "Id must is a numeric and greater than zero", "To verify id is invalid");
                }
            }

            $idTest = $this->CI->db->query( "SELECT * FROM (`History`) WHERE `isDeleted` = 0 AND `playerId` = $playerIdExit LIMIT 1 " )->result();

            if( ! empty( $idTest ) ) {

                 $id = $idTest[0]->id;

                // To verify delete history return is valid
                // ========================================
               $testResultThird = $this->history->destroy( $playerIdExit, $id );
               
               $this->assertEmpty($testResultThird[0], "To verify result is null");              

            } 
        }        
    }
}