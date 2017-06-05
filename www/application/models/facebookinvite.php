<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class FacebookInvite extends MY_Model {

    // set table is wheel
    protected $_table = 'FacebookPlayerInvites';

    // set validations rules
    protected $validate = array(
        'friendFacebookId' => array( 
            'field' => 'friendFacebookId', 
            'label' => 'friendFacebookId',
            'rules' => 'required|max_length[766]'
        ),
        'responseId' => array( 
            'field' => 'responseId', 
            'label' => 'responseId',
            'rules' => 'required|max_length[128]'
        ),
    );

    protected $public_attributes = array(
            'id',
            'playerId',
            'friendFacebookId',
            'responseId',
            'dateAdded',
        );

    /**
    * get friend List
    * @param  int $playerId
    * @return array
    */
    public function getFriendList( $playerId, $limit, $offset ) 
    {   
        // check playerId has exists or no
        $player = $this->user->getById( $playerId );

        // in the case error will return
        if ( is_array( $player ) ) 
            return $player;        
			
        $dateCurrent = date( 'Y-m-d' );
        
        $key = "KEY-FacebookInvite-playerId-$playerId-$dateCurrent-$limit-$offset";        

        $friendList = $this->limit( $limit, $offset )->get_many_by( array( 'playerId' => $playerId, 'date_format(dateAdded,"%Y-%m-%d")' => array( 'isRaw' => "'$dateCurrent'" ) ) );
        
        // if not found friend list
        if ( empty( $friendList ) )         
            return array( 'code' => 2, 'message' => 'Friend list not found', 'statusCode' => 200 ); 
                
        $count = $this->count_by( 'playerId', $playerId );
        $list = array();

        if ( $count > 0 ) 
        {    
            foreach ( $friendList as $friend ) 
            {
                $friendFacebookId = $this->user->createDecryptedArray( $friend->friendFacebookId );
                array_push( $list, $friendFacebookId );
            }
        }

        return array ( 'code' => 0, 'friends' => $list, 'statusCode' => 200 );
    }
    
    public function add( $playerId, $data ) 
    {

        // Memcache MUST BE enabled for this routine to run, because otherwise it opens a vulnerability
        // where someone could fill the database
        if ( $this->memcacheEnable ) 
        {
            $dateCurrent = date( 'Y-m-d' );
            $daily_cap_key = "KEY-FacebookInviteDailyCap-playerId-$playerId-$dateCurrent";

            // the first at all, get the result from memcache
            $daily_cap = $this->memcacheInstance->get( $daily_cap_key );

            if ( !$daily_cap ) 
                $daily_cap = 0;            
            
            if ( $daily_cap < 150 ) 
            {
                // Check if the maximum number of invites per day has been reached. This prevents
                // someone from doing 
                $isGranted = $this->user->checkActionOwner( $playerId );

                // if not authentication
                if ( $isGranted !== TRUE ) 
                {
                    return $isGranted;
                }

                // validate data insert 
                if ( empty( $data ) )
                {
                    $result = array( 'code' => 1, 'message' => 'Please enter the required data', 'statusCode' => 200 );                    
                } 
                else 
                {
                     // reset errors messages
                    $this->form_validation->reset_validation();
                    // set data for all field to validation
                    $this->form_validation->set_params( $data );

                    // set rules validation
                    $this->form_validation->set_rules( $this->validate );

                    if ( $this->form_validation->run() === FALSE ) 
                    {
                        $errors = $this->form_validation->validation_errors();
                        // return result errors log
                        $result = array( 'code' => 2, 'message' => $errors, 'statusCode' => 200 );
                    } 
                    else 
                    {
                        $friendFacebookId = $data['friendFacebookId'];
                        
                        // The response will be a string of comma separated facebook ID's
                        $friendArray = explode( ',', $friendFacebookId );
                        
                        // add playerId to data array
                        $data['playerId'] = $playerId;
                        
                        $success = true;
                        
                        foreach ( $friendArray as $friendId ) 
                        {
                            $data['friendFacebookId'] = $this->user->createEncryptedString( $friendId );
                            
                            // set skip_validation = TRUE in 2nd parameter
                            $insertId = $this->insert( $data, TRUE );

                            if ( $insertId ) 
                            {
                                // Increment the daily cap
                                $daily_cap = $daily_cap + 1;
                                $this->user->updateMemcache( $daily_cap_key, $daily_cap);
                            } 
                            else 
                            {
                                // get and log error message
                                $errorMessage = $this->db->_error_message();
                                log_message( 'error', 'Insert Facebook Invite: ' . $errorMessage );

                                $result = array( 'code' => 3, 'message' => $errorMessage, 'statusCode' => 200 );
                            }   
                        }
                        
                        if ( $success )
                        {
                            // get object wheel by id 
                            $result = $this->get( $insertId );
                            $result->friendFacebookId = $friendFacebookId;
                            $result->statusCode = 201;

                            // Reset all keys once new friends have been added
                            $key = "KEY-FacebookInvite-playerId-$playerId-";                            
                        }
                    }
                }
            }
            else
            {
                // return log error when data miss/ invalid
                $result = array( 'code' => 4, 'message' => 'Daily Facebook friend invite limit reached', 'statusCode' => 200 );

            }
        }
        else
        {
            // return log error when data miss/ invalid
            $result = array( 'code' => 5, 'message' => 'Memcache not enabled', 'statusCode' => 200 );

        }
        
        // return object wheel when create new wheel successful
        return $result;
    }

}