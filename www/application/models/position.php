<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Position extends MY_Model {

            // set table is Positions 
	protected $_table = 'Positions';

            // call convert date after get into db
	protected $after_get = array('relate', 'convertData');
	

	/**
	 * convert date mysql to php m-d-Y
	 * @param  object $position
	 * @return object conver date time calendar field 
	 */
            protected function convertData( $position ) {

                $intFields = array( 'id', 'playerId', 'fromPosition', 'startPosition', 'endPosition', 'ruleCode', 'ack' );

                if ( isset( $position->calendarDate ) )

                    // format date to m-d-Y for calendarDate field
                    $position->calendarDate = date( 'm-d-Y', strtotime( $position->calendarDate ) );

                // convert field in intFields array to int
                foreach ( $intFields as $field ) {

                    if ( isset( $position->{$field} ) ) {
                        $position->{$field} = (int)$position->{$field};
                    }
                }

                if ( isset( $position->ack ) && ! (int)$position->ack ) {

                    unset( $position->ack );
                }

                // return positions 
                return $position;
            }

	/**
	 * get current position by player Id
	 * @param  int $playerId id of player
	 * @return array or null
	 */
            public function getCurrent( $playerId ) 
            {
                    $currentDate = date( 'Y-m-d' );
                    $position = NULL;
                    
		$position = $this->get_by( array( 'playerId' => $playerId, 'calendarDate' =>  $currentDate) );

		if ( $position ) 
                     {
                            $this->checkConfigs($playerId, $position);
                            return $position;
		}                     

		return null;
            }
        
	/**
	 * get current position by player Id
	 * @param  int $playerId id of player
	 * @return array or null
	 */
            public function getLast( $playerId ) 
            {
                // get position from db
                $position = $this->limit( 1 )->order_by('calendarDate', 'DESC')->get_by( 'playerId', $playerId );
                
                if ( $position )            
                {
                    $position->dateDiff = 1; //abs(floor((strtotime(str_replace("-", "/", $position->calendarDate)) - strtotime(date("Y-m-d"))) / (24 * 3600)));
                    return $position;                
                }
                return null;
	}
        
	/**
	 * get last position of player
	 * @param  int $playerId id of player
	 * @return int
	 */
            public function getLastPosition( $playerId ) 
            {
                
                $position = $this->getLast( $playerId );
                $lastPosition = ( $position ) ? $position->endPosition : 0;			

                // return last position 
                return $lastPosition;
            }
	
	/**
	 * check position is first of player id or not
	 * @param  int $playerId id of player
	 * @return boolean
	 */
            public function checkPositionIsFirst( $playerId ) 
            {
                return true;
            }

	/**
            * get rule applied by weight
            * @param  array  $weightedValues  array( ruleCode => weight, ruleCode1 => weight1 )
            * @return int ruleCode
            */
            protected function getRuleByWeight( $weightedValues) 
            {

                $rand = mt_rand( 0, 100);
                foreach($weightedValues as $key => $value)
                {
                    $rand -= $value;
                    if($rand < 0)
                    {
                        return $key;
                    }
                }
                return 5;
            }
            
	public function add( $playerId, $playPeriodPreDay ) 
           {
                return array( 'code' => $code, 'message' => 'Position created successfully', 'statusCode' => 201 );                
	}
    
            public function checkConfigs($playerId, &$data)
            {                
                $this->load->model('configs');
                $config = $this->configs->getConfig();
                
                 if(isset($config['Map']) && isset($config['Map']['Days']) && count($config['Map']['Days']))                    
                        foreach($config['Map']['Days'] as $event)                        
                            if(isset($data->startPosition) && $event['day_number'] == $data->startPosition)
                                $this->processDayAction($event, $playerId, $data);                                       
            }
    
            private function processDayAction($event, $playerId, &$data)
            {
                $this->load->model('eventnotification');
                $currentDate = date('Y-m-d');
                $key = "KEY-Position-playerId-Current-$playerId-$currentDate";
                //First check to see if they ever been on this day before
                $dup = $this->get_many_by(array('playerId' => $playerId, 'calendarDate <>' => date('Y-m-d'), 'endPosition' => $event['day_number']));
                if($dup)
                    return true;
                              
                switch($event['action'])
                {
                    case 'sponsor_wheel': $ret = $this->eventnotification->add(
                            array('playerId' => $playerId,
                                'type' => 'wheel',
                                'data' => json_encode(array('wheelId' => 1, 'spinKey' => "KEY-SponsorWheel-$playerId-" . md5(date("Y-m-d H:i:s")), 'notificationEventVersion' => 1))
                                ), 
                            $playerId);
                        break;
                    
                    case 'multiplier': $data->multiplier = (int) $event['multiplier'];
                        $this->db->set( 'multiplier', $event['multiplier'])
                            ->where( 'playerId', $playerId )
                            ->where( 'calendarDate', $currentDate )                            
                            ->update( 'Positions' );
                        $this->user->updateMemcache( $key, $data);
                        break;
                    
                }

                return false;
            }            

	/**
	* get all positions from database
	* @param  int $playerId
	* @param  int $limit
	* @param  int $offset
	* @return array
	*/
	public function getAllFromDatabase( $playerId, $limit, $offset) 
            {
                return array( 'code' => 0, 'positions' => array(), 'offset' => 0, 'limit' => 0, 'count' => 0, 'statusCode' => 200 );                 
	}

	public function getAll( $playerId, $limit, $offset ) 
            {

                // validate player id return
                if ( $error = $this->checkPlayerId( $playerId ) ) 
                    return $error;
		                
                return $this->getAllFromDatabase( $playerId, $limit, $offset );
	}

        public function checkPlayerId( $playerId ) 
        {
            if ( ! is_numeric($playerId) || $playerId <= 0 )                     
                return array( 'code' => 1, 'message' => 'Player Id must is a numeric and greater than zero', 'status' => 400 );

            $count = $this->user->count_by( 'id', $playerId );

            if ( $count === 0 )                         
                return array( 'code' => 2, 'message' => 'Player Id doesn\'t exist', 'status' => 404 );	  		

            return false;
        }

        public function getHighestPosition( $playerId ) 
        {
            return 0;            
        }
	
        public function resetRule( $playerId, $limit, $offset ) 
        {
            return array( 'code' => 0, 'statusCode' => 200 );            
        }

        public function setAck( $playerId, $id ) 
        {
            return array( 'code' => 0, 'position' => $position, 'statusCode' => 200 );            
        }
}

