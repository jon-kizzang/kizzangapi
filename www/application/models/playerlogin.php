<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class PlayerLogin extends MY_Model {

    // set table Maps
	protected $_table = 'PlayerLogins';
	protected $public_attributes = array(
                'playerId',
                'lastLogin',
                'loginType',
                'loginSource',
                'mobileType',
                'ipAddress',
                'appId',
                'isRegistration',
                'userAgent'
            );
    
       protected $validate = array(
	        array( 'field' => 'playerId', 
	               'rules' => 'required|greater_than[0]'
	            ),	        
	        array( 'field' => 'lastLogin', 
	               'rules' => 'valid_datetime'
	            ),
                array( 'field' => 'loginType', 
	               'rules' => 'required'
	            ),
                array( 'field' => 'loginSource', 
	               'rules' => 'required'
	            ),
                array( 'field' => 'ipAddress', 
	               'rules' => 'xss_clean|max_length[45]|trim'
	            ),
                array( 'field' => 'isRegistration', 
	               'rules' => 'is_numeric'
	            ),
                array( 'field' => 'appId', 
	               'rules' => 'xss_clean|max_length[10]|trim'
	            ),
                array( 'field' => 'userAgent', 
	               'rules' => 'xss_clean|max_length[2000]|trim'
	            ),
                array( 'field' => 'mobileType', 
	               'rules' => 'xss_clean|max_length[10]|trim'
	            )
        );
       
    public function add($data)
    {
        // validate data insert 
        if ( empty( $data ) ) {

            // return log error when data miss/ invalid
            $errors = array( 'code' => 1, 'message' => 'Please the required enter data', 'statusCode' => 400 );

            return $errors;
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
                $result = array( 'code' => 2, 'message' => $errors, 'statusCode' => 400 );
            } 
            else 
            {                
                // set skip_validation = TRUE in 2nd parameter
                $this->insert( $data, TRUE );

                if ( $errorMessage = $this->db->_error_message()) 
                {

                    // get and log error message                    
                    log_message( 'error', 'Insert PlayerLogin: ' . $errorMessage );

                    $result = array( 'code' => 3, 'message' => json_encode($data), 'statusCode' => 400 );
                }   
                else
                {
                    $result = array( 'code' => 0, 'message' => 'success', 'statusCode' => 200 );
                }
            }
        }

        // return object Winner
        return $result;
    }
}
