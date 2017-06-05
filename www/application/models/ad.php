<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ad extends MY_Model {

    // set table is wheel
    protected $_table = 'Ads';
    
    protected $public_attributes = array(
            'id',
            'playerId',
            'gameType',
            'theme',
            'type',
            'status'
        );

    protected $validate = array(

        'playerId' => array( 
            'field' => 'playerId', 
            'label' => 'Player ID',
            'rules' => 'required|numeric'
        ),        
        'gameType' => array( 
            'field' => 'gameType', 
            'label' => 'gameType',
            'rules' => 'required'
        ),
        'theme' => array( 
            'field' => 'theme', 
            'label' => 'theme',
            'rules' => 'required'
        ),
        'type' => array( 
            'field' => 'type', 
            'label' => 'type',
            'rules' => 'required'
        ),
        'status' => array( 
            'field' => 'status', 
            'label' => 'status',
            'rules' => 'required'
        )
    );
    
    public function add( $data ) 
    {
        $this->form_validation->reset_validation();        
        $this->form_validation->set_params( $data );
        $this->form_validation->set_rules( $this->validate );
        
        if ( $this->form_validation->run() === FALSE ) 
            return array( 'code' => 1, 'message' => $this->form_validation->validation_errors(), 'statusCode' => 200 );
        
        $this->insert($data);
        return array( 'code' => 0, 'message' => 'Added', 'statusCode' => 200 );
    }

}