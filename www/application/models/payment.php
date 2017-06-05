<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Payment extends MY_Model {

    // set validations rules
    protected $validate = array(

        // verify name must be required
        'paypalEmail' => array( 
            'field' => 'paypalEmail', 
            'label' => 'paypalEmail',
            'rules' => 'required|valid_email|trim'
        ),
    );

    public function payInfo( $playerId ) {

        // check playerId has exists or no
        $player = $this->user->getById( $playerId );

        // in the case is existsed player
        if ( is_object( $player ) ) {

            $player = array( 'code' => 0, 'accountData' => $player->accountData, 'email' => $player->accountEmail, 'payPal' => $player->payPal, 'profileComplete' => $player->profileComplete, 'statusCode' => 200 );

        }

        return $player;
    }

    /**
     * Update PayPal Info
     * @param  array $data
     * @return [type]       [description]
     */
    public function payUpdate( $playerId, $data ) {

        // check playerId has exists or no
        $player = $this->user->getById( $playerId );

        // in the case error will return
        if ( is_array( $player ) ) 
            return $player;        

        $paypalInfo = array( 'code' => 1, 'message' => 'Underfined message', 'statusCode' => 400 );
        $regex = "^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$";

        if ( !isset($data['paypalEmail']) )
        {
            return array( 'code' => 2, 'message' => 'Please enter the required data', 'statusCode' => 400 );
        }
        else 
        {           
            if(!filter_var($data['paypalEmail'], FILTER_VALIDATE_EMAIL) || !$data['paypalEmail'])
                    return array( 'code' => 3, 'message' => 'Invalid Email Address', 'statusCode' => 400 );
            
            $payPal = $this->user->createEncryptedString( $data['paypalEmail'] );

            $isUpdated = $this->user->update( $playerId, array( 'payPal' => $payPal ), TRUE );

            $result = $this->user->getByIdFromDb( $playerId );

            if ( is_object( $result ) ) 
            {
                if ( $this->memcacheEnable ) 
                {
                    // update cache getById player
                    $key = "KEY-Player-playerId-$playerId" . md5( "getOne_get-player_id:$playerId" );
                    $this->user->updateMemcache( $key, $result );
                }

                $accountData = array_filter( $result->accountData );

                $paypalInfo = array('code' => 0, 'accountData' => $accountData, 'payPalEmail' => $result->payPal, 'statusCode' => 200  );
            }
            else 
            {
                $paypalInfo = array('code' => 4, 'payPal' => 'A8A8A8', 'statusCode' => 200 );
            }

            return $paypalInfo;            
        }

        return $paypalInfo;
    }
}