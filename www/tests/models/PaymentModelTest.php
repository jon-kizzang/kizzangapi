<?php 
class PaymentModelTest extends CIUnit_TestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->CI->load->model(array('payment', 'player'));

        $this->payment = $this->CI->payment;
        $this->player = $this->CI->player;

        // disable send SQS when run unit test
        $this->player->executeTesting = TRUE;
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    function testPaymentInfo() {

        // To verify get info payment is invalid
        // ========================================
        // To verify player is invalid
        $player          = $this->player->limit(1)->order_by( 'id', 'DESC' )->get_all();
        $playerIdExit    = !empty( $player ) ? $player[0]->id : 0 ;
        $playerIdNotExit = ($playerIdExit + 1); 
        $playIdInvalid   = array(null, 'abc', 0, $playerIdNotExit, -1 );

        // To verify player is invalid
        foreach ( $playIdInvalid as $key => $value ) {
            
            $testResultFirst = $this->payment->payInfo( $value );
            
            if ( isset( $testResultFirst['code']) &&  $testResultFirst['statusCode'] == 400 ) {

                // To verify player Id input is invalid
                $this->assertContains( $testResultFirst['message'], 'Id must be a numeric and greater than zero', 'To verify player Id input is invalid' );
                
            } elseif( $testResultFirst['statusCode'] == 403 ) {

                //To verify player is not exist
                $this->assertContains( $testResultFirst['message'] , 'Not authorized', "To verify player is invalid" );
            } elseif ($testResultFirst['statusCode'] == 404) {

                //To verify player is not exist
                $this->assertContains( $testResultFirst['message'] , 'Player Not Found', "To verify player is invalid" );
            }
        }

        // To verify get info payment is valid
        // To verify update player return is valid
        $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);

        $login     = $this->player->login($dataLogin);

        $this->player->setToken( $login['token'] );
        
        $testResultSecond = $this->payment->payInfo($login['playerId']);

        if ( is_array( $testResultSecond ) && ( isset( $testResultSecond['message'] ) && $testResultSecond['message'] === 'OK' ) ) {

            // To verify email return must be equal email when user login 
            $this->assertEquals( $testResultSecond['email'], $dataLogin['email'], 'To verify email return must be equal email when user login');

            // To verify paypal return must be equal paypal when user login
            $this->assertEquals( $testResultSecond['payPal'], $dataLogin['email'], 'To verify email return must be equal email when user login'); 
        }

    }

    function testUpdatePayment() {

        $data['paypalEmail'] = "admin2@kizzang.com";

        // To verify update info payment is invalid
        // To verify player is invalid
        $player          = $this->player->limit(1)->order_by( 'id', 'DESC' )->get_all();
        $playerIdExit    = !empty( $player ) ? $player[0]->id : 0 ;
        $playerIdNotExit = ($playerIdExit + 1); 
        $playIdInvalid   = array(null, 'abc', 0, $playerIdNotExit, -1 );

        // To verify player is invalid
        foreach ( $playIdInvalid as $key => $value ) {
            
            $testResultFirst = $this->payment->payUpdate( $value , $data);

            if ( isset( $testResultFirst['code'] ) && $testResultFirst['code'] == 1 && $testResultFirst['statusCode'] == 400) {

                // To verify player Id input is invalid
                $this->assertContains( $testResultFirst['message'], 'Id must be a numeric and greater than zero', 'To verify player Id input is invalid' );
                
            } elseif( $testResultFirst['statusCode'] == 404 ) {

                //To verify player is not exist
                $this->assertContains( $testResultFirst['message'] , 'Player Not Found', "To verify player is invalid" );
            } 
        }

        // To verify update player return is valid
        $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);

        $login     = $this->player->login($dataLogin);
        $this->player->setToken( $login['token'] );

        // To verify paypalEmail is invalid
        $paypalEmailInvalid = array('abc', null, 1, 0, -1);
        
        foreach ($paypalEmailInvalid as $key => $value) {

            $dataInvalid['paypalEmail'] = $value;
            $testResultSecond           = $this->payment->payUpdate( $login['playerId'], $dataInvalid );

            if ( is_array( $testResultSecond ) && isset( $testResultSecond['message'] ) ) {

                if ( empty( $value ) ) {

                    $this->assertContains( $testResultSecond['message'][0], 'The paypalEmail field is required.', "To verify paypalEmail is invalid" );
                } else {

                    $this->assertContains( $testResultSecond['message'][0], 'The paypalEmail field must contain a valid email address.', "To verify paypalEmail is invalid" );
                }
            }
        }

        // To verify update info payment is valid
        $testResultThird = $this->payment->payUpdate( $login['playerId'], $data );
        
        if ( is_array($testResultThird) && isset ($testResultThird['message']) && $testResultThird['message'] === 'OK' ) {

            // To verify email return must be equal emal login 
            $this->assertEquals($testResultThird['accountData']['email'], $dataLogin['email'], "To verify email return must be equal emal login");
            
            // To verify paypalEmal return must be equal paypalEmail input 
            $this->assertEquals($testResultThird['payPalEmail'], $data['paypalEmail'], "To verify paypalEmal return must be equal paypalEmail input ");
        }       
        
    }

}
