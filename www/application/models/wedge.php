<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
use Aws\Common\Aws;
class Wedge extends MY_Model 
{

    protected $token = null;

    // set table is Wedges
    protected $_table = 'Wedges';

    // set validations rules
    protected $validate = array(
        // verify wheelId must be is required
        'wheelId' => array( 
            'field' => 'wheelId', 
            'label' => 'wheelId',
            'rules' => 'required|greater_than[0]'
        ),

        // verify value must be is required
        'value' => array(
            'field' => 'value',
            'label' => 'value',
            'rules' => 'required|regex_match["^\[add\]\[\d*\]\[.*\]$"]'
        ),

        // verify displayString must be is required
        'displayString' => array(
            'field' => 'displayString',
            'label' => 'displayString',
            'rules' => 'required'
        ),

        // verify color must be is required
        'color' => array(
            'field' => 'color',
            'label' => 'color',
            'rules' => 'required'
        ),
        'weight' => array(
            'field' => 'weight',
            'label' => 'weight',
            'rules' => 'required'
        ),

    );

    protected $public_attributes = array(
            'id',
            'wheelId',
            'value',
            'displayString',
            'color',
            'weight',
        );

    protected $playerRole = NULL;

	/**
     * set token to check owner action
     * @param string $token
     * @return none
     */
    public function setToken( $token ) 
    {
        $this->token = $token;
    }

    /**
    * get all wedge from database
    * @param  int $wheelId
    * @return array
    */
    protected function getAllFromDatabase( $wheelId ) 
    {

        // get all wedge is not deleted from database by offset and limit
        $wedges = $this->get_many_by( 'wheelId', $wheelId );

        if ( empty( $wedges ) ) 
        {
            // return log errors when wedge return null
            $errors = array( 'code' => 1, 'message' => 'Wedge Not Found', 'statusCode' => 404 );

            return $errors; 
         } 

         $results = array( 'code' => 0, 'wedges' => $wedges, 'statusCode' => 200 );

         return $results;

    }
    
