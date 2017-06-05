<?php

use Aws\Common\Aws;

class Debug extends MY_Controller {
	// set variable player->memcacheInstance
	public function __construct() {

            if ( isset( $_SERVER['HTTP_TOKEN'] ) )
                $this->token = $_SERVER['HTTP_TOKEN'];
		
		parent::__construct(
            TRUE, // Controller secured
            array(
               'clearCache' => 'Administrator',
               'lastlogin' => 'Administrator',
               'createGames' => 'Administrator',
               'globalMessages' => 'Administrator',
               'clearCacheByPlayerId' => 'Administrator',
               'recoverEventNotifications' => 'Administrator',
               'verifyMemCacheKey' => 'Administrator',
               'removeMemCacheKey' => 'Administrator',
               'removeMemCachePlayer' => 'Administrator',
               'addGameToken' => array( 'Administrator', 'User', 'Guest' ),
               'sendEmailInvite' => 'Administrator',
               'PlayerById' => 'Administrator',
                'addAd' => array('Administrator','User','Guest'),
                'analytics' => array('Administrator','User','Guest')
            )//secured action
        );

	}

    public function maintenance_post()
    {
        $code = 0;
        $description = "";
        $this->load->model("configs");
        $config = $this->configs->getConfigDb();
        if(isset($config['Config']['Maintenance']))
        {
            $code = $config['Config']['Maintenance'][0];
            switch($code)
            {
                case 0: break;
                case 1: $description = "Thank you for visiting Kizzang. We are currently updating our app to provide new content and improve your experience. Please check back later."; break;
                case 2: $description = "Temp Forced Update Copy"; break;
            }
        }
        return $this->formatResponse(array('code' => $code, 'description' => $description, 'statusCode' => 200));
    }
    
    public function date_post()
    {        
        $results['date'] = date("Y-m-d H:i:s");            
        $results['code'] = 200;
        $this->formatResponse( $results );
    }
    
    public function addAd_post()
    {
        $data = $this->post();
        $this->load->model('ad');
        $data['playerId'] = $this->_get_player_memcache( 'playerId' );
        $this->formatResponse($this->ad->add($data));
    }
    
    public function email_action_get()
    {
        $this->email_action_post();
    }
    
    public function email_action_post()
    {
        $postBody = file_get_contents('php://input');
        $this->db->query("Insert into PayPalStatuses (status) values (?)", $postBody);
        //$this->formatResponse(array('statusCode' => 200));
        die();
        
        if($this->input->post())
        {
            $data = json_decode($this->input->post(), true);
            if(isset($data['TopicArn']) && isset($data['Message']))
            {
                $message = $data['Message'];
                
            }
        }
    }
    
    public function analytics_post()
    {
        $data = $this->post();
        if(!isset($data['type']) || !isset($data['subType']))
        {
            $ret = array('code' => 1, 'message' => 'Failed Validation', 'statusCode' => 200);
        }
        else
        {
            $data['playerId'] = $this->_get_player_memcache("playerId");
            if(isset($data['id']))
            {
                $data['foreignId'] = $data['id'];
                unset($data['id']);
            }
            
            $success = $this->db->insert('analytics.rawAnalytics', $data);
            if($success)
                $ret = array('code' => 0, 'statusCode' => 200);
            else
                $ret = array('code' => 2, 'message' => 'Failed Insert', 'statusCode' => 200);
        }
        $this->formatResponse($ret);
    }
    
    public function w2check_get($id)
    {
        $this->w2check_post($id);
    }
    
    public function ppStatus_post()
    {
        $data = $this->post();
        $this->db->insert('PayPalStatuses', array('status' => json_encode($data)));
        return array('code' => 0, 'statusCode' => 200);
    }
    
