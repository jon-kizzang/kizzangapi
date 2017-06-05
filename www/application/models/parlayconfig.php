<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ParlayConfig extends MY_Model {

    // set table is ParlayConfig
    protected $_table = 'SportParlayConfig';

    // one final match config will has more parlay cards and parlay places
    protected $has_many = array( 
            'games' => array( 'model' => 'ParlayCard', 'primary_key' => 'parlayCardId' ),
            'place' => array( 'model' => 'ParlayPlace', 'primary_key' => 'parlayCardId' ),
        );

    // set validations rules
    protected $validate = array(

        'parlayCardId' => array( 
            'field' => 'parlayCardId', 
            'label' => 'parlayCardId',
            'rules' => 'required|greater_than[0]'
        ),
        'cardWin' => array( 
            'field' => 'cardWin', 
            'label' => 'cardWin',
            'rules' => 'required'
        ),
        'cardDate' => array( 
            'field' => 'cardDate', 
            'label' => 'cardDate',
            'rules' => 'required|valid_date'
        ),
        'serialNumber' => array( 
            'field' => 'serialNumber', 
            'label' => 'Serial Number',
            'rules' => 'required'
        ),
        'endDate' => array( 
            'field' => 'endDate', 
            'label' => 'endDate',
            'rules' => 'valid_date'
        )
        
    );

    protected $public_attributes = array(
            'id',
            'parlayCardId',
            'cardWin',
            'cardDate',
            'serialNumber',
            'endDate'
        );

    /**
     * get ParlayConfig from database
     * @param  int $id
     * @return array or object
     */
    protected function getByIdFromDb( $id ) 
    {
        // get object ParlayConfig by id from database
        $result = $this->get( $id );

        if ( empty( $result ) ) 
        {
            // return log errors when return empty result
            $error = array( 'code' => 1, 'message' => 'Parlay Config Not Found', 'statusCode' => 404 ); 
            return $error;
        }
        else 
        {
            $result->statusCode = 200;
            // return object of ParlayConfig
            return $result;
        }
    }

    /**
     * get ParlayConfig from database
     * @param  int $id
     * @return array or object
     */
    protected function getByParlayCardIdFromDb( $id ) 
    {
        // get object ParlayConfig by id from database
        $result = $this->get_by( array( 'parlayCardId' => $id ) );

        if ( empty( $result ) ) 
        {
            // return log errors when return empty result
            $error = array( 'code' => 1, 'message' => 'Parlay Config Not Found', 'statusCode' => 404 ); 
            return $error;
        }
        else 
        {
            $result->statusCode = 200;
            // return object of ParlayConfig
            return $result;
        }
    }

    public function getById( $id ) 
    {
        if ( ! is_numeric( $id ) || $id <= 0 )
            return array( 'code' => 1, 'message' => 'Id must is a numeric and greater than zero', 'statusCode' => 400 );            
        
        return $this->getByIdFromDb( $id );
    }

    public function getByParlayCardId( $id ) 
    {
        if ( ! is_numeric( $id ) || $id <= 0 ) 
            return array( 'code' => 1, 'message' => 'Id must is a numeric and greater than zero', 'statusCode' => 400 );
            
        return $this->getByParlayCardIdFromDb( $id );
    }
    
    protected function getAllFromDatabase( $limit, $offset ) 
    {   
        $parlayConfigs = $this->limit( $limit, $offset )->get_all();
        $count = $this->count_all();

        if ( empty( $parlayConfigs ) )         
            return array( 'code' => 1, 'message' => 'Parlay Config Not Found', 'statusCode' => 404 );            
        else 
            return array( 'code' => 0, 'parlayConfigs' => $parlayConfigs, 'limit' => (int)$limit, 'offset' => (int)$offset, 'count' => $count, 'statusCode' => 200 );            
    }
    
    public function getAllById($id)
    {       
        $parlayConfigs = array();

        $config = $this->get_by('parlayCardId', $id);
        if($config)
        {
            $config->cardId = NULL;
            $parlayConfigs[] = $config;
        }

        if ( empty( $parlayConfigs ) ) 
            return array( 'code' => 1, 'message' => 'Parlay Config Not Found', 'statusCode' => 404 );        
        
        foreach($parlayConfigs as &$config)
            $config->cardDate = date("Y-m-d", strtotime($config->endDate));

        return array( 'code' => 0, 'items' => $parlayConfigs, 'statusCode' => 200 );
    }

    public function getAllByDate( $date, $range, $userType ) 
    {        
        $dateConverted = str_replace( '-', '/', $date );
        $dateMysql = date( 'Y-m-d', strtotime( $dateConverted ) );
        $datePlus = date( 'Y-m-d', strtotime( $dateConverted . "+$range days" ) );
        $parlayConfigs = array();
        if($userType == "Guest")
            $type = "'cheddadailyshowdown','sidailyshowdown','sicollegebasketball','ptbdailyshowdown'";
        else
            $type = "'ptbdailyshowdown','sidailyshowdown','sicollegebasketball','cheddadailyshowdown','profootball','collegefootball','profootball2016','collegefootball2016'";
        
        if(!$range)
        {
            $temp = $this->db->query("Select * from SportParlayConfig where cardDate <= ? 
                and type in ($type) 
                and isActive = 1
                and endDate - INTERVAL 15 MINUTE >= ? order by cardWin DESC", array($dateMysql, date('Y-m-d H:i:s')));
          
            foreach($temp->result() as $row)
                $parlayConfigs[] = $row;
        }
        
        if($range)
        {            
            $temp = $this->db->query("Select * from SportParlayConfig where cardDate <= ? 
                and type in ($type) 
                and isActive = 1
                and cardDate <= ? order by cardWin DESC", array($dateMysql, $datePlus));            
        }
        else
        {
            $temp = $this->db->query("Select * from SportParlayConfig where cardDate = ? 
                and type in ($type) 
                and isActive = 1
                and endDate - INTERVAL 15 MINUTE >= ? order by cardWin DESC", array($dateMysql, date('Y-m-d H:i:s')));
        }
        
        $types = $userType == "Guest" ? array('ptbdailyshowdown' => 'ptbdailyshowdown') : array('guestdailyshowdown' => 'guestdailyshowdown');
        foreach($temp->result() as $config)
        {            
            $types[$config->type] = $config->type;
            $parlayConfigs[] = $config;
        }
                
        $rs = $this->db->query("Select * from SportParlayConfig where cardDate = ? 
            and type not in ('profootball','collegefootball','" . implode("','", $types) . "') 
            and isActive = 1
            order by cardWin DESC", array(date("Y-m-d", strtotime("tomorrow"))));
        
        //print $this->db->last_query(); die();

        foreach($rs->result() as $temp)
            $parlayConfigs[] = $temp;

        if ( empty( $parlayConfigs ) )         
                return array( 'code' => 1, 'message' => 'Parlay Config Not Found on date ' . $date, 'statusCode' => 404 );                                    
                        
        return array( 'code' => 0, 'items' => $parlayConfigs, 'statusCode' => 200 );
    }

    /**
    * get all Parlay Configs
    * @param  int $limit
    * @param  int $offset
    * @return array
    */
    public function getAll( $limit, $offset ) 
    {        
        return $this->getAllFromDatabase( $limit, $offset );
    }
}
 