    public function getSponsorWheel($eventId, $spinKey, $id)
    {        
        $ret = array();
        
        $rs = $this->db->query(sprintf("Select id, playerId from EventNotifications where id = %d and data like '%s' and pending = 1", $eventId, "%" . $spinKey . "%"));
        if(!$rs->num_rows())
            return array('code' => 1, 'message' => 'Invalid Event Notification', 'statusCode' => 404);
        
        $event = $rs->row();
        $playerId = $event->playerId;
        
        $rs = $this->db->query("Select id from Wheels where wheelType = 'Sponsored' and isDeleted = 0 order by id DESC limit 1");
        if(!$rs->num_rows())
            return array('code' => 2, 'message' => 'No valid Wheel Available', 'statusCode' => 404);
        
        $wheelId = $rs->row()->id;
        $rs = $this->db->query("Select w.id, w.displayString, s.artAssetUrl as url, color, w.magnitude, w.height, w.width, w.angle_radians as angle, s.name, s.offerMessage as message, w.weight 
            From Wedges w
            Inner join Sponsor_Campaigns s on s.id = w.sponsorCampaignId
            Where w.wheelId = ?", array($wheelId));
        
        if($rs->num_rows())
        {
            $ret['count'] = $rs->num_rows();
            $ret['wedges'] = $rs->result();
            $ids = array();
            foreach($ret['wedges'] as $wedge)            
                for($i = 0; $i < $wedge->weight; $i++)
                    $ids[] = $wedge->id;
            $ret['winnerId'] = $ids[rand(0, count($ids) - 1)];
            foreach($ret['wedges'] as $index => &$wedge)
            {
                if($wedge->id == $ret['winnerId'])
                {
                    $ret['winnerId'] = $index;
                    //Check to see if it is tickets by checking the displayString
                    if(is_numeric($wedge->displayString))
                    {                        
                        $this->load->model('ticket');
                        $currentDate = date("Y-m-d");            
                        $position = $this->memcacheInstance->get("KEY-Position-playerId-Current-$playerId-$currentDate");
                        if($position && isset($position->multiplier))
                            $ticketCount = $position->multiplier * $wedge->displayString;
                        else
                            $ticketCount = $wedge->displayString;
                        $this->ticket->add($id, $ticketCount , $spinKey);
                    }
                    else //Create an Event Notification for them
                    {                        
                        $this->load->model('eventnotification');
                        $data = json_encode(array('serialNumber' => sprintf("KC%05d", $wedge->sponsorCampaignId), 'entry' => 0, 'prizeAmount' => 0.01, 'prizeName' => $wedge->name, 'buttonType' => 1));
                        $type = "sweepstakes";
                        $result = $this->eventnotification->add(compact('type','data','playerId'), $playerId);
                        if(is_array($result))
                            print_r( compact('type','data','playerId','result'));
                                                
                    }
                }
                $wedge->positionId = $index;
            }
        }
        
        $this->db->query("Update EventNotifications set pending = 0, updated = now() where id = ?", array($event->id));
        
        $ret['code'] = 0;
        $ret['statusCode'] = 200;
        return $ret;
    }
    
    /**
    * get all wedge
    * @param  int $wheelId
    * @return array
    */
    public function getAll( $wheelId ) 
    {
        if ( $this->memcacheEnable ) 
        {

            $key = "KEY-Wedge-All-$wheelId";

            // the first at all, get the result from memcache
            $result = $this->memcacheInstance->get( $key );

            if ( ! $result ) 
            {
                // if empty result, will get all wedges from database
                $result = $this->getAllFromDatabase( $wheelId );

                // set the result to memcache
                $this->user->updateMemcache( $key, $result, 7200 );   
            }
        }
        else 
        {
            $result = $this->getAllFromDatabase( $wheelId );
        }

        //$this->formatResult( $result );

        // return an array
        return $result;

    }

    /**
     * get wedge from database
     * @param  int $wheelId
     * @param  int $id
     * @return array or object
     */
    protected function getByIdFromDb( $wheelId, $id ) 
    {
        // get object wedge by if from database
        $result = $this->get_by( array( 'id' => $id, 'wheelId' => $wheelId ) );

        if ( empty( $result ) ) 
        {
            // return log errors when return empty result
            $errors = array( 'code' => 1, 'message' => 'Wedge Not Found', 'statusCode' => 404 ); 
            return $errors; 
        } 
        else 
        {
            $result->code = 0;
            $result->statusCode = 200;            
            return $result;
        }
    }

    /**
    * get wedge by id
    * @param  int 		$wheelId
    * @param  int 		$id wedge id
    * @param  string 	$playerRole
    * @return array
    */
    public function getById( $wheelId, $id, $playerRole ) {

        // validate the id.
        $invalidId = $this->invalidId( $wheelId, $id );

        if ( $invalidId ) {

            return $invalidId;
        }

        if ( $this->memcacheEnable ) 
        {
            $key = "KEY-Wedge-ID-$wheelId-$id";

            // the first at all, get the result from memcache
            $result = $this->memcacheInstance->get( $key );

            if ( ! $result ) 
            {
                // get wedge from database if empty on memcache
                $result = $this->getByIdFromDb( $wheelId, $id);
                $this->user->updateMemcache( $key, $result );
            }
        }
        else 
        {
            // if not enabled caching, just return the data form database.
            $result = $this->getByIdFromDb( $wheelId, $id );
        }

        $this->formatResult( $result, $playerRole );

        // return object of wedges
        return $result;

    }

    /**
     * format result before return
     * @param  object $wedge
     * @return object
     */
    protected function formatResult( & $wedge, $playerRole ) 
    {
        $wedges = array();
        if ( is_object( $wedge ) ) 
            $wedges[] = $wedge;
        elseif(isset($wedge['wedges']))
            $wedges = $wedge['wedges'];
        
        foreach ($wedges as $key => $value ) 
        {
            $value->id = (int)$value->id;

            if ( $playerRole === 'Player' ) {

                unset( $value->wheelId );
                unset( $value->value );
                unset( $value->numberOfWedge );
                unset( $value->weight );
            }
            else {

                $value->wheelId = (int)$value->wheelId;
                $value->weight = (int)$value->weight;
            }
        }        
    }

    /**
     * validation id
     * @param  int $wheelId
     * @param  int $id
     * @param  bool $onlyCheckWheelId
     * @param  bool $getNumberOfWedges
     * @return bool or array
     */
    protected function invalidId( $wheelId, $id, $onlyCheckWheelId = FALSE, $getNumberOfWedges = FALSE) 
    {
        $errors = array();
        if ( ! $onlyCheckWheelId )
        {
            if($id && is_numeric($id) && $wheelId && is_numeric($wheelId))
                return $errors;
             return array( 'code' => 1, 'message' => "Both id and wheelId have to be numeric", 'statusCode' => 400 );
        }               

        // load wheel model
        $this->load->model( 'wheel' );

        $wheel = $this->wheel->getById( $wheelId );

        if ( is_array( $wheel ) && (int)$wheel['statusCode'] === 404 ) 
            return $wheel;        
        elseif ( $getNumberOfWedges )
            return (int)$wheel->numberOfWedges;        

        return FALSE;
    }
    
    /**
     * rand dom wheel wedges by wheelId and spinKey
     * @param  int $wheelId
     * @param  int $numberOfWedges
     * @return array
     */
    public function randomWheelWedges( $wheelId, $multiplier ) 
    {
        $id = NULL;

        // init wedge list
        $wedgesList = array();        

        // get all wedges by wheelId
        $wedges = $this->getAll( $wheelId );

        if ( (int)$wedges['statusCode'] === 200 ) 
        {
            $orig_wedges = array();
            if($wheelId == 4)
            {
                //I hate doing this by id, but oh well...  Readjusting the values
                $this->load->model("sweepstake");
                $sweeps = $this->sweepstake->getAllActive();  
                $orig_wedges = $wedges['wedges'];
                if(isset($sweeps['sweepstakes']) && count($sweeps['sweepstakes']))
                {
                    $top = $sweeps['sweepstakes'][0];
                    if($top->isImportant)
                    {                        
                        foreach($orig_wedges as $index => $wedge)
                            if($top->ratioTicket > (int) $wedge->displayString)
                                unset($wedges['wedges'][$index]);
                    }                    
                }                
            }
            
            if ( $this->memcacheEnable ) 
            {
                $key = "KEY-Wedge-$wheelId";
                // the first at all, get the result from memcache
                $weightRand = $this->memcacheInstance->get( $key );
            }

            if(!$weightRand)
            {
                $weightRand = array();

                foreach ( $wedges['wedges'] as $key => $wedge )                             
                    for($i = 0; $i < $wedge->weight; $i++)
                        $weightRand[] = $wedge->id;
                if($this->memcacheEnable)
                {
                    $key = "KEY-Wedge-$wheelId";
                    $this->memcacheInstance->set( $key, $weightRand, 7200 );
                }
            }

            // random id from 1 to $numberOfWedges
            $id = $weightRand[rand(0, count($weightRand) - 1)];
            $wedgesList = $wedges['wedges'];
            $ticketTotal = 0;
            shuffle($wedgesList);
            foreach($wedgesList as $key => $wedge)
            {
                if($wedge->id == $id)
                {
                    unset($wedgesList[$key]);
                    if(preg_match("/\[([0-9]+)\]/", $wedge->value, $match))
                            $ticketTotal = $match[1] * $multiplier;
                        
                    $wedgesList[] = $wedge;
                    break;
                }
            }
            
            if($wheelId == 4)
                $wedgesList = $orig_wedges;
            
            $result = array(
                'meta' => array( 'winId' => $id, 'ticketTotal' => $ticketTotal, 'multiplier' => $multiplier ),
                'wedges' => array_values($wedgesList),
                'statusCode' => empty( $wedgesList ) ? 404 : 200
            );
            
            return $result;
        }
    }

	/**
     * convertGameToken 
     * @param  $data 
     * @return $result
     */
    protected function countTicket( $token ) 
    {    
        $tokenArray = explode('][', $token);
        $ticket = 0;
        
        if ( isset( $tokenArray[0] ) )        
            if ( strcmp( $tokenArray[0], "[add" ) == 0 )
                $ticket = isset( $tokenArray[1] ) ? $tokenArray[1] : 0;                    

        return (int)$ticket;
    }

    public function addSpinEvent( $wheelId, $playerId, $gameInfo = null )
    {
            // Generate random 10 character string for token
            $length = 10;
            $wheelToken = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
            $wheelTokenKey = 'KEY-spinWheelToken' . md5( $wheelId . $wheelToken );

            // Store this token in cache and delete it when spinning the wheel. This key will be
            // passed to the wedge spin function and deleted at that time. This is a one-time use key.
            $this->memcacheInstance->set( $wheelTokenKey, $playerId, 0 );

            $this->load->model( 'eventnotification' );

            $wheelData = array(
                    'data' => json_encode( array('wheelId' => $wheelId, 'spinKey' => $wheelTokenKey, 'gameInfo' => $gameInfo) ),
                    'type' => 'wheel',
                    'pending' => 1,
                    'playerId' => $playerId,
                    'notificationEventVersion' => 0,
            );

            $eventResult = $this->eventnotification->add( $wheelData, $playerId );

            return $eventResult;
    }
    
    /**
     * dispose of an event that is not supported by the app
     * @param type $key
     * @param type $playerId
     */
    public function disposeEvent( $eventId, $spinKey, $playerId, $playerRole ) 
    {    
        // get current player, who is spining.
        $spinKeyValue = $this->memcacheInstance->get( $spinKey );

        // if no spin result found then check game token
        if ( $spinKeyValue == $playerId ) 
        {             
            $this->memcacheInstance->delete( $spinKey );	
        }
        
        // Reset this event notification so it doesn't show up in any future queries
        $this->load->model('eventnotification');
        $data = array ( 'pending' => 0 );
        $this->eventnotification->edit( $eventId,  $playerId, $playerRole, $data );
    }
    
    /**
     * random wheel wedge 
     * @param  int $wheelId
     * @param  int $eventId
     * @param  string $spinKey
     * @return array
     */
    public function spin( $wheelId, $eventId, $spinKey, $playerId, $playerRole ) 
    {

            // get current player, who is spining.
            $spinKeyValue = $this->memcacheInstance->get( $spinKey );
            $currentDate = date("Y-m-d");
            
            $position = $this->memcacheInstance->get("KEY-Position-playerId-Current-$playerId-$currentDate");
            
            $multiplier = 1;
            if($position && isset($position->multiplier))            
                $multiplier = (int) $position->multiplier;            
            
            // if no spin result found then check game token
            $this->load->model("eventnotification");
            if ( $spinKeyValue == $playerId ) 
                  {
                // Delete the key from memcache when it's been used
                $this->memcacheInstance->delete( $spinKey );	
            }
            else 
            {
                //If not in memcache, then get this from the DB                
                if(!$this->eventnotification->getTicketEventDB($eventId, $playerId))
                      return array( 'code' => 2, 'message' => 'Not Found:'.$playerId.','.$spinKeyValue.','.$spinKey, 'statusCode' => 404 );        
            }

            // Reset this event notification so it doesn't show up in any future queries            
            $data = array ( 'pending' => 0 );
            $this->eventnotification->edit( $eventId,  $playerId, $playerRole, $data );

            $numberOfWedges = 0;

            // validate or get wheel
            $invalidId = $this->invalidId( $wheelId, 1, TRUE, TRUE );

            // in the case error
            if ( is_array( $invalidId) ) 
                return $invalidId;            
            elseif ( is_numeric( $invalidId ) )
                $numberOfWedges = $invalidId;            

            // random wheel wedges    
            $wedges = $this->randomWheelWedges( $wheelId, $multiplier );

            $winId = $wedges['meta']['winId'];

            $winWedge = $this->get_by( array( 'wheelId' => $wheelId, 'id' => $winId ) );
            if($wheelId == 4)
            {
                $this->load->model("configs");
                $configs = $this->configs->getConfig();
                if(isset($configs['Map']['Popup']))
                {
                    unset($wedges['statusCode']);
                    $theme = $configs['Map']['Popup'][0]['theme'];
                    switch($theme)
                    {
                        case 'sibiggame': 
                            $rs = $this->db->query("Select * from BGQuestionsConfig where theme = ? and convert_tz(now(), 'GMT', 'US/Pacific') between startDate and endDate", array($theme));
                            if($rs->num_rows())
                            {
                                $wedges['pushGame'] = true;
                                $wedges['theme'] = $theme;
                            }
                            else
                            {
                                $wedges['pushGame'] = false;
                            }
                            break;
                            
                        case 'sifinal3': 
                            $rs = $this->db->query("Select * from FinalConfigs where theme = ? and convert_tz(now(), 'GMT', 'US/Pacific') between startDate and endDate", array($theme));
                            if($rs->num_rows())
                            {
                                $wedges['pushGame'] = true;
                                $wedges['theme'] = $theme;
                            }
                            else
                            {
                                $wedges['pushGame'] = false;
                            }
                            break;
                            
                        case 'bracket': 
                            $rs = $this->db->query("Select * from BracketConfigs where convert_tz(now(), 'GMT', 'US/Pacific') between startDate and endDate");
                            if($rs->num_rows())
                            {
                                $wedges['pushGame'] = true;
                                $wedges['theme'] = $theme;
                            }
                            else
                            {
                                $wedges['pushGame'] = false;
                            }
                            break;
                            
                        case 'ptbdailyshowdown':
                        case 'profootball':
                        case 'collegefootball':
                        case 'sicollegebasketball':
                        case 'sidailyshowdown':
                            $rs = $this->db->query("Select * from SportParlayConfig where type = ? and convert_tz(now(), 'GMT', 'US/Pacific') between cardDate and endDate", array($theme));
                            if($rs->num_rows())
                            {
                                $wedges['pushGame'] = true;
                                $wedges['theme'] = $theme;
                            }
                            else
                            {
                                $wedges['pushGame'] = false;
                            }
                            break;
                    }
                    
                    $wedges['statusCode'] = 200;
                }
            }

            $this->load->model( 'ticket' );

            $ticketCount = $this->countTicket( $winWedge->value );

            // insert ticket
            $this->ticket->add( $playerId, $wedges['meta']['ticketTotal'], $spinKey );

            return $wedges;
    }
}
