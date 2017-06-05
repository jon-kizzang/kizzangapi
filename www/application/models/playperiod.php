<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class PlayPeriod extends MY_Model 
{
    // will call relate and convertData function after get from db
    protected $after_get = array('relate', 'convertData');

    // set table is PlayPeriod
    protected $_table = 'PlayPeriod';
    
    // set rule validation
    protected $validate = array(
        // gamesPlayed is required and greater than zero
        array( 'field' => 'gamesPlayed', 
           'label' => 'Games Playerd',
           'rules' => 'required|greater_than[0]'
        ),
        // gamesPlayed is required and greater than zero
        array( 'field' => 'id', 
           'label' => 'Id',
           'rules' => 'required|greater_than[0]'
        )
    );

   
    function __construct() 
    {

        parent::__construct();
        // load position model
        $this->load->model( 'position' );
    }

    protected function convertData( $playperiod ) 
    {
        $intFields = array( 'id', 'playerId', 'gamesCredit', 'gamesPlayed', 'countMissedDay' );

        if ( isset( $playperiod->startDate ) && $playperiod->startDate ) 
            $playperiod->startDate = date( 'm-d-Y H:i:s', strtotime( $playperiod->startDate ) );

        if ( isset( $playperiod->endDate ) && $playperiod->endDate )         
            $playperiod->endDate = date( 'm-d-Y H:i:s', strtotime( $playperiod->endDate ) );        
        
        if ( isset( $playperiod->status ) && isset( $playperiod->status->name ) && $playperiod->status->name ) 
        {            
            $playperiod->statusId = $playperiod->status->id;
            $playperiod->status = $playperiod->status->name;
        }

        foreach ( $intFields as $field )         
            if ( isset( $playperiod->{$field} ) )
                $playperiod->{$field} = (int)$playperiod->{$field};                    

        return $playperiod;
    }

    public function getPlayPeriodPrevious( $playerId ) 
    {              
        return $this->playperiod->limit( 1 )->get_by( array( 'playerId' => $playerId, 'date(endDate) =' => date( 'Y-m-d', strtotime("yesterday") ) ) );        
    }

    public function getAllPlayPeriodFromDb( $playerId, $limit, $offset, $startDate, $endDate ) 
    {
        $dateFormat = 'Y-m-d H:i:s';
        $startDate = date( $dateFormat, $startDate );
        $endDate = date( $dateFormat, $endDate );

        $results = $this->with('status')->limit( $limit, $offset )->get_many_by( array('playerId' => $playerId, 'startDate >= ' => $startDate, 'endDate <= ' => $endDate ) );

        $count = $this->count_by( array('playerId' => $playerId, 'startDate >= ' => $startDate, 'endDate <= ' => $endDate ) );

        if ( empty($results) )
            return array( 'message' => 'Play Period Not Found', 'statusCode' => 404 );        
        else 
            return array( 'playPeriod' => $results, 'limit' => $limit, 'offset' => $offset, 'count' => $count, 'statusCode' => 200 );        
    }

    public function getAll( $playerId, $limit, $offset, $startDate, $endDate  ) 
    {
        $errors = array();

        $player = $this->checkPlayerId( $playerId );
        if ( is_array( $player ) )
            return $player;        
        
        if ( ! $this->form_validation->valid_datetime( $startDate ) )
            $errors[] = 'Start Date must contain a valid date (m-d-Y H:i:s)';        

        if ( ! $this->form_validation->valid_datetime( $endDate ) )
            $errors[] = 'End Date must contain a valid date (m-d-Y H:i:s)';        

        if ( ! empty( $errors ) )
            return array( 'message' => $errors, 'statusCode' => 400 );        

        $startDateInt = strtotime( str_replace( '-', '/', $startDate ) );
        $endDateInt = strtotime( str_replace( '-', '/', $endDate ) );

        if ( $startDateInt >= $endDateInt )
            return array( 'message' => 'End Date must greater than Start Date', 'statusCode' => 400 );        

        return $this->getAllPlayPeriodFromDb(  $playerId, $limit, $offset, $startDateInt, $endDateInt );

    }

    public function getById( $playerId, $id, $isGranted = FALSE ) 
    {

        if ( ! $isGranted )             
            if ( is_array( $this->user->checkActionOwner( $playerId ) ) )
                return $isValid;                    

        if ( ! is_numeric($id) || $id <= 0 )         
            return array( 'message' => 'Id must is a numeric and greater than zero');        
        
        return $this->getByPlayerIdFromDb( $playerId, $id );        
    }
   
    public function getByPlayerIdFromDb( $playerId, $id = null, $limit = 1, $offset = 0 ) 
    {    
        if ( $id ) 
        {
            $result = $this->with('status')->get_by( array( 'id' => $id, 'playerId' => $playerId ) );
        }
        else 
        {
            $date = date("Y-m-d");         
            if($limit == 1)
                $result = $this->order_by("id", "DESC")->limit( $limit, $offset )->get_by( 'playerId', $playerId, "date(endDate)", $date );
            else
                $result = $this->order_by("id", "DESC")->limit( $limit, $offset )->get_many_by( 'playerId', $playerId );
            
            $count = $this->count_by( 'playerId', $playerId );
        }

        if ( empty( $result ) ) 
        {
            $result = $this->add($playerId);
            if(is_object($result))
                return $result;
            return array( 'message' => 'Play Period Not Found', 'statusCode' => 404 );
        }
        else 
        {
            if (!$id && $limit != 1 ) 
            {
                return array( 'playPeriods' => $result, 'offset' => (int)$offset, 'limit' => (int)$limit, 'count' => $count, 'statusCode' => 200 );
            }
            else 
            {
                $result->statusCode = 200;
                if($this->user->memcacheEnable)
                    $this->user->updateMemcache('Key-PlayPeriod-' . $playerId . "-" . date("Y-m-d"), $result);
                return $result;
            }
        }
    }

    public function getByPlayerId( $playerId, $limit, $offset) 
    {
        $player = $this->checkPlayerId( $playerId );

        if ( is_array( $player ) ) 
            return $player;            

        $result = NULL;
        if($this->user->memcacheEnable)
            $result = $this->user->memcacheInstance->get('Key-PlayPeriod-' . $playerId . "-" . date("Y-m-d"));
        
        if($result)
            return $result;
        
        return $this->getByPlayerIdFromDb( $playerId, null, $limit, $offset );            
    }
       
        public function add( $playerId ) 
        {
            $status = 2;
            $startDate = date( 'Y-m-d H:i:s' );
            $endDate = date( 'Y-m-d 23:59:59' );
            $playDate = date("Y-m-d");
           
            $playPeriod = $this->get_by( array( 'playerId' => $playerId, 'playDate' => $playDate ));            

            if ( $playPeriod ) 
            {
                log_message( 'error', 'PlayPeriod exists in ' . date('Y-m-d') . ' with ' . $playerId );
                $result = array( 'id' => $playPeriod->id, 'message' => 'PlayPeriod exists in ' . date('Y-m-d'), 'statusCode' => 400 );
                return $result;
            }

            $playPeriodData = array(
                    'playerId' => $playerId,
                    'startDate' => $startDate,
                    'playDate' => $playDate,
                    'endDate' => $endDate                    
            );
            
            $this->db->query("Insert IGNORE into PlayPeriod (playerId, startDate, playDate, endDate) values (?, ?, ?, ?)", 
                array($playerId, $startDate, $playDate, $endDate));
            
            $rs = $this->db->query("Select id from PlayPeriod where playerId = ? and playDate = ?", array($playerId, $playDate));
            $insertId = $rs->row()->id;

            if ( $insertId ) 
            {
                $result = $this->getById( $playerId, $insertId, TRUE );
                if($result && is_object($result))
                {
                    $result->missedDays = 0;
                    $result->statusCode = 201;
                    if($this->user->memcacheEnable)
                        $this->user->updateMemcache('Key-PlayPeriod-' . $playerId . "-" . date("Y-m-d"), $result);
                }
                else
                {
                    $result = array( 'message' => "Error validating Player: " . print_r($result, true), 'code' => 1, 'statusCode' => 200 );
                }

            } 
            else 
            {                
                $result = array( 'message' => "Insert Failed to PlayPeriod Table.", 'statusCode' => 400 );
            }

            return $result;
        }

    /**
     * update status play period
     * @param  int $playerId id of player
     * @param  int $id       id of playperiod
     * @param  int $status
     * @return array or object
     */
    public function editStatus( $playerId, $id, $status ) {

        // check exists playerId or no
        $player = $this->checkPlayerId( $playerId );

        // if error will return
        if ( is_array( $player ) )
            return $player;        

        $data = array(
           'id' => $id,
           'status' => $status
          );

        // set rule validattion
        $validate = array(
            // status is numeric and greater than -1
            array( 'field' => 'status', 
                    'label' => 'Status',
                    'rules' => 'greater_than[-1]'
                 ),
            // id is required, numeric and greater than 0
            array( 'field' => 'id', 
                    'label' => 'Id',
                    'rules' => 'required|greater_than[0]'
                 )
        );

        // reset error messages
        $this->form_validation->reset_validation();

        // set form data to validate
        $this->form_validation->set_params( $data );

        // set rules validation
        $this->form_validation->set_rules( $validate );

        // if validation return fail
        if ( $this->form_validation->run() === FALSE ) {

            // get list error
            $errors = $this->form_validation->validation_errors();

            return array( 'message' => $errors, 'statusCode' => 400 );

        }
        else {

            if ( $status > 4 ) {

                return array( 'message' => 'Status must is numeric and in set (0,1,2,3,4)', 'statusCode' => 400 );
            }
            else 
            {

                $playPeriod = $this->getById( $playerId, $id );

                // if exists playperiod by id
                if ( is_object($playPeriod) && $playPeriod->statusCode === 200 ) {

                    $timeNow = strtotime( date( 'm/d/Y H:i:s' ));
                    $endDate = strtotime( str_replace('-', '/', $playPeriod->endDate ));

                    // if player not confirmed email or has deleted 
                    if ( $player->emailVerified == 0 || $player->isDeleted == 1 )
                        $status = 1;                    
                    elseif ( $timeNow >= $endDate )
                        $status = 4;                    
                    elseif ( $playPeriod->gamesPlayed == $playPeriod->gamesCredit )
                        $status = 3;
                    
                    $isUpdated = $this->update( $id, array( 'status' => $status ), TRUE );

                    // if updated the successfully
                    if ( $isUpdated )
                        return $this->getByPlayerIdFromDb( $playerId, $id );                                               
                    else 
                        return array('message' => $errorMessage, 'statusCode' => 400 );

                } 
                else 
                {
                    return $playPeriod;
                }
            }
        }
    }

    public function edit( $playerId, $id, $gamesPlayed = 15 ) 
    {     
        $player = $this->checkPlayerId( $playerId );

        if ( is_array( $player ) ) 
            return $player;        

        $data = array(
            'id' => $id,
            'gamesPlayed' => $gamesPlayed
        );

        $this->form_validation->reset_validation();
        $this->form_validation->set_params( $data );
        $this->form_validation->set_rules( $this->validate );
        
        if ( $this->form_validation->run() === FALSE ) 
            return array( 'message' => $this->form_validation->validation_errors(), 'statusCode' => 400 );
        
        $playPeriod = $this->getById( $playerId, $id );
        $rs = $this->db->query("Select * from Users where id = ?", array($playerId));
        if($rs->num_rows())
            $player = $rs->row();

        if ( is_object($playPeriod) && $playPeriod->statusCode === 200 ) 
        {
            $timeNow = strtotime( date( 'm/d/Y H:i:s' ));
            $endDate = strtotime( str_replace('-', '/', $playPeriod->endDate ));

            if ( $player->accountStatus === 'Deleted' ) 
            {
                $update = array( 'status' => 1 );
            }
            else 
            {
                if ( $timeNow >= $endDate && $playPeriod->statusId != 3 )                 
                    $update = array( 'status' => 4 );                
                elseif ( $gamesPlayed >= $playPeriod->gamesCredit )                 
                    $update = array( 'gamesPlayed' => $gamesPlayed, 'status' => 3 );
                else                 
                    $update = array( 'gamesPlayed' => $gamesPlayed, 'status' => 2 );
            }

            $isUpdated = $this->update( $id, $update, TRUE );

            if ( $isUpdated ) 
            {
                // get playperiod from db
                $playPeriod = $this->getByPlayerIdFromDb( $playerId, $id );               

                // if enable memcache
                if ($this->memcacheEnable) 
                {
                    // update memcache
                    $key = "KEY-Playperiod-playerId-Id-$playerId-$id";
                    $this->user->updateMemcache( $key, $playPeriod );
                }

                return $playPeriod;

            } 
            else 
            {
                // get and log error message
                $errorMessage = $this->db->_error_message();
                return array('message' => $errorMessage, 'statusCode' => 400 );
            }

        } 
        else 
        {
            return $playPeriod;
        }
    }
      
    public function checkPlayerId( $playerId ) 
    {
        return $this->user->getById( $playerId );        
    }
}