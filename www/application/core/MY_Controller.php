<?php

require( APPPATH . '/libraries/REST_Controller.php' );

include_once APPPATH . "/helpers/Tracer.php" ;

class MY_Controller extends REST_Controller
{
    protected $secured_controller;
    protected $secured_actions;
    protected $token = null;

    function __construct( $secured_controller = FALSE, $secured_actions = array() )
    {
        if ( isset( $_SERVER['HTTP_TOKEN'] ) )
            $this->token = $_SERVER['HTTP_TOKEN'];
        
        parent::__construct();

        // respond to preflights
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS')
        {
            // return only the headers and not the content
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']) && $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'POST' )
            {
                header('Access-Control-Allow-Origin: *');
                header('Access-Control-Allow-Headers: X-Requested-With');
            }
            exit;
        }

        $this->secured_controller = $secured_controller;
        $this->secured_actions = $secured_actions;

        if ( $this->secured_controller )
        {
            $this->_check_security();
        }
    }

    protected function _check_security()
    { 
        // get playerId
        $playerId = $this->_get_player_memcache( 'playerId' );

        if (is_array( $playerId ) )
        {
            $this->response( array( 'error' => 'Not authorized CS 1' ), 200 );
        }
        else {
            if ( ! $this->_access_granted( $playerId, $this->router->method ) )
            {
                $this->response( array( 'error' => 'Not authorized CS 2 ' . $playerId), 200 );
            }
        }
    }    

    protected function _access_granted( $userId, $action_name )
    {
        if ( ! $this->secured_controller ) {
            return TRUE;
        }
        else {            
            if ( ! array_key_exists( $action_name, $this->secured_actions ) ) {
                return TRUE;
            }
            else {
                if ( $userId === FALSE || ! isset( $userId ) ) {
                    return FALSE;
                }
                else {
                    // get playerRole
                    $playerRole = $this->_get_player_memcache( 'playerRole' );

                    if ( ! is_array( $this->secured_actions[$action_name] ) ) {
                        $allowed_roles = trim( $this->secured_actions[$action_name] );

                        if ( $allowed_roles == '*') {
                            return TRUE;
                        }
                        else {
                            return stripos( $allowed_roles, $playerRole ) !== FALSE;
                        }
                    }
                    else {
                        return in_array( $playerRole, $this->secured_actions[$action_name] );
                    }
                }               
            }
        }
    }

    /**
     * get player from memcache
     * @param  int $column playerId or playerRole
     * @return int or string
     */
    protected function _get_player_memcache( $column )
    {        
        if ( $this->token ) 
        {
            $player = $this->sessions->getPlayerData($this->token);
            
            if ( $player ) {
                return $player[$column];
            }
            else {
                if ( $this->user->memcacheInstance->getResultCode() == Memcached::RES_NOTFOUND ) {
                    return array( 'error' => 'Cache resource not found' );
                }
            }
        }            
        return FALSE;
    }
    
    /**
     * get session from memcache
     * @param  int $column playerId or playerRole
     * @return int or string
     */
    protected function _get_session_memcache( $column )
    {        
        if ( $this->token ) 
        {
            $session = $this->sessions->getSessionData($this->token);
            
            if ($session && isset($session[$column]) ) {
                return $session[$column];
            }
            else {
                if ( $this->user->memcacheInstance->getResultCode() == Memcached::RES_NOTFOUND ) {
                    return array( 'error' => 'Cache resource not found' );
                }
            }
        }            
        return FALSE;
    }

    /**
     * format result before return
     * @param  array or object $result
     * @return json
     */
    public function formatResponse( $result ) {

        $status = 500;

        $in_maintenace_mode = $this->user->maintenanceMode();

        // check result retuen on function 
        if ( is_array($result) ) {

            // if is array will be get element last and remove it from result
            $status = array_pop( $result );

            // are we in maintenace mode
            if ($in_maintenace_mode) $result['maintenanceMode'] = $in_maintenace_mode ;

        } 

        if (isset($result->statusCode)) {

            // get status code 
            $status = $result->statusCode;
            // remove status code 
            unset($result->statusCode);

            // are we in maintenace mode
            if ($in_maintenace_mode) $result->maintenanceMode = $in_maintenace_mode ;

        }
        
        if($status != 200 || (isset($result->code) && $result->code != 0))        
            error_log(json_encode($result));        

        $this->response( $result, $status );
    }
}