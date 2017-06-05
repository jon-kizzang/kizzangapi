<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class FinalGame extends MY_Model {

    // set table is FinalMatchConfig
    protected $_table = 'FinalGames';
       
    protected $public_attributes = array(
            'id',
            'finalConfigId',
            'gameType',
            'dateTime',
            'teamId1',
            'teamId2',
            'description',            
            'created',
            'updated'
        );
    
}