    public function w2check_post($id)
    {
        $rs = $this->db->query("Select t.*, playerId from rightSignature.signins s
            Inner join rightSignature.templates t on s.templateId = t.id
            where s.id = ? and status = 'Pending'", array($id));
        if(!$rs->num_rows())
        {
            $results = array('code' => 1, 'message' => 'Invalid id', 'statusCode' => 200);
        }
        else
        {
            $template = $rs->row();
            $rs = $this->db->query("Select * from Users where id = ?", array($template->playerId));
            if(!$rs->num_rows())
                $results = array('code' => 2, 'message' => 'Invalid Player id', 'statusCode' => 200);
            else
                $results = array('code' => 0, 'user' => $rs->row(), 'template' => $template, 'statusCode' => 200);
        }
        $this->formatResponse( $results );
    }
    
    public function w2update_post($id)
    {
        $this->w2update_get($id);
    }
    
    public function w2update_get($id)
    {
        $this->db->query("Update rightSignature.signins set status = 'Complete' where id = ?", array($id));
        $rs = $this->db->query("Select * from rightSignature.signins where id = ?", array($id));
        if(!$rs->num_rows())
            $this->formatResponse(array('code' => 1, 'message' => 'Status Update was incomplete', 'statusCode' => 200));
        
        $playerId = $rs->row()->playerId;
        $this->db->query("Update Winners set status = 'Claimed' where player_id = ? and expirationDate > ?", array($playerId, date("Y-m-d H:i:s")));
        $this->db->query("Update Users set accountStatus = 'Active' where id = ?", array($playerId));
        $this->formatResponse(array('code' => 0, 'message' => 'Status Updated', 'statusCode' => 200));
    }
    
    public function ipn_post()
    {
        
        $data = null;        
        if ( $this->input->post() ) 
            $data = $this->input->post();
       
        if ( isset( $data ) )
            $this->processIPN( $data );
        
        // Send an empty HTTP 200 OK response to acknowledge receipt of the notification 
        header('HTTP/1.1 200 OK'); 
    }
    
    private function processIPN( $data )
    {

        // Build the required acknowledgement message out of the notification just received
        $req = 'cmd=_notify-validate';               // Add 'cmd=_notify-validate' to beginning of the acknowledgement

        foreach ($data as $key => $value) {         // Loop through the notification NV pairs
            $value = urlencode(stripslashes($value));  // Encode these values
            $req  .= "&$key=$value";                   // Add the NV pairs to the acknowledgement
        }
        
        $status = isset($data['payment_status']) ? $data['payment_status'] : "Unknown";
        $amount = isset($data['payment_gross_1']) ? $data['payment_gross_1'] : 0;
        $win_confirmation_id = isset($data['unique_id_1']) ? $data['unique_id_1'] : 0;
        $xaction_id = isset($data['masspay_txn_id_1']) ? $data['masspay_txn_id_1'] : "Unknown";
        $rec = compact('status','amount','win_confirmation_id','xaction_id');
        $rec['info'] = json_encode($data);
        $this->db->insert('PaypalAudits', $rec);
        
        $win_status = "";
        
        switch($status)
        {
            case 'Completed': 
                $win_status = "Paid"; 
                break;
            
            case 'Denied':
            case 'Expired':
            case 'Failed':
            case 'Refunded':
                $win_status = "Forfeit";
                break;
        }
        $this->db->where('id', $win_confirmation_id);
        $rec = array('payPalStatus' => $status, 'payPalTransactionId' => $xaction_id);
        if($win_status)
            $rec['status'] = $win_status;
        $this->db->update("Payments", $rec);        
        
        // Enables sandbox mod
        $sandbox_mode = (USE_SANDBOX == false);
        if($sandbox_mode)
        {
           $paypal_url = PAYPAL_ENDPOINT_SANDBOX;
        }
        else
        {
           $paypal_url = PAYPAL_ENDPOINT_LIVE;
        }
        //$paypal_url = "https://api-3t.paypal.com/nvp";

        $ch = curl_init($paypal_url);
        if ($ch == FALSE)
        {
            return FALSE;
        }

        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);

        // Set TCP timeout to 30 seconds
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

        $res = curl_exec($ch);

        if (curl_errno($ch) != 0)
        {
            // cURL error
            curl_close($ch);
            exit;
        }
        else
        {
            curl_close($ch);
        }        
    }
    
