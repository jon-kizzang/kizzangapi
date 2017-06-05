<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class FinalConfig extends MY_Model {

    // set table is FinalMatchConfig
    protected $_table = 'FinalConfigs';
   
    // set validations rules
    protected $validate = array(

        'id' => array( 
            'field' => 'parlayCardId',
            'label' => 'parlayCardId',
            'rules' => 'required|greater_than[0]'
        ),
        'sportCategoryId' => array( 
            'field' => 'sportCategoryId', 
            'label' => 'sportCategoryId',
            'rules' => 'required|greater_than[0]'
        ),
        'serialNumber' => array( 
            'field' => 'serialNumber', 
            'label' => 'Serial Number',
            'rules' => 'required'
        ),
        'startDate' => array( 
            'field' => 'startDate', 
            'label' => 'startDate',
            'rules' => 'required|valid_date'
        ),
        'endDate' => array( 
            'field' => 'startDate', 
            'label' => 'startDate',
            'rules' => 'required|valid_date'
        ),
    );

    protected $public_attributes = array(
            'id',
            'startDate',
            'endDate',
            'serialNumber',
            'prizes',
            'theme',
            'picksHash',
            'sportCategoryId',
            'created',
            'updated'
        );

    public function getAllByDate($date, $playerId)
    {
        $this->load->model('finalgame');
        $this->load->model('finalanswer');
        
        $rs = $this->db->query("Select * from FinalConfigs where ? between startDate and endDate", array($date));
        
        if(!$rs->num_rows())
            return array('code' => 1, 'errorCode' => 'No current Configs', 'statusCode' => 200);                
        
        return array('code' => 0, 'games' => $rs->result(), 'statusCode' => 200);
    }
    
    public function getOne($id)
    {
        $this->load->model('finalgame');
        $this->load->model('parlayteam');
        $game = $this->get_by(array('id' => $id));
        if(!$game)
            return array('code' => 1, 'errorCode' => 'Invalid Game ID', 'statusCode' => 200);
        
        $rs = $this->db->query("Select * from FinalGames where finalConfigId = ?", array($id));
        
        if(!$rs->num_rows())
            return array('code' => 2, 'errorCode' => 'No Current Games', 'statusCode' => 200);
        
        $temp = $rs->result();
        foreach($temp as &$row)
        {
            $team1 = $this->parlayteam->get_by(array('sportCategoryId' => $game->sportCategoryId, 'id' => $row->teamId1));
            if($team1)
                $row->teamName1 = $team1->name;
            
            $team2 = $this->parlayteam->get_by(array('sportCategoryId' => $game->sportCategoryId, 'id' => $row->teamId2));
            if($team2)
                $row->teamName2 = $team2->name;
        }
        $game->games = $temp;
        return array('code' => 0, 'game' => $game, 'statusCode' => 200);
    }
    
}