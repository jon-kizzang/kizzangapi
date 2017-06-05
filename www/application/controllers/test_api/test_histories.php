<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
/**
 * Testing mapstate 
 * 
 */
class test_histories extends CI_Controller {
  
    function __construct() {
        parent::__construct();

        // loading model map
        $this->load->model( 'history' );

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

    function testAddHistory() {
        $data = array(
            'gameType'=> 1,
            'history' => "history",
            'prizeValue' => "prizeValue",
            'displayToken' => "ABCD"
           );
        // To verify add history return is invalid
        // ========================================
        $player = $this->player->limit(1)->order_by('id', 'DESC')->get_all();
        !empty( $player ) ? $playerIdExit = $player[0]->id : $playerIdExit = 0 ;
        $playerIdNotExit = ($playerIdExit + 1); 

        $playIdInvalid = array( null, 'abc', 0, -1, $playerIdNotExit );
        
        // To verify player is invalid
        foreach ( $playIdInvalid as $key => $value ) {
            
            $testResultFirst = $this->history->add( $value, $data );

            if ( is_array( $testResultFirst ) && isset( $testResultFirst['errors'] ) ) {

                // To verify player Id input is invalid
                $this->unit->run( $testResultFirst['errors'], 'Id must is a numeric and greater than zero', 'To verify add history return is invalid', 'To verify player Id input is invalid' );
                
            } elseif ( is_array( $testResultFirst ) && isset( $testResultFirst['error'] )) {

                //To verify player is not exist
                $this->unit->run( $testResultFirst['error'] , 'Not authorized', "To verify add history return is invalid", "To verify player is invalid" );
            }
        }
        // To verify update player return is valid
        $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);

        $login = $this->player->login($dataLogin);

        $this->player->setToken( $login['token'] );
        // To verify data is invalid
        // To verify data is empty
        $dataEmpty = '';

        $testResultSecond = $this->history->add( $playerIdExit, $dataEmpty );
        if ( is_array( $testResultSecond ) && isset( $testResultSecond['errors'] ) ) {

            $this->unit->run( $testResultSecond['errors'], 'Please the required enter data', 'To verify data is invalid' ,'To verify data is empty' );
        }
        // To verify data request is null

        // To verify data gameType is invalid
        $dataInvalid = $data;
        $dataInvalid['gameType'] = '';
        $testResultThird = $this->history->add( $playerIdExit, $dataInvalid );
        if ( is_array( $testResultThird ) && isset( $testResultThird['errors'] ) ) {

            $this->unit->run( $testResultThird['errors'][0], 'The gameType field is required.', 'To verify data is invalid' ,'To verify data is empty' );
        }

        // To verify data history is invalid
        $dataInvalid = $data;
        $dataInvalid['history'] = '';
        $testResultFourth = $this->history->add( $playerIdExit, $dataInvalid );

        if ( is_array( $testResultFourth ) && isset( $testResultFourth['errors'] ) ) {

            $this->unit->run( $testResultFourth['errors'][0], 'The history field is required.', 'To verify data is invalid' ,'To verify data is empty' );
        }
        // To verify prizaValie is invalid
        $dataInvalid = $data;
        $dataInvalid['prizeValue'] = '';

        $testResultFifth = $this->history->add( $playerIdExit, $dataInvalid );

        if ( is_array( $testResultFifth ) && isset( $testResultFifth['errors'] ) ) {

            $this->unit->run( $testResultFifth['errors'][0], 'The prizeValue field is required.', 'To verify data is invalid' ,'To verify data is empty' );
        }
        // To verify displayToken id invalid
        $dataInvalid = $data;
        $dataInvalid['displayToken'] = '';

        $testResultSixth = $this->history->add( $playerIdExit, $dataInvalid );

        if ( is_array( $testResultSixth ) && isset( $testResultSixth['errors'] ) ) {

            $this->unit->run( $testResultSixth['errors'][0], 'The displayToken field is required.', 'To verify data is invalid' ,'To verify data is empty' );
        }

        // To verify add history return is valid
        // ========================================
        $testResultSeventh = $this->history->add( $playerIdExit, $data );
            
        if ( is_object( $testResultSeventh ) && isset( $testResultSeventh->id ) ) {

            // To verify playerId return must be equal playerId input
            $this->unit->run((int)$testResultSeventh->playerId, (int)$playerIdExit, "To verify playerId return must be equal playerId input" ,"To verify add history return is valid");

            // To verify gameType return must be equal gameType input data
            $this->unit->run((int)$testResultSeventh->gameType, $data['gameType'] , "To verify gameType return must be equal gameType input data" ,"To verify add history return is valid");

            // To verify history return must be equal history input data
            $this->unit->run( $testResultSeventh->history, $data['history'] , "To verify history return must be equal history input data" ,"To verify add history return is valid");

            // To verify prizeValue return must be equal prizeValue input data
            $this->unit->run( $testResultSeventh->prizeValue, $data['prizeValue'] , "To verify prizeValue return must be equal prizeValue input data" ,"To verify add history return is valid");
            // To verify displayToken return must be equal displayToken input data
            $this->unit->run( $testResultSeventh->displayToken, $data['displayToken'] , "To verify displayToken return must be equal displayToken input data" ,"To verify add history return is valid");

            // To verify isDeleted return must be equal 0
            $this->unit->run( (int)$testResultSeventh->isDeleted, 0 , "To verify isDeleted return must be equal 0" ,"To verify add history return is valid");
        }
        
        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());
    }

    function testGetById() {
        $history = $this->history->count_all();
        if ( $history > 0 ) {

            // To verify get history return is invalid
            // ========================================
            // To verify player is invalid
            $player = $this->player->limit(1)->order_by( 'id', 'DESC' )->get_all();
            $playerIdExit = !empty( $player ) ? $player[0]->id : 0 ;
            $playerIdNotExit = ($playerIdExit + 1); 
            $playIdInvalid = array(null, 'abc', 0, $playerIdNotExit, -1 );
            $id = 1;
            // To verify player is invalid
            foreach ( $playIdInvalid as $key => $value ) {
                
                $testResultFirst = $this->history->getById( $value, $id );

                if ( is_array( $testResultFirst ) && isset( $testResultFirst['errors'] ) ) {

                    // To verify player Id input is invalid
                    $this->unit->run( $testResultFirst['errors'], 'Id must is a numeric and greater than zero', 'To verify add history return is invalid', 'To verify player Id input is invalid' );
                    
                } elseif ( is_array( $testResultFirst ) && isset( $testResultFirst['error'] )) {

                    //To verify player is not exist
                    $this->unit->run( $testResultFirst['error'] , 'Not authorized', "To verify add history return is invalid", "To verify player is invalid" );
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

                    $this->unit->run( $testResultSecond['message'], "Id must is a numeric and greater than zero", "To verify id is invalid", "To verify get history return is invalid" );
                }
            }

            $idTest = $this->db->query( "SELECT * FROM (`History`) WHERE `isDeleted` = 0 AND `playerId` = $playerIdExit LIMIT 1 " )->result();
            if( ! empty( $idTest ) ) {

                 $id = $idTest[0]->id;
                // To verify add history return is valid
                // ========================================
               $testResultThird = $this->history->getById( $playerIdExit, $id );
                    
                if ( is_object( $testResultThird ) && isset( $testResultThird->id ) ) {

                    // To verify playerId return must be equal playerId input
                    $this->unit->run((int)$testResultThird->playerId, (int)$playerIdExit, "To verify playerId return must be equal playerId input" ,"To verify add history return is valid");

                    // To verify gameType return must be equal gameType input data
                    $this->unit->run((int)$testResultThird->gameType, (int)$idTest[0]->gameType , "To verify gameType return must be equal gameType input data" ,"To verify add history return is valid");

                    // To verify history return must be equal history input data
                    $this->unit->run( $testResultThird->history, $idTest[0]->history , "To verify history return must be equal history input data" ,"To verify add history return is valid");

                    // To verify prizeValue return must be equal prizeValue input data
                    $this->unit->run( $testResultThird->prizeValue, $idTest[0]->prizeValue , "To verify prizeValue return must be equal prizeValue input data" ,"To verify add history return is valid");
                    // To verify displayToken return must be equal displayToken input data
                    $this->unit->run( $testResultThird->displayToken, $idTest[0]->displayToken , "To verify displayToken return must be equal displayToken input data" ,"To verify add history return is valid");

                    // To verify isDeleted return must be equal 0
                    $this->unit->run( (int)$testResultThird->isDeleted, 0 , "To verify isDeleted return must be equal 0" ,"To verify add history return is valid");
                }
            } 

            else {
                
                echo "<h4 style='color: red;'> Can't test by  get by ID: {$playerIdExit}. Pls try again.<h4>";

            }
        } 
        else {

            echo "<h4 style='color: red;'> Can't test because database is empty. Pls try again.<h4>";
        }
        
        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());

    }

    function testGetByGameType() {
        $offset = 0;
        $limit = 10;
        $gameType = 1;

        // To verify playerId input is invalid
        $player = $this->player->limit(1)->order_by( 'id', 'DESC' )->get_all();
        $playerIdExit = !empty( $player ) ? $player[0]->id : 0 ;
        $playerIdNotExit = ($playerIdExit + 1); 
        $playIdInvalid = array(null, 'abc', 0, $playerIdNotExit, -1 );

        // To verify player is invalid
        foreach ( $playIdInvalid as $key => $value ) {
            
            $testResultFirst = $this->history->getAll( $playerIdExit, $limit, $offset, $gameType );

            if ( is_array( $testResultFirst ) && isset( $testResultFirst['errors'] ) ) {

                // To verify player Id input is invalid
                $this->unit->run( $testResultFirst['errors'], 'Id must is a numeric and greater than zero', 'To verify get by game type history return is invalid', 'To verify player Id input is invalid' );
                
            } elseif ( is_array( $testResultFirst ) && isset( $testResultFirst['error'] )) {

                //To verify player is not exist
                $this->unit->run( $testResultFirst['error'] , 'Not authorized', "To verify get by game history return is invalid", "To verify player is invalid" );
            }
        }

        // login
        $dataLogin = array( 'email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1 );
        $login = $this->player->login( $dataLogin );
        $this->player->setToken( $login['token'] );

        // To verify get by gameType is invalid
        $gameTypeInvalid = array( 'abc', -1 );
        foreach ($gameTypeInvalid as $key => $value) {
            
            $testResultSecond = $this->history->getAll( $playerIdExit, $limit, $offset, $value );
            if ( is_array( $testResultSecond ) && isset( $testResultSecond['message'] ) ) {

                $this->unit->run( $testResultSecond['message'], 'History Not Found', 'To verify get by game history return is invalid', 'To verify get by gameType is invalid' );
            }
        }
        // To verify offset and limit is invalid
        $offsetInvalid = array( 'abc', -1);

       foreach ($offsetInvalid as $key => $value) {
            
            $testResultThird = $this->history->getAll( $playerIdExit, $limit, $value, $value );
            if ( is_array( $testResultThird ) && isset( $testResultThird['message'] ) ) {

                $this->unit->run( $testResultThird['message'], 'History Not Found', 'To verify get by game history return is invalid', 'To verify offset and limit is invalid' );
            }
        }

        // To verify get by gameType is valid
        $testResultFourth = $this->history->getAll( $playerIdExit, $limit, $offset, $gameType);

        if ( is_array( $testResultFourth ) && isset( $testResultFourth['histories'] ) ) {

            // To verify offset return must be equal offset input
            $this->unit->run( $testResultFourth['offset'], $offset, 'To verify get by gameType is valid',"To verify offset return must be equal offset input" );

            // To verify limit return must be equal limit input
            $this->unit->run( $testResultFourth['limit'], $limit, 'To verify get by gameType is valid',"To verify limit return must be equal limit input" );

            foreach ($testResultFourth['histories'] as $value) {
                
                // To verify playerId return must be equal playerInput
                $this->unit->run( (int)$value->playerId, (int)$playerIdExit, 'To verify get by gameType is valid', 'To verify playerId return must be equal playerInput');

                // To verify gameType return must be equal gameType input
                $this->unit->run( (int)$value->gameType, $gameType, 'To verify get by gameType is valid', 'To verify gameType return must be equal playerInput');
            }
            
        }
        echo $this->unit->report();
        echo $this->returnResult( $this->unit->result() );
    }

    function testGetAll() {

        $offset = 0;
        $limit = 10;
        $gameType = null;

        // To verify playerId input is invalid
        $player = $this->player->limit(1)->order_by( 'id', 'DESC' )->get_all();
        $playerIdExit = !empty( $player ) ? $player[0]->id : 0 ;
        $playerIdNotExit = ($playerIdExit + 1); 
        $playIdInvalid = array(null, 'abc', 0, $playerIdNotExit, -1 );

        // To verify player is invalid
        foreach ( $playIdInvalid as $key => $value ) {
            
            $testResultFirst = $this->history->getAll( $playerIdExit, $limit, $offset, $gameType );

            if ( is_array( $testResultFirst ) && isset( $testResultFirst['errors'] ) ) {

                // To verify player Id input is invalid
                $this->unit->run( $testResultFirst['errors'], 'Id must is a numeric and greater than zero', 'To verify get All history return is invalid', 'To verify player Id input is invalid' );
                
            } elseif ( is_array( $testResultFirst ) && isset( $testResultFirst['error'] )) {

                //To verify player is not exist
                $this->unit->run( $testResultFirst['error'] , 'Not authorized', "To verify get All history return is invalid", "To verify player is invalid" );
            }
        }

        // login
        $dataLogin = array( 'email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1 );
        $login = $this->player->login( $dataLogin );
        $this->player->setToken( $login['token'] );
       
        // To verify offset and limit is invalid
        $offsetInvalid = array( 'abc', -1);

       foreach ($offsetInvalid as $key => $value) {
            
            $testResultThird = $this->history->getAll( $playerIdExit, $limit, $value, $value );
            if ( is_array( $testResultThird ) && isset( $testResultThird['message'] ) ) {

                $this->unit->run( $testResultThird['message'], 'History Not Found', 'To verify get All history return is invalid', 'To verify offset and limit is invalid' );
            }
        }

        // To verify get AllType is valid
        $testResultFourth = $this->history->getAll( $playerIdExit, $limit, $offset, $gameType);

        if ( is_array( $testResultFourth ) && isset( $testResultFourth['histories'] ) ) {

            // To verify offset return must be equal offset input
            $this->unit->run( $testResultFourth['offset'], $offset, 'To verify get All is valid',"To verify offset return must be equal offset input" );

            // To verify limit return must be equal limit input
            $this->unit->run( $testResultFourth['limit'], $limit, 'To verify get All is valid',"To verify limit return must be equal limit input" );

            foreach ($testResultFourth['histories'] as $value) {
                
                // To verify playerId return must be equal playerInput
                $this->unit->run( (int)$value->playerId, (int)$playerIdExit, 'To verify get All is valid', 'To verify playerId return must be equal playerInput');

            }
            
        }
        echo $this->unit->report();
        echo $this->returnResult( $this->unit->result() );
    }

    function testEditHistory() {
        $dataUpdate = array(
            'gameType'=> 2,
            'history' => "history update",
            'prizeValue' => "prizeValue update",
            'displayToken' => "ABCDUPDATE"
           );
        // To verify edit history return is invalid
        // ========================================
        $player = $this->player->limit(1)->order_by('id', 'DESC')->get_all();
        !empty( $player ) ? $playerIdExit = $player[0]->id : $playerIdExit = 0 ;
        $playerIdNotExit = ($playerIdExit + 1); 
        $countHistory = $this->history->count_by( 'playerId', $playerIdExit );

        if ( $countHistory > 0 ) {

            $history = $this->db->query( "SELECT * FROM (`History`) WHERE `isDeleted` = 0 AND `playerId` = $playerIdExit LIMIT 1 " )->result();

            $id = $history[0]->id;
            $playIdInvalid = array( null, 'abc', 0, -1, $playerIdNotExit );
            
            // To verify player is invalid
            foreach ( $playIdInvalid as $key => $value ) {
                
                $testResultFirst = $this->history->edit( $value, $id, $dataUpdate );

                if ( is_array( $testResultFirst ) && isset( $testResultFirst['errors'] ) ) {

                    // To verify player Id input is invalid
                    $this->unit->run( $testResultFirst['errors'], 'Id must is a numeric and greater than zero', 'To verify edit history return is invalid', 'To verify player Id input is invalid' );
                    
                } elseif ( is_array( $testResultFirst ) && isset( $testResultFirst['error'] )) {

                    //To verify player is not exist
                    $this->unit->run( $testResultFirst['error'] , 'Not authorized', "To verify edit history return is invalid", "To verify player is invalid" );
                }
            }
            // To verify update player return is valid
            $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);

            $login = $this->player->login($dataLogin);

            $this->player->setToken( $login['token'] );
            // To verify data is invalid
            // To verify data is empty
            $dataEmpty = '';

            $testResultSecond = $this->history->edit( $playerIdExit, $id, $dataEmpty );
            if ( is_array( $testResultSecond ) && isset( $testResultSecond['errors'] ) ) {

                $this->unit->run( $testResultSecond['errors'], 'Please the required enter data', 'To verify data is invalid' ,'To verify data is empty' );
            }
            // To verify data request is null

            // To verify data gameType is invalid
            $dataInvalid['gameType'] = '';
            $testResultThird = $this->history->edit( $playerIdExit, $id, $dataInvalid );
            if ( is_array( $testResultThird ) && isset( $testResultThird['errors'] ) ) {

                $this->unit->run( $testResultThird['errors'][0], 'The gameType field is required.', 'To verify data is invalid' ,'To verify data is empty' );
            }

            // To verify data history is invalid
            $dataInvalidHistoRy['history'] = '';
            $testResultFourth = $this->history->edit( $playerIdExit, $id, $dataInvalidHistoRy );

            if ( is_array( $testResultFourth ) && isset( $testResultFourth['errors'] ) ) {

                $this->unit->run( $testResultFourth['errors'][0], 'The history field is required.', 'To verify data is invalid' ,'To verify data is empty' );
            }
            // To verify prizaValie is invalid
            $dataInvalidPrize['prizeValue'] = '';

            $testResultFifth = $this->history->edit( $playerIdExit, $id, $dataInvalidPrize );

            if ( is_array( $testResultFifth ) && isset( $testResultFifth['errors'] ) ) {

                $this->unit->run( $testResultFifth['errors'][0], 'The prizeValue field is required.', 'To verify data is invalid' ,'To verify data is empty' );
            }
            // To verify displayToken id invalid
            $dataInvalidToken['displayToken'] = '';

            $testResultSixth = $this->history->edit( $playerIdExit, $id, $dataInvalidToken );
            if ( is_array( $testResultSixth ) && isset( $testResultSixth['errors'] ) ) {

                $this->unit->run( $testResultSixth['errors'][0], 'The displayToken field is required.', 'To verify data is invalid' ,'To verify data is empty' );
            }

            // To verify id invalid 
            $idInvalid = array('abc', null, 0, -1);

            foreach ($idInvalid as $key => $value) {
                
                $testResultSeventh = $this->history->getById( $playerIdExit, $value );
                
                if ( is_array( $testResultSeventh ) && isset( $testResultSeventh['message'] ) ) {

                    $this->unit->run( $testResultSeventh['message'], "Id must is a numeric and greater than zero", "To verify id is invalid", "To verify get history return is invalid" );
                }
            }

            // To verify edit history return is valid
            // ========================================
            $testResultEighth = $this->history->edit( $playerIdExit, $id, $dataUpdate );
            if ( is_object( $testResultEighth ) && isset( $testResultEighth->id ) ) {

                // To verify playerId return must be equal playerId input
                $this->unit->run((int)$testResultEighth->playerId, (int)$playerIdExit, "To verify playerId return must be equal playerId input" ,"To verify edit history return is valid");

                // To verify gameType return must be equal gameType input data
                $this->unit->run((int)$testResultEighth->gameType, $dataUpdate['gameType'] , "To verify gameType return must be equal gameType input data" ,"To verify edit history return is valid");

                // To verify history return must be equal history input data
                $this->unit->run( $testResultEighth->history, $dataUpdate['history'] , "To verify history return must be equal history input data" ,"To verify edit history return is valid");

                // To verify prizeValue return must be equal prizeValue input data
                $this->unit->run( $testResultEighth->prizeValue, $dataUpdate['prizeValue'] , "To verify prizeValue return must be equal prizeValue input data" ,"To verify edit history return is valid");
                // To verify displayToken return must be equal displayToken input data
                $this->unit->run( $testResultEighth->displayToken, $dataUpdate['displayToken'] , "To verify displayToken return must be equal displayToken input data" ,"To verify edit history return is valid");

                // To verify isDeleted return must be equal 0
                $this->unit->run( (int)$testResultEighth->isDeleted, 0 , "To verify isDeleted return must be equal 0" ,"To verify edit history return is valid");
            }
        
        } else {

            echo "<h4 style='color: red;'> Can't test edit history by: {$playerIdExit}. Pls try again.<h4>";
        }
        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());
    }

    function testDeleteHistory() {

        $history = $this->history->count_all();
        if ( $history > 0 ) {

            // To verify delete history return is invalid
            // ========================================
            // To verify player is invalid
            $player = $this->player->limit(1)->order_by( 'id', 'DESC' )->get_all();
            $playerIdExit = !empty( $player ) ? $player[0]->id : 0 ;
            $playerIdNotExit = ($playerIdExit + 1); 
            $playIdInvalid = array(null, 'abc', 0, $playerIdNotExit, -1 );
            $id = 1;
            // To verify player is invalid
            foreach ( $playIdInvalid as $key => $value ) {
                
                $testResultFirst = $this->history->getById( $value, $id );

                if ( is_array( $testResultFirst ) && isset( $testResultFirst['errors'] ) ) {

                    // To verify player Id input is invalid
                    $this->unit->run( $testResultFirst['errors'], 'Id must is a numeric and greater than zero', 'To verify add history return is invalid', 'To verify player Id input is invalid' );
                    
                } elseif ( is_array( $testResultFirst ) && isset( $testResultFirst['error'] )) {

                    //To verify player is not exist
                    $this->unit->run( $testResultFirst['error'] , 'Not authorized', "To verify add history return is invalid", "To verify player is invalid" );
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

                    $this->unit->run( $testResultSecond['message'], "Id must is a numeric and greater than zero", "To verify id is invalid", "To verify get history return is invalid" );
                }
            }

            $idTest = $this->db->query( "SELECT * FROM (`History`) WHERE `isDeleted` = 0 AND `playerId` = $playerIdExit LIMIT 1 " )->result();
            if( ! empty( $idTest ) ) {

                 $id = $idTest[0]->id;

                // To verify delete history return is valid
                // ========================================
               $testResultThird = $this->history->destroy( $playerIdExit, $id );
               
               $this->unit->run($testResultThird[0], null, "To verify delete history return is valid", "To verify result is null");              

            } 

            else {
                
                echo "<h4 style='color: red;'> Can't test delete by playerId: {$playerIdExit}. Pls try again.<h4>";

            }
        } 
        else {

            echo "<h4 style='color: red;'> Can't test because database is empty. Pls try again.<h4>";
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