    public function passthruImage_post()
    {
        $data = $_POST;
        if(!isset($data['url']))
        {
            $this->formatResponse(array('code' => 4, 'message' => 'Invalid Call', 'statusCode' => 200));
            die();
        }
        $context = stream_context_create(array('http' => array(
            'timeout' => 10.0
        )));
        
        $ext = substr($data['url'], strrpos($data['url'], ".") + 1);
        $fullPath = $data['url'];
        $fp = fopen($fullPath, 'rb', false, $context);

        if($fp)
        {
            $fname     = basename ($fullPath);

            header("Pragma: ");
            header("Cache-Control: ");
            header("Content-Type: image/$ext");
            header("Content-Disposition: attachment; filename=\"".$fname."\"");         
            fpassthru($fp);
        }
        else
        {
            $fakeAd = "https://d23kds0bwk71uo.cloudfront.net/800x600_ad_ph.jpg";
            $fp = fopen($fakeAd, 'rb');
            if($fp)
            {
                header("Pragma: ");
                header("Cache-Control: ");
                header("Content-Type: image/jpg");
                header("Content-Disposition: attachment; filename=\"".$fakeAd."\"");         
                fpassthru($fp);
            }
            else
            {
                print "Failed";
            }
        }
        exit;
    }
    //API Key: 2ae89912245373fbcb1a304684450a578fff14ac9c1bdf4951e96651beb4f2d2
    //Network ID: adperio
    //Sample: https://api.hasoffers.com/v3/Affiliate_Affiliate.json?Method=getAccountManager&api_key=2ae89912245373fbcb1a304684450a578fff14ac9c1bdf4951e96651beb4f2d2&NetworkId=adperio
    public function passthru_post()
    {
        $data = $_POST;
        if(!isset($data['url']))
        {
            $this->formatResponse(array('code' => 4, 'message' => 'Invalid Call', 'statusCode' => 200));
            die();
        }
        
        $data['url'] = str_replace("json?", "json?api_key=2ae89912245373fbcb1a304684450a578fff14ac9c1bdf4951e96651beb4f2d2&NetworkId=adperio&", $data['url']);
                        
        $curlHandle = curl_init();
 
        // Configure cURL request
        curl_setopt($curlHandle, CURLOPT_URL, $data['url']);        
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 5);
        $jsonEncodedApiResponse = curl_exec($curlHandle);

        // Ensure HTTP call was successful
        if($jsonEncodedApiResponse === false)
        {
                $this->formatResponse( array('code' => 1, 'message' => 'API call failed with cURL error: ' . curl_error($curlHandle), 'statusCode' => 200));
                die();
        }
            
        // Clean up the resource now that we're done with cURL
        curl_close($curlHandle);

        // Decode the response from a JSON string to a PHP associative array
        $apiResponse = json_decode($jsonEncodedApiResponse, true);

        // Make sure we got back a well-formed JSON string and that there were no
        // errors when decoding it
        $jsonErrorCode = json_last_error();
        if($jsonErrorCode !== JSON_ERROR_NONE) 
        {            
            $this->formatResponse( array('code' => 2, 'message' => 'API response not well-formed (json error code: ' . $jsonErrorCode . ')', 'statusCode' => 200));
            die();
        }

