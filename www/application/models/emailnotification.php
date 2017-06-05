<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class EmailNotification extends MY_Model {

    // set table is EmailNotifications
    protected $_table = 'EmailNotifications';

    // set validations rules
    protected $validate = array(

        'notificationType' => array( 
            'field' => 'notificationType', 
            'label' => 'Notification Type',
            'rules' => 'required'
        ),
        // 'bounceType' => array( 
        //     'field' => 'bounceType', 
        //     'label' => 'Bounce Type',
        //     'rules' => 'required'
        // ),
        // 'bounceSubType' => array( 
        //     'field' => 'bounceSubType', 
        //     'label' => 'Bounce Sub Type',
        //     'rules' => 'required'
        // ),
        'bounceRecipients' => array( 
            'field' => 'bounceRecipients', 
            'label' => 'Bounce Recipients',
            'rules' => 'required'
        ),
        'mailTime' => array( 
            'field' => 'mailTime', 
            'label' => 'Mail Time',
            'rules' => 'required'
        ),
    );

    protected $public_attributes = array(
            'id',
            'notificationType',
            'bounceType',
            'bounceSubType',
            'bounceRecipients',
            'mailTime',
            'otherData'
        );
    
    /**
     * get Email Notification from database
     * @param  int $id
     * @return array or object
     */
    protected function getByIdFromDb( $id ) {

        // get object EmailNotification by id from database
        $result = $this->get( $id );

        if ( empty( $result ) ) {

            // return log errors when return empty result
            $error = array( 'code' => 1, 'message' => 'Email Notification Not Found', 'statusCode' => 404 ); 

            return $error;
        }
        else {

            $result->statusCode = 200;

            // return object of Email Notification
            return $result;
        }
    }

    /**
    * get Email Notification by id
    * @param  int $id Email Notification id
    * @return array
    */
    public function getById( $id ) 
    {
        // validate the id.
        if ( ! is_numeric( $id ) || $id <= 0 ) 
            return array( 'code' => 1, 'message' => 'Id must is a numeric and greater than zero', 'statusCode' => 400 );
                   
        $result = $this->getByIdFromDb( $id );

        return $result;
    }

    /**
    * get all Email Notifications
    * @return array
    */
    public function getAll() 
    {
        $emailNotifications = $this->get_all();

        if ( ! empty( $emailNotifications ) )
            $result = array( 'code' => 0, 'notifications' => $emailNotifications, 'statusCode' => 200 );
        else
            $result = array( 'code' => 1, 'message' => 'Email Notification Not Found', 'statusCode' => 404 );

        return $result;
    }

    public function add( $data ) 
    {
        // validate data insert 
        if ( empty( $data ) ) 
            return array( 'code' => 1, 'message' => 'Please the required enter data', 'statusCode' => 400 );
       
       $this->form_validation->reset_validation();
       $this->form_validation->set_params( $data );
       $this->form_validation->set_rules( $this->validate );

       if ( $this->form_validation->run() === FALSE ) 
           return array( 'code' => 2, 'message' => $this->form_validation->validation_errors(), 'statusCode' => 400 );             

        if ( isset( $data['otherData'] ) ) 
            $data['otherData'] = md5( $data['otherData'] );        

        $insertId = $this->insert( $data, TRUE );

        if ( $insertId ) 
        {            
            $result = $this->getById( $insertId );
            $result->statusCode = 201;
        } 
        else 
        {
            $errorMessage = $this->db->_error_message();
            log_message( 'error', 'Insert Email Notification: ' . $errorMessage );
            $result = array( 'code' => 3, 'message' => $errorMessage, 'statusCode' => 400 );
        }   

        return $result;
    }    
}