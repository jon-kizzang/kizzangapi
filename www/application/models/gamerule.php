<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class GameRule extends MY_Model {
	
    // Use for fetching values from the db and updating memcache instead of
    // using memcache directly if a key already exists. Helpful for testing.
    private $testing = FALSE;
	
    protected $_table = 'GameRules';

    protected $public_attributes = array(
        'id',
        'serialNumber',
        'ruleURL',
        'gameType'
        );

    /**
     * getRulesFromDB get rules by game type from database
     * @param  tring $gameSerialNumber 
     * @return array
     */
    public function getGameRulesFromDB( $gameSerialNumber ) 
    {
        $this->primary_key = 'serialNumber';

        $result = $this->get( $gameSerialNumber );

        if ( empty( $result ) ) 
        {
            // return errors Rule not found when result return empty
            $result = array( 'code' => 1, 'message' => 'Rule Not Found', 'statusCode' => 404 );

        } 
        else 
        {
            unset($result->id);
            $result->statusCode = 200;
        }

        return $result;
    }
    
    /**
     * getRulesFromDB get rules by game type from database
     * @param  tring $gameSerialNumber 
     * @return array
     */
    public function getGameRulesCatFromDB( $gameType, $limit, $offset ) 
    {       

        $result = $this->db->select( 'serialNumber, ruleURL, name' )
                            ->from('GameRules')
                            ->where( 'gameType', $gameType )
                            ->where('serialNumber <>', 'TEMPLATE')
                            ->where('endDate >= ', date('Y-m-d'))
                            ->where('startDate <= ', date('Y-m-d'))
                            ->order_by('startDate', 'DESC')                            
                            ->offset($offset)
                            ->get()->result();       

        if ( empty( $result ) )         
            $result = array( 'code' => 1, 'message' => 'Rule Not Found Category ' . $gameType, 'statusCode' => 404 );       
        else         
            $result['statusCode'] = 200;        

        return $result;
    }

    public function getGameRules( $gameSerialNumber ) 
    {
        if ( is_null( $gameSerialNumber ) ) 
            return array( 'code' => 1, 'message' => 'GameSerialNumber must be not null', 'statusCode' => 400 );            
        
        $result = $this->getGameRulesFromDB( $gameSerialNumber );        

        // if not enabled caching, just return the data form database.
        return $result;
    }
    
    public function getGameRulesCat( $gameType, $limit, $offset) 
    {        
        if ( is_null( $gameType ) ) 
            return array( 'code' => 1, 'message' => 'GameSerialNumber must be not null', 'statusCode' => 400 );            
                
        return $this->getGameRulesCatFromDB( $gameType );        
    }

    protected function getAllRuleFromDB( $limit, $offset ) 
    {
        $rules = $this->limit( $limit, $offset )->get_all();

        if ( empty( $rules ) )
            $result = array( 'code' => 1, 'message' => 'Rule Not Found', 'statusCode' => 404 );       
        else 
            $result = array('code' => 0, 'count' => count($rules), 'rules' => $rules, 'limit' => (int)$limit, 'offset' => (int)$offset, 'statusCode' => 200 );        

        return $result;
    } 

    public function getAll( $limit, $offset ) 
    {
        return $this->getAllRuleFromDB( $limit, $offset );        
    }
}