        // Print out the response details
        if($apiResponse['response']['status'] === 1)
            $this->formatResponse( array('code' => 0, 'data' => $apiResponse['response']['data'], 'statusCode' => 200));            
        else 
            $this->formatResponse( array('code' => 1, 'message' => $apiResponse['response']['errorMessage'], 'statusCode' => 200));            
    }
        
    /*
     * A function to verify whether or not a memcache key exists
     * debug/verifyMemCacheKey/
     */

    public function verifyMemCacheKey_get($key) 
    {
        $results['message'] = $this->user->memcacheInstance->get($key);            
        $results['code'] = 200;
        $this->formatResponse( $results );
    }                
    
    /*
     * A function to delete a memcache key if it exists
     */
    
    public function removeMemCacheKey_get($key)
    {
        $result = $this->user->memcacheInstance->get($key);
        $this->user->memcacheInstance->delete($key);
        $results['message'] = $result ? true : false;            
        $results['code'] = 200;
        $this->formatResponse( $results );            
    }
    
    /*
     * A function to delete a player memcache keys if it exists
     */
    
    public function removeMemCachePlayer_get($id)
    {                
        $results['message'] = " Results Deleted";            
        $results['code'] = 200;
        $this->formatResponse( $results );            
    }

	/**
	 * Recover event notifications in memcache after a clear
	 */
    public function recoverEventNotifications_post() {
        
        $pendingEvent = $this->db->select( 'playerId, data' )
                        ->from('EventNotifications')
                        ->where( 'pending', 1 )
                        ->get()->result();
                        
        $result = array( 'code' => 0, 'message' => 'Default result', 'statusCode' => 200 );
        
        if ( empty( $pendingEvent ) ) {

            $result = array( 'code' => 0, 'message' => 'No pending events need to be recovered', 'statusCode' => 200 );
            
        }
        else {

            $keyList = array();

            foreach ( $pendingEvent as $event ) {
                
                // Return JSON object representing data
                $json_data = json_decode( $event->data );
                
                if ( isset( $json_data->spinKey ) )
                {
                    $playerId = $event->playerId;
                    $wheelTokenKey = $json_data->spinKey;

                    $keyList[$wheelTokenKey] = $playerId;
                    
                    // Store this token in cache and delete it when spinning the wheel. This key will be
                    // passed to the wedge spin function and deleted at that time. This is a one-time use key.
                    $this->user->memcacheInstance->set( $wheelTokenKey, $playerId, 0 );
                }

            }

            $result['data'] = $keyList;
        }
        
        $response = array('result' => $result );

        $this->response( $response, 200 );
    }
        
	/**
	 * Force reset memcache
	 */
	public function clearCache_post() {
		
		/* flush all items in 10 seconds */
		$result = $this->user->memcacheInstance->flush(1);

		$this->response( $result, 200 );
	}

	/**
	 * Add a game token for testing wheel
	 */
	public function addGameToken_post($wheelId, $gameToken) {
		
		// Create game token key
		$gameTokenKey = 'KEY-gameToken' . md5( $wheelId . $gameToken );

		// For now, the game token never expires once it's set
		$expire = 0;
		
		// Set temporary game token for wheel
		$result = $this->user->memcacheInstance->set($gameTokenKey, $gameToken, $expire);

		$response = array('result' => $result, 'key' => $gameTokenKey);
		
		$this->response( $response, 200 );
	}
	
    // Check if all the required form data is sent with the last login call
    protected function checkLastLoginPostData( $postData )
    {
        $messages = null;

        if (!array_key_exists('lastDateLoggedIn', $postData))
        {
            $messages['error'] = "Form missing lastDateLoggedIn";
        }

        if (!array_key_exists('newPosition', $postData))
        {
            $messages['error'] = "Form missing newPosition";
        }

        if (!array_key_exists('playerId', $postData))
        {
            $messages['error'] = "Form missing playerId";
        }

        if (!array_key_exists('timesMissed', $postData))
        {
            $messages['error'] = "Form missing timesMissed";
        }

        return $messages;
    }
        
        // Debug player position and game count data
	public function lastlogin_get( $postData ) {

            if(!isset($postData['clearEN']) || !$postData['clearEN'])
            {
                $query = sprintf("UPDATE EventNotifications SET pending=0 WHERE playerId='%d'",  $postData['playerId']);
                $this->db->query( $query );
            }
            
            $messages = $this->checkLastLoginPostData( $postData );
            if ( $messages )
            {
                $messages['statusCode'] = 200;
                return $this->formatResponse( $messages );                
            }
            
            $lastDateLoggedIn = $postData['lastDateLoggedIn'];
            if ( ! $this->form_validation->valid_date( $lastDateLoggedIn ) ) 
            {
                $this->response( array( 'error' => 'lastDateLoggedIn must contain a valid date (m-d-Y)' ), 400 );
            }

            $timesMissed = $postData['timesMissed'];
            $position = $postData['newPosition'];
            $playerId = $postData['playerId'];
            
            // Optional settings
            $clearGameCount = 0;
            if (array_key_exists('clearGameCount', $postData))
            {
                $clearGameCount = $postData['clearGameCount'];
            }
            
            $gamesToAdd = 0;
            if (array_key_exists('gamesToAdd', $postData))
            {
                $gamesToAdd = $postData['gamesToAdd'];
            }
            $ruleCode = 0;
            if (array_key_exists('ruleCode', $postData))
            {
                $ruleCode = $postData['ruleCode'];
            }
            
            // Load models needed to manipulate player data
            $this->load->model( 'position' );
            $this->load->model( 'gamecount' );
            $this->load->model( 'playperiod' );

            // Only flush player data. Previously, all memcache was being flushed which would cause problems
            // with pending notifications, wheel spins, and other mechanics in the app.
            $currentDate = date("Y-m-d");
            $this->user->memcacheInstance->delete("KEY-Position-playerId-Current-$playerId-$currentDate");
            $this->user->memcacheInstance->delete("KEY-Position-playerId-All-$playerId");
            $this->user->memcacheInstance->delete("KEY-Position-playerId-$playerId-0-10");
            $this->user->memcacheInstance->delete("KEY-GameCount-playerId-$playerId-$currentDate");
            $this->user->memcacheInstance->delete("KEY-playperiod-current-playerId-$playerId");
            $this->user->memcacheInstance->delete("KEY-GameCount-playerId-$playerId-$dateCreated");                        
            
            // Make sure the tokens match for this player when using the debug call
            $this->user->setToken( $this->token );
            $this->gamecount->setToken( $this->token );
            
            // If clearGameCount is sent with the form data, wipe out the current game count data
            if ( $clearGameCount == 1)                            
                $this->gamecount->delete_by( 'playerId', $playerId );            

            // Delete any existing player play periods and position data in the database
            $this->playperiod->delete_by( 'playerId', $playerId );	
            $this->position->delete_by( 'playerId', $playerId );

            // create play period for current day
            $playPeriod = $this->playperiod->add( $playerId );

            // in the case created successfully
            if ( is_array( $playPeriod ) )
            {
                if ( array_key_exists('id', $playPeriod ) )
                {
                    $playPeriodId = $playPeriod["id"];

                    // Get existing play period for player if we couldn't add one
                    $playPeriod = $this->playperiod->getById( $playerId, $playPeriodId );
                }
            }

            $this->position->add($playerId, 0);
            $currentPosition = $this->position->getLast( $playerId );

            // Calculate the current number of games played by the player
            $query = $this->db->select('SUM(count) AS count', FALSE )
                            ->where( 'playerId', $playerId )
                            ->where( 'playPeriodId', $playPeriod->id )
                            ->get( 'GameCount' )
                            ->row();

            $currentGamesPlayed  = isset( $query->count ) ? $query->count : 0;
            $currentGamesPlayed += $gamesToAdd;
            
            $lastDateLoggedIn = $lastDateLoggedIn . date( ' H:i:s' );
            $dateInt = strtotime( str_replace( '-', '/', $lastDateLoggedIn ) );
            $startDate = date( 'Y-m-d H:i:s', $dateInt );
            $endDate = date( 'Y-m-d 23:59:59', $dateInt );

            $nextPosition = $position;
            $status = 2;
            if ($currentGamesPlayed >= 15)
            {
                $nextPosition++;
                $status = 3;
            }
                
            $playPeriodData = array(
                        'playerId' => $playerId,
                        'startDate' => $startDate,
                        'endDate' => $endDate,
                        'gamesCredit' => 15,
                        'gamesPlayed' => $currentGamesPlayed,
                        'status' => $status,
                        'countMissedDay' => $timesMissed
                    );

            $this->playperiod->update( $playPeriod->id, $playPeriodData, TRUE );

            if ( $gamesToAdd > 0 )
            {
                $this->addGames( $playerId, $gamesToAdd, $playPeriod->id, 'SportsEvent' );
            }
            
            $multiplier = 1;
            $this->load->model('configs');
            $config = $this->configs->getConfig();
            if(isset($config['Map']) && isset($config['Map']['Days']))
                foreach($config['Map']['Days'] as $event)                
                    if($event['action'] == 'multiplier' && $event['day_number'] == $position)
                        $multiplier = $event['multiplier'];                            

            $data = array(
                        'playerId' => $playerId,
                        'fromPosition' => $nextPosition,
                        'startPosition' => $position,
                        'endPosition' => $nextPosition,
                        'multiplier' => $multiplier,
                        'calendarDate' => date( 'Y-m-d', strtotime( str_replace('-', '/', $lastDateLoggedIn ) ) ),
                        'ruleCode' => $ruleCode, /* simulate a natural increment position */
                        'ruleApplied' => 'debug position'
                    );

            // Get last position entry for player and update it with this debug data
            $this->position->update( $currentPosition->id, $data, TRUE);                        
                
            $lastPosition = $this->position->getLast( $playerId );
            $lastPlayPeriod = $this->playperiod->getByPlayerId( $playerId, 1, 0 );
            
            // Current play period, flush cache again so we get the db values and the cache is reset
            $this->user->memcacheInstance->delete("KEY-Position-playerId-Current-$playerId-$currentDate");
            $this->user->memcacheInstance->delete("KEY-Position-playerId-All-$playerId");
            $this->user->memcacheInstance->delete("KEY-Position-playerId-$playerId-0-10");
            $this->user->memcacheInstance->delete("KEY-GameCount-playerId-$playerId-$currentDate");
            $this->user->memcacheInstance->delete("KEY-playperiod-current-playerId-$playerId");
            $this->user->memcacheInstance->delete("KEY-GameCount-playerId-$playerId-$dateCreated");
                        
            
            // Clear all event notifications in db            
            $this->response( array( 'playPeriod' => $lastPlayPeriod, 'position' => $lastPosition ), 201 );
	}
        
        //
        // Add games to current game list for a player
        //
        public function addGames( $playerId, $gamesToAdd, $playPeriodId, $gameType = 'SportsEvent' )
        {
            // get gameCount by filed token, field dateCreate and gameType from databaase
            $result = $this->gamecount->get_by( array( 'playPeriodId' => $playPeriodId, 'gameType' => $gameType ) );

            // exists game count with token in day
            if ( ! empty( $result ) && is_object( $result ) ) {

                $id = $result->id;

                // update count by id, set skip_validation = TRUE in 3nd parameter
                $this->gamecount->update( $id, array( 'count' => $gamesToAdd ), TRUE );
                
            }
            else
            {
		$decodeTokenSession = base64_decode( $this->token );
	        $tokenSessionArray = explode( Player::ENCRYPTION_KEY, $decodeTokenSession );

	        // token include email, password and deviceId
                $tokenGameCount = null;
	        if ( count( $tokenSessionArray ) == 3 ) {

	            $tokenGameCount = $this->user->base64Encode( array( $tokenSessionArray[0], $tokenSessionArray[1] ) );
	        }
                
                // init game count data
                $gameCountData = array(
                        'playerId' => $playerId,
                        'playPeriodId' => $playPeriodId,
                        'token' => $tokenGameCount,
                        'gameType' => $gameType,
                        'count' => $gamesToAdd
                );

                if ( $tokenGameCount != null )
                {
                    // set skip_validation = TRUE in 2nd parameter
                    $id = $this->gamecount->insert( $gameCountData, TRUE );
                    
                    if ( $id )
                    {	
                        // get gamecount of player on current date
                        //$count = $this->gamecount->getCountCurrent( $playerId, $playPeriodId );

                        // update gamesPlayed of play period with count of gamecount
                        //$this->playperiod->edit( $playerId, $playPeriodId, $count );

                    }
                }
            }
                        
        }
        
	public function createGames_post() {
            
            $this->load->model( 'playperiod' );
            $this->load->model( 'gamecount' );
            
            $this->user->setToken( $this->token );
            $this->gamecount->setToken( $this->token );
            
            $data = $this->post();
            
            $playerId     = $data['playerId'];
            $gamesToAdd   = $data['gamesToAdd'];
            $gameType     = $data['gameType'];
            
            // create play period for current day
            $playPeriod = $this->playperiod->getByPlayerIdFromDb( $playerId, true );
            $playPeriodId = $playPeriod->id;
            
            $this->addGames( $playerId, $gamesToAdd, $playPeriodId, $gameType );
            
            $startDate = date( 'Y-m-d H:i:s' );
            $endDate = date( 'Y-m-d 23:59:59' );
            
            // Update play period data with current game count
            $playPeriodData = array(
                        'playerId' => $playerId,
                        'startDate' => $startDate,
                        'endDate' => $endDate,
                        'gamesCredit' => 15,
                        'gamesPlayed' => $gamesToAdd,
                        'status' => $playPeriod->status,
                        'countMissedDay' => $playPeriod->countMissedDay
                    );

            $this->playperiod->update( $playPeriodId, $playPeriodData, TRUE );
            
            $this->user->memcacheInstance->delete("KEY-Position-playerId-Current-$playerId-$currentDate");
            $this->user->memcacheInstance->delete("KEY-Position-playerId-All-$playerId");
            $this->user->memcacheInstance->delete("KEY-Position-playerId-$playerId-0-10");
            $this->user->memcacheInstance->delete("KEY-GameCount-playerId-$playerId-$currentDate");
            $this->user->memcacheInstance->delete("KEY-playperiod-current-playerId-$playerId");
            $this->user->memcacheInstance->delete("KEY-GameCount-playerId-$playerId-$dateCreated");
            
            $this->response( array( 'messages' => 'Games created' ), 200 );
	}
        
	public function lastlogin_post() {
            
            $data = $this->post();
            
            $this->lastlogin_get( $data );
	}

	public function globalMessages_post( $playerId ) {

		$key = "KEY-GlobalMessage-playerId-$playerId-" . md5( "playerId-$playerId" );
		$statusMessageKey = "KEY-StatusMessage-playerId-$playerId-" . md5( "getByPlayerId-$playerId" );
		$statusMessageStateKey = "KEY-StatusMessage-State-playerId-$playerId-" . md5( "getState-$playerId" );
		$this->user->updateMemcache( $statusMessageKey, NULL );
		$this->user->updateMemcache( $statusMessageStateKey, NULL );

		$messages = $this->user->memcacheInstance->get( $key );

		$data = $this->post();

		$errors = array();

		if ( ! isset( $data['displayText']) || ! $data['displayText'] ) {

			$errors[] = 'The displayText is required';
		}

		if ( ! isset( $data['options']) || ! $data['options'] ) {

			$errors[] = 'The options is required';
		}

		if ( ! empty( $errors ) ) {

			$result = array();
			$result['code'] = 1;
			$result['errors'] = $errors;
			$result['statusCode'] = 400;
			
			$this->formatResponse( $result );
		}
		else {

			$data['playerId'] = (int)$playerId;
			$data['createdDate'] = date('Y-m-d H:i:s');
			$data['type'] = 'system_status';
			$data = array_merge( array('id' => count( $messages['messages'] ) + 1 ), $data );

			$messages['messages'][] = $data;
			$messages['statusCode'] = 200;

			$this->user->updateMemcache( $key, $messages, 0 );
		}

		$this->formatResponse( $messages );
	}

        public function clearCacheByPlayerId_post( $playerId ) 
        {

            if ( ! is_numeric( $playerId ) || $playerId <= 0 ) {

                $result = array( 'code' => 1, 'message' => 'Id must be a numeric and greater than zero', 'statusCode' => 400 );

                $this->formatResponse( $result );
            }

            $keys = array();

            $player = $this->user->getById( $playerId, TRUE );

            if ( is_array( $player ) ) {

                $this->formatResponse( $player );
            }

            $allKey 	= $this->user->memcacheInstance->getAllKeys();
            $prefixKey 	= "-playerId-$playerId-";

            foreach ( $allKey as $key ) {

                if ( strpos( $key, $prefixKey ) !== FALSE ) {

                    array_push( $keys, $key );
                }
            }

            $isDeleted = $this->user->memcacheInstance->deleteMulti( $keys );

            if ( $isDeleted ) 
            {
                $emailHash 			= $player->emailHash;
                $passwordHash 		= $player->passwordHash;
                $tokenSessionBase64 = $this->user->base64Encode( array( $emailHash, $passwordHash ) );
                $tokenSessionKey    = 'KEY-session-token' . $tokenSessionBase64;

                $this->user->memcacheInstance->delete( $tokenSessionKey );

                $keys = array();

                // Not sure player login with any deviceId, only empty cache deviceId in [1..10]
                for ( $i = 1; $i < 11; $i++ ) { 

                    $tokenSession = $this->user->base64Encode( array( $emailHash, $passwordHash, md5( $i ) ) );
                    $tokenPlayerKey = 'KEY-player-token' . $tokenSession;

                    array_push( $keys, $tokenPlayerKey );
                }

                $this->user->memcacheInstance->deleteMulti( $keys );
            }

            $result = array( 'message' => 'Empty cache the successfully with player id ' . $playerId , 'statusCode' => 200 );

            $this->formatResponse( $result );
        }

	public function getAllKey_get() {

		$allKey = 	$this->user->memcacheInstance->getAllKeys();

		$result = array( 'keys' => $allKey, 'statusCode' => 200 );

		$this->formatResponse( $result );
	}

	/**
	 * only run flood test
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function getGender_get( $id ) {

		$this->load->model('gender');

		$key = 'Player-Flood-Test' . md5( "getPlayerById-$id" );

		$result = $this->user->memcacheInstance->get( $key );

		if ( $result ) 
			$this->formatResponse( $result );

		$result = $this->gender->get( $id );

        if ( empty($result) ) {

            $result = array( 'code' => 1, 'message' => 'Gender Not Found', 'statusCode' => 404 );
        }
        else {

            $result->statusCode = 200;

            $this->user->updateMemcache( $key, $result );
        }

        $this->formatResponse( $result );
	}

	public function currentTime_get() {

		$dateCurrentMysql = $this->db->query("SELECT current_timestamp() as time;")->row('time');

		$this->formatResponse( array( 'timeMysql' => $dateCurrentMysql, 'timePHP' => date('Y-m-d H:i:s'), 'statusCode' => 200 ) );
	}

}

