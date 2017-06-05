<?php

class GameCountModelTest extends CIUnit_TestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->CI->load->model(array('gamecount', 'player'));

        $this->gamecount = $this->CI->gamecount;
        $this->player    = $this->CI->player;
    }

    public function tearDown()
    {
        parent::tearDown();
    }


    /**
     *  testAddGameCount 
     *  Function testing create gameCounts of player
     * 
     */
    function testAddGameCount() {
       
        // To verify player add new gameCount is invalid
        // =============================================
        // To verify player id input is invalid
        $playerIdInvalid = array(null, '', 'abc', 0, -123);
        foreach ($playerIdInvalid as $key => $value) {
            
            $data          = array();
            $testResutlOne = $this->gamecount->add($value, $data);

            if ( is_array($testResutlOne) && isset($testResutlOne['message'])) {

                $this->assertContains($testResutlOne['message'], "Id must be a numeric and greater than zero");
            }
        }

        // To verify data input is invalid
        $dataInvalid = array(null, '');

        foreach ($dataInvalid as $value) {
            
            $testResutlSecond = $this->gamecount->add(13, $data);

            // To verify if user is not login before create newGameCount
            if ( is_array($testResutlSecond) && isset($testResutlSecond['message']) ) {

                $this->assertContains($testResutlSecond['message'], "Not authorized", "To verify if user is not login before create newGameCount");
            }

        }

        foreach ($dataInvalid as $value) {
            $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);

            $login = $this->player->login($dataLogin);
            if (is_array($login) && isset($login['token']) ) {
                
                $this->player->setToken( $login['token'] );
                $playerId = $login['playerId'];
                $testResutlThird = $this->gamecount->add($playerId, $value);

                if (is_array($testResutlThird) && isset($testResutlThird['error']) ) {

                    $this->assertContains($testResutlThird['error'], "The gameType is required", "To verify data input is invalid");
                }
            } 
        }    
        
        // To verify player add new gameCount is valid
        // =============================================
        $dataLogin        = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);
        $data['gameType'] = "ABC";
        $login            = $this->player->login($dataLogin);
        
        if (is_array($login) && isset($login['token']) ) {
            
            $this->gamecount->setToken( $login['token']);
            $this->player->setToken( $login['token'] );
            $playerId  = $login['playerId'];
            $gameCount = $this->gamecount->getByPlayerId($playerId, null);

            if (is_array($gameCount) && isset($gameCount['gameCounts'])) {

                $gameCounts = [];
                $gameCounts = $gameCount['gameCounts'];

                foreach ( $gameCounts as $value) {

                    if ($value->gameType === "ABC") {

                        $count = $value->count;

                    } else {

                        $count = 0;
                    }
                }
                
            } else {

                $count = 0;
            } 
            
            $testResutlFourth = $this->gamecount->add($playerId, $data);
            
            if (is_object($testResutlFourth) && isset($testResutlFourth) ) {

                // To verify game count return is must be count + 1
                // $this->assertEquals((int)$testResutlFourth->count, (int)($count + 1), "To verify game count return is must be count + 1");

                // To verify name type must be is "ABC"
                $this->assertEquals($testResutlFourth->gameType, "ABC", "To verify name type must be is 'ABC'");

            }

        }    

    }

}