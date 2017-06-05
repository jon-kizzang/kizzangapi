<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class FinalAnswer extends MY_Model {

    // set table is FinalMatchConfig
    protected $_table = 'FinalAnswers';
    
    protected $token;
       
    protected $validate = array(

        'playerId' => array( 
            'field' => 'playerId', 
            'label' => 'Player ID',
            'rules' => 'required|numeric'
        ),        
        'finalConfigId' => array( 
            'field' => 'finalConfigId', 
            'label' => 'finalConfigId',
            'rules' => 'required|numeric'
        ),
        'answerHash' => array( 
            'field' => 'answerHash', 
            'label' => 'Answer Hash',
            'rules' => 'required'
        ),
    );
    
    protected $public_attributes = array(
            'id',
            'playerId',
            'finalConfigId',
            'answerHash',            
            'created',
            'updated'
        );
    
    public function setToken( $token ) 
    {
        $this->token = $token;
    }
    
    public function save($data)
    {        
        $this->form_validation->reset_validation();
        
        $this->form_validation->set_params( $data );
        
        $this->form_validation->set_rules( $this->validate );
        
        if ( $this->form_validation->run() === FALSE ) 
            return array( 'code' => 1, 'message' => $this->form_validation->validation_errors(), 'statusCode' => 200 );        
        
        $rec = $this->get_by(array('playerId' => $data['playerId'], 'finalConfigId' => $data['finalConfigId'], 'answerHash' => $data['answerHash']));
        if($rec)
            return array('code' => 3, 'message' => 'Duplicate Entry', 'statusCode' => 200);
        
        $insertId = $this->insert($data);
        
        $this->load->model("gamecount");
        $this->gamecount->setToken( $this->token );
        $this->user->setToken( $this->token );

        $playerId = $data['playerId'];
        $countData = array( 'gameType' => 'SportsEvent', 'foreignId' => $data['finalConfigId'], 'maxGames' => 10);        
        $countResponse = $this->gamecount->add( $playerId, $countData);
        
        if ( is_array($countResponse ) )          
            return array( 'code' => 2, 'message' => 'Unable to increment game count', 'statusCode' => 200 );
        
        $this->load->model("chedda");        
        $rec = array('serialNumber' => sprintf("KF%05d", $data['finalConfigId']), 'entry' => $insertId);
        $this->chedda->addEventNotification($playerId, $rec, 'Final3' );
        
        return array( 'code' => 0, 'message' => 'Entry Saved', 'statusCode' => 201 );
    }    
}