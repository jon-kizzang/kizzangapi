<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require dirname(__FILE__) . '/../../vendor/autoload.php';

// include_once "application/helpers/Tracer.php" ;
 
use Aws\Common\Aws;

class User extends MY_Model {
    
    protected $token = null;
    
    // set table is Players
    protected $_table = 'Users';
    
    const ENCRYPTION_KEY = "KizfDkj353";
    
    // set validation rules
    protected $validate = array(
        // email is required, valid, unique and maxlength is 120 characters
        'email' => array( 
            'field' => 'email', 
            'label' => 'Email or phone number',
            'rules' => 'required|valid_email_phone|email_phone_unique[Players.emailHash.phoneHash]|max_length[120]|trim'
        ),
        // password is required and maxlength is 20 characters
        'password' => array(
            'field' => 'password',
            'label' => 'Password',
            'rules' => 'required|xss_clean|max_length[20]|trim'
        ),
        // gender is required and matches with 1,2 or 3
        'gender' => array(
            'field' => 'gender',
            'label' => 'Gender',
            'rules' => 'required|regex_match["^[1-4]{1}$"]'
        ),
        // firstName is required, maxlength is 25 characters, include alpha and space
        'firstName' => array(
            'field' => 'firstName',
            'label' => 'First Name',
            'rules' => 'required|max_length[25]|trim|regex_match["^([a-zA-Z\s]+)$"]'
        ),
        // lastName is required, maxlength is 25 characters, include alpha and space
        'lastName' => array(
            'field' => 'lastName',
            'label' => 'Last Name',
            'rules' => 'required|max_length[25]|trim|regex_match["^([a-zA-Z\s]+)$"]'
        ),
        // day of birth is required and valid
        'dob' => array(
            'field' => 'dob',
            'label' => 'Date of birth',
            'rules' => 'required|date_of_birth'
        ),
        // state include 2 characters alpha
        'state' => array(
            'field' => 'state',
            'label' => 'State',
            'rules' => 'alpha|max_length[2]|min_length[2]'
        ),
        // zip is numeric and maxlength is 8 characters
        'zip' => array(
            'field' => 'zip',
            'label' => 'Zip',
            'rules' => 'numeric|max_length[8]'
        ),
        // honorific including 'mr.','dr.', 'miss', 'ms.' or 'mrs.'
        'honorific' => array(
            'field' => 'honorific',
            'label' => 'Honorific',
            'rules' => 'max_length[12]|valid_honorific'
        ),
        // city including alpha charactes and maxlength is 40
        'city' => array(
            'field' => 'city',
            'label' => 'City',
            'rules' => 'regex_match["^([a-zA-Z\s]+)$"]|max_length[40]'
        ),
        'homePhone' => array(
            'field' => 'homePhone',
            'label' => 'Home Phone',
            'rules' => 'valid_phone'
        ),
        'mobilePhone' => array(
            'field' => 'mobilePhone',
            'label' => 'Mobile Phone',
            'rules' => 'valid_phone'
        ),
        'address' => array(
            'field' => 'address',
            'label' => 'Address',
            'rules' => 'valid_address'
        ),
        'address2' => array(
            'field' => 'address2',
            'label' => 'Address2',
            'rules' => 'max_length[200]'
        ),
        'countryCode' => array(
            'field' => 'countryCode',
            'label' => 'Country Code',
            'rules' => 'alpha|max_length[2]|min_length[2]|valid_country_code'
        ),
    );

    // will remove if any the param not in array before insert or update
    protected $public_attributes = array(
                'firstName',
                'lastName',
                'userType',
                'screenName',
                'dob',
                'userType',
                'city',
                'state',
                'zip',
                'address',
                'phone',
                'mobilePhone',
                'email',
                'passwordHash',
                'accountName',
                'accountHash',
                'accountCode',
                'lastApprovedPrivacyPolicy',
                'lastApprovedTOS',
                'referralCode',
                'gender',                
                'profileComplete',                
                'payPalEmail',                
                'fbId'                
              );
    

    function __construct() {

        if (extension_loaded('newrelic')) 
        {
            newrelic_add_custom_tracer ("parent::__construct");
        }
        
        parent::__construct();
        
    }
    
    public function getCurrentGame($theme)
    {
        $parlays = array('ptbdailyshowdown','sidailyshowdown','cheddadailyshowdown','profootball2016');
        if(in_array($theme, $parlays))
        {
            $rs = $this->db->query("Select parlayCardId from SportParlayConfig 
                where type = ? and convert_tz(now(), 'GMT', 'US/Pacific') between cardDate and endDate limit 1", array($theme));
            if($rs->num_rows())
                return array('code' => 0, 'id' => $rs->row()->parlayCardId, 'type' => 'Parlay', 'statusCode' => 200);
        }
        return array('code' => 1, 'message' => 'No Active card Exists', 'statusCode' => 200);
    }
    
    public function sendGenericEmail($to_address, $subject, $body, $from_address = 'welcome@kizzang.com')
    {
        $this->load->library('email');
        $config['mailtype']     = 'html';
        $config['protocol']     = 'smtp';
        $config['smtp_host']    = 'tls://email-smtp.us-east-1.amazonaws.com';
        $config['smtp_user']    = 'AKIAJNBPMBFTVPTBEWRQ';
        $config['smtp_pass']    = 'AgKt69yJlGzN186y23i+SYSfN6ihp0un7/TcShzKr5Wh';
        $config['smtp_port']    = '465';
        $config['wordwrap']     = TRUE;
        $config['newline']      = "\r\n"; 

        $this->email->initialize($config);

        $this->email->from($from_address);
        $this->email->to($to_address);
        $this->email->subject($subject);
        $this->email->message($body);
        //$this->email->bcc('barton.anderson@kizzang.com');

        if($this->email->send())
            return TRUE;
        else
            return FALSE;            
    }
    
    public function setToken( $token ) 
    {
        $this->token = $token;
    }    

    public function getTransactions($playerId)
    {               
        $rs = $this->db->query("Select convert_tz(created, 'GMT', 'US/Pacific') as winDate, serialNumber as gameId, prizeName, status as pstatus from Payments where playerId = ? order by created DESC limit 20", array($playerId));
        $transactions = array();
        $count = $rs->num_rows();
        foreach($rs->result() as $row)
        {            
            switch(substr($row->gameId, 0, 2))
            {
                case 'KZ': $row->game = 'Scratch Card'; break;
                case 'KT': $row->game = 'Store Purchase'; break;
                case 'KS': $row->game = 'Slot Tournament'; break;
                case 'KP': $row->game = 'Parlay Card'; break;
                case 'KW': $row->game = 'Sweepstakes'; break;
                case 'KB': $row->game = 'Big Game 30'; break;
                case 'KF': $row->game = 'Final 3'; break;
                case 'KC': $row->game = 'Coupons'; break;
                case 'KL': $row->game = 'Magnanimous Millions'; break;
                case 'KR': $row->game = 'Run of a Lifetime'; break;
                default: $row->game = "Unknown";
            }
            switch($row->pstatus)
            {
                case 'Unpaid': 
                case 'Pending':
                    $row->status = 'Potential Winner'; break;
                case 'Paid': $row->status = 'Paid Winner'; break;
                case 'Forfeited': $row->status = 'Forfeited'; break;
                default: $row->status = 'Unknown';
            }
            unset($row->pstatus);
            $transactions[] = $row;
        }
        return array('code' => 0, 'transactions' => $transactions, 'count' => $count, 'statusCode' => 200);
    }
    
    protected function getAllFromDatabase( $offset, $limit) {
        
        // get player with limit and offset and join with gender and role table
        $players = $this->limit( $limit, $offset )->get_many_by( 'accountStatus', 'Active' );

        // get count all player actived
        $count = $this->count_by( 'accountStatus', 'Active' );

        if ( empty( $players) ) 
            return array( 'code' => 1, 'message' => 'Player Not Found', 'statusCode' => 200 );
        else
            return array( 'players' => $players, 'offset' => (int)$offset, 'limit' => (int)$limit, 'count' => $count, 'statusCode' => 200 );
        
    }

    public function getAll( $offset, $limit ) {

        // if enable memcache
        if ( $this->memcacheEnable ) {

            $key = "KEY-Player-getAll" . md5( "getAll_get-offset:$offset-limit:$limit" );

            // the first at all, get the result from memcache
            $result = $this->memcacheInstance->get( $key );

            if ( ! $result ) {

                // if rusult return false with get player from db
                $result = $this->getAllFromDatabase( $offset, $limit );

                // set the result to memcache
                $this->updateMemcache( $key, $result );   
            }

            return $result;
        }

        // if not enable memcache will get from db
        return $this->getAllFromDatabase( $offset, $limit );
    }

    public function getCurrentData( $playerId ) 
    {
        $result = NULL;

        // load gamecount model
        $this->load->model( 'gamecount' );
        $this->load->model( 'position' );
        $this->load->model( 'playperiod');
        
        // get current play period 
        $playPeriodCurrent = $this->playperiod->getByPlayerId( $playerId, 1, 0);
        
        // in the case error
        if ( is_array( $playPeriodCurrent ) ) 
        {    
            // in the case play period not found
            if ( $playPeriodCurrent['statusCode'] === 404 ) 
               $playPeriodCurrent = null;                 
            else 
               return $playPeriodCurrent;                
        }
        else 
        {             
            unset( $playPeriodCurrent->statusCode );
            if(date("Y-m-d", strtotime(str_replace("-", "/", $playPeriodCurrent->endDate))) != date("Y-m-d")) //IF NOT CURRENT
            {
                $playPeriodCurrent = $this->playperiod->add($playerId);
            }
        }
        
        $gameCountCurrent = $this->gamecount->getByPlayPeriodId( $playPeriodCurrent->id, $playerId );

        // in the case error
        if ( is_array( $gameCountCurrent ) ) 
        {
            // in the case game count not found
            if ( $gameCountCurrent['statusCode'] === 404  )                 
               $gameCountCurrent = null;
        }

        //if we have no gameCounts hard set to zero
        if ( !isset( $gameCountCurrent['gameCounts'] ) )
            $gameCountCurrent['gameCounts'] = 0;

        // get current position
        $position = array();

        // format result to return
        $result = array(
                'playPeriod' => $playPeriodCurrent,
                'gameCounts' => ( $playPeriodCurrent ) ? $gameCountCurrent['gameCounts'] : null,
                'position'   => $position,
                'statusCode' => 200
            );

        return $result;
    }

    public function getByIdFromDb( $id ) {

        // get player by id and join with gender and role table
        $temp = $this->get( $id );        
        $result = new stdClass();
        $result->accountData = array('city' => $temp->city, 
            'address' => $temp->address, 
            'dob' => date("m/d/Y", strtotime($temp->dob)),
            'email' => $temp->email,
            'firstName' => $temp->firstName,
            'lastName' => $temp->lastName,
            'gender' => $temp->gender,
            'homePhone' => $temp->phone,
            'mobilePhone' => $temp->phone,
            'state' => $temp->state,
            'zip' => $temp->zip ? sprintf("%05d", $temp->zip) :  '',
            'fbId' => $temp->fbId);
        $result->payPal = $temp->payPalEmail;
        //print_r($result); die();
        if ( empty($result) ) 
            return array( 'code' => 1, 'message' => 'Player Not Found', 'statusCode' => 200 );
        
        $result->statusCode = 200;
        return $result;        
    }
    
    public function getById( $id, $isGranted = FALSE ) {
        $isGranted = true;
        if ( ! $isGranted ) 
        {
            // compare id with player id from memcache
            $isGranted = $this->checkActionOwner( $id );
         
            if ( $isGranted !== TRUE ) 
                return $isGranted;            
        }
        
        // if enable memcache
        $this->memcacheEnable = false;
        if ( $this->memcacheEnable ) 
        {
            $key = "KEY-User-playerId-$id";

            // the first at all, get the result from memcache
            $result = NULL; //$this->memcacheInstance->get( $key );

            if ( ! $result ) 
            {    
                $result = NULL; //$this->getByIdFromDb( $id );
             
                if ( is_object( $result ) )                     
                    $this->updateMemcache( $key, $result );                

                return $result;
            }
        }

        // if not enable memcache will get player from db
        return $this->getByIdFromDb( $id );

    }
    
    public function getSiteUrl()
    {
        $url = "kizzang.com";
        
        switch(getenv("ENV"))
        {
            case "dev": $url = "dev.kizzang.com"; break;
            case "stage": $url = "qa.kizzang.com"; break;
            case "prod": $url = "kizzang.com"; break;
        }
        
        return $url;
    }
    
    public function add( $data, $skipValidation = FALSE ) {

        // if user not enter any data
        if ( empty( $data ) )         
            return array( 'code' => 1, 'message' => 'Please enter the required data', 'statusCode' => 200 );
        

        $this->form_validation->reset_validation();
        $this->form_validation->set_params( $data );
        $this->form_validation->set_rules( $this->validate );

        if ( ! $skipValidation && $this->form_validation->run() === FALSE )             
            return array( 'code' => 2, 'message' => $this->form_validation->validation_errors(), 'statusCode' => 200 );
                      

        if ( ! $skipValidation  && ( empty( $data['option3'] ) || ! filter_var( $data['option3'], FILTER_VALIDATE_BOOLEAN ) ) )
            return array( 'code' => 2, 'message' => 'Please read and accept our Privacy Policy and Terms of Service', 'statusCode' => 200 );        
        
        $rs = $this->db->query("Select * from Users where accountName = ?", $data['accountName']);
        if($rs->num_rows())
            $result = array( 'code' => 4, 'message' => 'This email address or phone number has already been used. Please enter a new one.', 'statusCode' => 200 );
        
        $insertId = $this->insert( $data, TRUE );

        if ( $insertId ) 
        {
         
            $result = $this->getById( $insertId, TRUE );
            $result->statusCode = 201;
                        
            // if player register with a email            
            if ( $this->form_validation->valid_email( $result->accountName ) ) 
            {                            
                $body = $this->load->view("emails/wrapper", array('content' => $this->load->view('emails/verificationCode', array('code' => $result->accountCode), true), 'emailCode' => md5($result->accountName)), true);
                $isSent = $this->sendGenericEmail($result->accountName, "Email Confirmation - Kizzang", $body);
            }
            else 
            {
                $newAccountMsg = "Kizzang registration. Confirmation code: " . $result->accountCode;
                $isSent = $this->sendSMS( $result->accountName, $newAccountMsg, $result->accountCode );
            }

            if ( $isSent !== TRUE ) return $isSent;

        }
        else 
        {            
            $errorMessage = $this->db->_error_message();
            log_message('error', 'Insert player: ' . $errorMessage );
            
            $result = array( 'code' => 5, 'message' => $errorMessage, 'statusCode' => 200 );            
        }

        return $result;                
    }
    
    /**
    * resend a text message to player using supplied phone number
    * @param  array $data phone number data
    * @return array
    */
    public function phoneResendCode( $data )
    {
        $result =  array( 'code' => 5, 'message' => 'Resend code unknown error occurred', 'statusCode' => 200 );
                    
        if ( isset( $data['phoneNumber'] ) )
        {            
            $player = $this->get_by( 'accountName', $data['phoneNumber'] );
            
            if ( empty( $player ) )
            {
                $result = array( 'code' => 6, 'message' => 'Player Not Found', 'statusCode' => 200 );
            }               
            else 
            {
                if ( $player->emailVerified == 0 ) 
                {    
                    $tokenSessionBase64 = $this->base64Encode(md5($data['phoneNumber']));
                    $tokenSessionKey    = 'KEY-phone-resend-code-session-token' . $tokenSessionBase64;
                    $phoneResendSessionHash   = $this->memcacheInstance->get( $tokenSessionKey );
                    $now = strtotime( date('Y-m-d H:i:s') );
                    $minutes = 5;
                    $timeTillReset = $minutes;                  // Minutes till reset - 5 minutes
                    $maxResendAttemptsPerSession = 3;
                    
                    if ( $phoneResendSessionHash ) 
                    {
                        // Round minutes to x.xx
                        $timeTillResetInMin = round(( $now - $phoneResendSessionHash['attemptDate'] ) / 60, 2);
                        
                        // 3 hours before resent can happen
                        if ( $timeTillResetInMin < $timeTillReset ) 
                        {    
                            $attemptCount = $phoneResendSessionHash['attemptCount'] + 1;
                        }
                        else 
                        {
    
                            $attemptCount = 1;
                            $phoneResendSessionHash['attemptDate'] = $now;
                        }
                        
                    }
                    else 
                    {    
                        $attemptCount = 1;
                        $phoneResendSessionHash['attemptDate'] = $now;
                    }

                    $phoneResendSessionHash['attemptCount'] = $attemptCount;
 
                    $this->updateMemcache( $tokenSessionKey, $phoneResendSessionHash, (($minutes+1)*60) );

                    $timeRemaining = $timeTillReset - $timeTillResetInMin;
                    
                    if ( $attemptCount <= $maxResendAttemptsPerSession )
                    {
                        
                        $resendMsg = "Kizzang registration code: " . $player->phoneCode;
                        
                        if ( $attemptCount == $maxResendAttemptsPerSession )
                            $resendMsg .= ", final attempt until " . $timeRemaining . " minutes pass";
                        else
                            $resendMsg .= ", attempt " . $attemptCount. " of " . $maxResendAttemptsPerSession;
                        
                        $isSent = $this->sendSMS( $data['phoneNumber'], $resendMsg, $player->phoneCode );
                        
                        if ( $isSent !== TRUE )
                            $result = $isSent;                                                    
                        else                            
                            $result =  array( 'code' => 4, 'message' => 'Unable to send text message', 'statusCode' => 200 );                        
                    }
                    else 
                    {                       
                        $result =  array( 'code' => 7, 'message' => 'Exceeded resend text message count. Please wait ' . $timeRemaining . ' minutes to try again.', 'statusCode' => 200 );
                    }
                }
                else 
                {                   
                    $result =  array( 'code' => 3, 'message' => 'Player phone already confirmed', 'statusCode' => 200 );
                }
            }
        }
        else 
        {            
            $result =  array( 'code' => 2, 'message' => 'Invalid data', 'statusCode' => 200 );
        }
        
        return $result;
    }    
    
    /**
    * update a player by id
    * @param  int $id  player id
    * @param  array $data player data
    * @return array if error else is object
    */
    public function edit( $id, $data ) 
    {
        $validate = array(        
            'paypalEmail' => array( 
                'field' => 'paypalEmail', 
                'label' => 'PayPal Email',
                'rules' => 'required|valid_email|max_length[120]|trim'
            ),
            'city' => array(
                'field' => 'city',
                'label' => 'City',
                'rules' => 'required|xss_clean|max_length[50]||regex_match["^([a-zA-Z\s]+)$"]|trim'
            ),             
            'address' => array(
                'field' => 'address',
                'label' => 'Address',
                'rules' => 'required|xss_clean|max_length[50]|trim'
            ),
            'zip' => array(
                'field' => 'zip',
                'label' => 'Zip Code',
                'rules' => 'required|xss_clean|max_length[5]|numeric|trim'
            ),
            'state' => array(
                'field' => 'state',
                'label' => 'State',
                'rules' => 'required|xss_clean|exact_length[2]|trim'
            ),
            'mobilePhone' => array(
                'field' => 'mobilePhone',
                'label' => 'Mobile Phone',
                'rules' => 'required|xss_clean|max_length[10]|numeric|trim'
            ),
            'firstName' => array(
                'field' => 'firstName',
                'label' => 'First Name',
                'rules' => 'required|xss_clean|regex_match["^([a-zA-Z\s]+)$"]|max_length[50]|trim'
            ),
            'lastName' => array(
                'field' => 'lastName',
                'label' => 'Last Name',
                'rules' => 'required|xss_clean|regex_match["^([a-zA-Z\s\-]+)$"]|max_length[50]|trim'
            )
        );
        
        if($data['password'])
        {
             $validate['password'] = array(
                'field' => 'password',
                'label' => 'Password',
                'rules' => 'required'
            );
        }
        
        if(isset($data['userType']) && $data['userType'] == "Guest")
        {
            $validate['bday'] = array(
                'field' => 'bday',
                'label' => 'bday',
                'rules' => 'required|numeric'
            );
            $validate['bmonth'] = array(
                'field' => 'bmonth',
                'label' => 'bmonth',
                'rules' => 'required|numeric'
            );
            $validate['byear'] = array(
                'field' => 'byear',
                'label' => 'byear',
                'rules' => 'required|numeric'
            );
            $validate['gender'] = array(
                'field' => 'gender',
                'label' => 'Gender',
                'rules' => 'required|xss_clean|max_length[50]|trim'
            );
        }
        
        $this->form_validation->reset_validation();
        $this->form_validation->set_params( $data );
        $this->form_validation->set_rules( $validate );
        
        if ( $this->form_validation->run() === FALSE ) 
            return array( 'code' => 1, 'messages' => $this->form_validation->validation_errors(), 'statusCode' => 200 );
        
        $dups = $this->checkDuplicates($id, $data['paypalEmail'], $data['mobilePhone'], $data['paypalEmail']);
        if(is_array($dups))
            return $dups;
        
        $rec = array('firstName' => trim($data['firstName']),
            'lastName' => trim($data['lastName']),
            'dob' => $data['byear'] . "-" . $data['bmonth'] . "-" . $data['bday'],
            'email' => $data['paypalEmail'],
            'accountName' => $data['paypalEmail'],
            'payPalEmail' => $data['paypalEmail'],
            'mobilePhone' => $data['mobilePhone'],
            'phone' => isset($data['homePhone']) ? $data['homePhone'] : $data['mobilePhone'],
            'city' => $data['city'],
            'state' => $data['state'],
            'gender' => ucfirst($data['gender']),
            'address' => trim($data['address']),
            'userType' => $data['userType'] == 'Guest' ? 'User' : $data['userType'],
            'zip' => $data['zip'],
            'profileComplete' => 1,
            'screenName' => ucfirst($data['firstName']) . " " . substr(ucfirst($data['lastName']), 0, 1));
        
        if($data['password'])
            $rec['passwordHash'] = md5($data['password']);
        
        $this->update($id, $rec, true);
        $view = $this->load->view("emails/wrapper", array('content' => $this->load->view("emails/welcome", array('name' => $data['firstName'] . " " . $data['lastName']), true), 'emailCode' => md5($data['paypalEmail']), 'emailCode' => md5($data['paypalEmail'])), true);
        $this->sendGenericEmail($data['paypalEmail'], "Welcome to Kizzang", $view);
        
        if($data['userType'] == 'Guest')
        {
            //Add them to the guest conversions
            $player = $this->get_by(array('id' => $id));
            $rec = array('conversionTime' => date("Y-m-d H:i:s"), 'playerId' => $id, 'accountCreated' => $player->lastApprovedTOS, 'secDiff' => strtotime("now") -  strtotime($player->lastApprovedTOS));
            $this->db->insert("GuestConversions", $rec);
            
            $this->load->model('sessions');
            $playerData = $this->sessions->getPlayerData($this->token);
            $playerData['playerRole'] = 'User';
            $this->db->query('Update Sessions set player_data = ? where id = ?', array(json_encode($playerData), $this->token));
            //print $this->db->last_query(); die();
            $this->memcacheInstance->set("KEY-Player-Data-" . $this->token, $playerData);
        }
                
        $key = "KEY-User-playerId-$id";        
        $this->memcacheInstance->delete( $key );
        
        return array('code' => 0, 'message' => 'User updated Successfully', 'statusCode' => 200);
    }
    
    private function checkDuplicates($playerId, $email, $phone, $payPalEmail)
    {
        $rs = $this->db->query("Select id from Users where id <> ? and (email = ? or accountName = ? or payPalEmail = ? or email = ? or accountName = ? or payPalEmail = ?)", array($playerId, $email, $email, $email, $payPalEmail, $payPalEmail, $payPalEmail));        
        if($rs->num_rows())
            return array('code' => 2, 'message' => 'This email address is already registered. Please choose another.', 'statusCode' => 200);
        
        $rs = $this->db->query("Select id from Users where id <> ? and (phone = ? or accountName = ?)", array($playerId, $phone, $phone));
        if($rs->num_rows())
            return array('code' => 2, 'message' => 'This phone number is already registered. Please choose another.', 'statusCode' => 200);
        
        return false;
    }
   

    /**
     * update memcache by key
     * @param  string $key unique key to detect memcache
     * @param  mixed $result
     * @param  int $expire milisecond
     * @return none
     */
    public function updateMemcache( $key, $result, $expire = null ) {

        // expire = 0 the item never expires
        $expire = ( $expire !== NULL ) ? $expire : (strtotime("tomorrow -1 second") - strtotime("now"));

        // update to memcache for player
        $this->memcacheInstance->set( $key, $result, $expire );
    }    

    public function phoneVerified( $data ) {

        $validate = array( 'email' => array( 
            'field' => 'phoneCode', 
            'label' => 'Phone Code',
            'rules' => 'required|numeric|max_length[6]|min_length[6]|trim'
        ) );

        // reset error messages
        $this->form_validation->reset_validation();
        
        // set form data to validate
        $this->form_validation->set_params( $data );

        // set rules validation
        $this->form_validation->set_rules( $validate );

        // if validation fail
        if ( $this->form_validation->run() === FALSE ) {

            $error = array( 'code' => 1, 'messages' => $this->form_validation->validation_errors(), 'statusCode' => 400 );

            return $error;
        }
        else {

            $player = $this->get_by( 'phoneCode', $data['phoneCode'] );

            $result = $this->verifiedAccount( $player );

            return $result;
        }
    }

    /**
     * ignore a email by emailCode
     * @param  string $emailCode
     * @return object or array
     */
    public function ignoreVerified( $emailCode, $data) {

        $rs = $this->db->query("Select * from Users where md5(email) = ?", array($emailCode));
        if (!$rs->num_rows()) 
        {
            $result =  array( 'code' => 1, 'message' => 'Player Not Found', 'statusCode' => 200 );
            return $result;
        }
        else 
        {
            $player = $rs->row();
            //enum('Normal','Commercial Opt Out','Transaction Opt Out','Both Opt Out')
            $status = 'Normal';
            $userType = $player->userType;
            $accountStatus = $player->accountStatus;
            if(!isset($data['commercial_opt_in']) && !isset($data['transactional_opt_in']))
            {
                $status = 'Both Opt Out';
                $userType = 'Guest';
                $accountStatus = 'Suspended';
            }
            elseif(!isset($data['commercial_opt_in']))
            {
                $status = 'Commercial Opt Out';
            }
            elseif(!isset($data['transactional_opt_in']))
            {
                $status = 'Transaction Opt Out';
                $userType = 'Guest';
                $accountStatus = 'Suspended';
            }
            
            $this->db->where('id', $player->id);
            $this->db->update('Users', array('emailStatus' => $status, 'accountStatus' => $accountStatus, 'userType' => $userType));
            
            $id = $this->base64Encode(array(md5($player->accountCode), $player->passwordHash ));
            $this->load->model('sessions');
            $this->sessions->destroy($id);
            
            return array('code' => 0, 'message' => 'Opt In Updated.', 'statusCode' => 200 );
        }
    }

    /**
     * get player by email and password
     * @param  string $email
     * @param  password $password
     * @return int or array
     */
    public function getByEmailPassword( $emailPhone, $password = NULL, $fbId = NULL ) 
    {
        
        $where = array('accountName' => $emailPhone);

        if ( $fbId ) 
            $where['fbId'] = $fbId;
        else if ( $password ) 
            $where['passwordHash'] = $password;

        $player = $this->get_by( $where );

        if ( empty( $player ) )
            return array( 'code' => 1, 'message' => 'Invalid email, phone number or password', 'statusCode' => 200 );
        else
            return $player;
    }
    
    /**
     * hash the email or phone number
     * @param  $email is email address or a phone number
     * @return hash of email or phone in array for where clause
     */
    public function getHashEmailPhone( $emailPhone )
    {
        $hash = array();
        
        if ( $this->form_validation->valid_email( $emailPhone ) ) {

            $hash['emailHash'] = $this->hashEmailPhone( $emailPhone );
        }
        else {

            $hash['phoneHash'] = $this->hashEmailPhone( $emailPhone );
        }
        
        return $hash;
    }
    
    /**
     * hash the email or phone number
     * @param  $email is email address or a phone number
     * @return hash of email or phone
     */
    public function hashEmailPhone( $emailPhone )
    {
        $hash = null;
        
        if ( $this->form_validation->valid_email( $emailPhone ) ) {

            $hash = md5( strtolower( $emailPhone ) );
        }
        else {
            
            $hash = md5( preg_replace( "/[^0-9]/", '', ("+1" . ltrim($emailPhone, "+1" ))) );
        }
        
        return $hash;
    }
    
    public function phoneCreate($data)
    {
        $this->load->model("playerlogin");
         $validate = array(
            'phone' => array(
                'field' => 'phone', 
                'label' => 'Email or phone number',
                'rules' => 'required|valid_email_phone|max_length[120]|trim'
            ),            
            'mobileType' => array(
                'field' => 'mobileType',
                'rules' => 'xss_clean|max_length[10]|trim'
            ),
            'loginSource' => array(
                'field' => 'loginSource',
                'rules' => 'xss_clean|max_length[10]|trim'
            ),
            'ipAddress' => array(
                'field' => 'ipAddress',
                'rules' => 'xss_clean|max_length[45]|trim'
            ),
            'userAgent' => array(
                'field' => 'userAgent',
                'rules' => 'xss_clean|max_length[2000]|trim'
            ),            
            'firstName' => array(
                'field' => 'firstName',
                'label' => 'First Name',
                'rules' => 'required|max_length[25]|trim|regex_match["^([a-zA-Z\s]+)$"]'
            ),            
            'lastName' => array(
                'field' => 'lastName',
                'label' => 'Last Name',
                'rules' => 'required|max_length[25]|trim|regex_match["^([a-zA-Z\s]+)$"]'
            ),
            'dob' => array(
                'field' => 'dob',
                'label' => 'Date of birth',
                'rules' => 'required|valid_db_date'
            ),
            'appId' => array(
                'field' => 'appId',
                'rules' => 'xss_clean|max_length[10]|trim'
            )
            
        );
         
         if(isset($data['first_name']))
         {
             $data['firstName'] = $data['first_name'];
             unset($data['first_name']);
         }
         
         if(isset($data['last_name']))
         {
             $data['lastName'] = $data['last_name'];
             unset($data['last_name']);
         }
         
         foreach($data as $key => $value)
            $data[$key] = trim($value);
         
         $data['phone'] = "+1" . ltrim($data['phone'], "+1" );

        $this->form_validation->reset_validation();
        $this->form_validation->set_params( $data );
        $this->form_validation->set_rules( $validate );
        $data['email'] = $data['phone'];

        if ( $this->form_validation->run() === FALSE )
        {
            $error = $this->form_validation->validation_errors();
            return array( 'code' => 1, 'message' => $error[0], 'statusCode' => 200 );
        }
        
        $phone = preg_replace( "/[^0-9]/", '', $data['phone']);
        $passwordHash = md5(NULL);
        $token = $this->base64Encode( array( md5($phone), $passwordHash ) );
        $device_id = isset($data['deviceId']) ? $data['deviceId'] : rand(1, 1000000);
        unset($data['deviceId']);
        
        $dup = $this->get_by(array('accountName' => $phone));
        if($dup)
            return array( 'code' => 2, 'message' => 'Duplicate Phone Found - ' . $phone, 'statusCode' => 200 );
                        
        $date = date("Y-m-d H:i:s");
        $phone = ltrim($data['phone'], "+1");

        $rec = array('firstName' => $data['firstName'],
            'lastName' => $data['lastName'],
            'screenName' => ucfirst(strtolower($data['firstName'])) . " " . strtoupper(substr($data['lastName'], 0, 1)),
            'accountName' => $phone,
            'accountCode' => sprintf("%05d", rand(1, 999999)),
            'phone' => $phone,
            'passwordHash' => $passwordHash,
            'accountStatus' => 'Active',
            'gender' => 'None',
            'lastApprovedTOS' => $date,
            'lastApprovedPrivacyPolicy' => $date);
                     
        $insertId = $this->insert($rec, true);
        
        if(!$insertId)
            return array('code' => 3, 'message' => 'Error Adding user', 'statusCode' => 200);
                
        $player = $this->get_by( array('id' => $insertId));
        $ip = $this->getIpAddress();
        
        if(strstr($_SERVER['HTTP_USER_AGENT'], "iOS"))
                $data['mobileType'] = 'iOS';
        elseif(strstr($_SERVER['HTTP_USER_AGENT'], "Android"))
                $data['mobileType'] = "Android";
        
        $playerLoginData = array('playerId'         => $player->id, 
                    'lastLogin'        => date( 'Y-m-d H:i:s' ), 
                    'loginType'        => 'Normal', 
                    'loginSource'      => isset( $data['loginSource'] ) ? $data['loginSource'] : 'Web', 
                    'ipAddress'        => $ip,
                    'appId'            => isset( $data['appId'] ) ? $data['appId'] : 0,                    
                    'userAgent'        => $_SERVER['HTTP_USER_AGENT'],
                    'mobileType'       => isset( $data['mobileType'] ) ? $data['mobileType'] : null
            );
        
        $this->playerlogin->insert($playerLoginData);
                
        $this->sessions->destroy($token);
        $player_data = array('playerId' => $player->id,
            'playerRole' => $player->userType,
            'playerEmail' => $player->accountName,
            'isSuspended' => $player->isSuspended,
            'isDeleted' => $player->isDeleted,            
            'newUserFlow' => $player->newUserFlow ? true : false,
            'screenName' => $player->screenName,
            'emailCode' => $player->accountCode,
            'phoneCode' => $player->accountCode
        );
        
        $this->load->config('rest');        
        $version    = config_item('rest_version');

        $this->load->config('app');
        $app_version    = config_item('app_version');

        $session_data = array('version' => $version,
            'appVersion' => $app_version);
        
        $rec = array(
            'player_data' => json_encode($player_data),
            'session_data' => json_encode($session_data));
        
        $this->sessions->add($token, $device_id, $rec);
        
        $newAccountMsg = "Kizzang registration. Confirmation code: " . $player->accountCode;
        $result = $this->sendSMS( $data['phone'], $newAccountMsg, $player->accountCode );        
        
        $ret = array('playerId' => $insertId,
            'playerRole' => $player->userType,
            'phone' => $player->accountName,
            'newUserFlow' => $player->newUserFlow ? true : false,
            'screenName' => $player->screenName,
            'gender' => $player->gender,
            'appVersion' => $app_version,           
            'token' => $token,
            'now' => $date,
            'code' => 0,
            'statusCode' => 200);
        
        return $ret;
    }
    
    public function accountVerify($data)
    {
        $validate = array( 'email' => array( 
            'field' => 'email', 
            'label' => 'Email or Phone number',
            'rules' => 'required|valid_email_phone|max_length[120]|trim'
        ),
        'confirmation_code' => array( 
            'field' => 'confirmation_code', 
            'label' => 'Confirmation Code',
            'rules' => 'required|numeric|trim'
        ),
        'password' => array( 
            'field' => 'password', 
            'label' => 'Password',
            'rules' => 'required|trim'
        ));
        
        if(isset($data['phone']))
        {
            $data['email'] = $data['phone'];
            unset($data['phone']);
        }
        
        $this->form_validation->reset_validation();
        $this->form_validation->set_params( $data );
        $this->form_validation->set_rules( $validate );

        if ( $this->form_validation->run() === FALSE )
           return array( 'code' => 1, 'message' => $this->form_validation->validation_errors(), 'statusCode' => 200 );
                
        $player = NULL;
        $email = strtolower( $data['email'] );
        if(is_numeric($email))
        {
            $rs = $this->db->query("Select * from Users where accountName = ? or phone = ?", array($email, $email));
            if($rs->num_rows())
                $player = $rs->row();
        }
        else
        {
            $rs = $this->db->query("Select * from Users where accountName = ? or email = ?", array($email, $email));
            if($rs->num_rows())
                $player = $rs->row();
        }        
        
        if(!$player)
            return array( 'code' => 2, 'message' => 'Account not found or Confirmation Code incorrect', 'statusCode' => 200 );
        
        $passwordHash = md5($data['password']);
        $this->db->query("Update Users set passwordHash = ? where id = ?", array($passwordHash, $player->id));
        
        return array('code' => 0, 'message' => 'Password Updated', 'statusCode' => 200);
        
    }
    
    public function verifyResend($data)
    {
        $validate = array( 'email' => array( 
            'field' => 'email', 
            'label' => 'Email or phone number',
            'rules' => 'required|valid_email_phone|max_length[120]|trim'
        ) );
        
        $this->form_validation->reset_validation();
        $this->form_validation->set_params( $data );
        $this->form_validation->set_rules( $validate );

        if ( $this->form_validation->run() === FALSE )         
            return array( 'code' => 1, 'message' => $this->form_validation->validation_errors(), 'statusCode' => 200 );
        
        $player = NULL;
        $email = strtolower( $data['email'] );
        if(is_numeric($email))
        {
            $rs = $this->db->query("Select * from Users where accountName = ? or phone = ?", array($email, $email));
            if($rs->num_rows())
                $player = $rs->row();
        }
        else
        {
            $rs = $this->db->query("Select * from Users where accountName = ? or email = ?", array($email, $email));
            if($rs->num_rows())
                $player = $rs->row();
        }        
        
        if(!$player)
            return array('code' => 2, 'message' => 'Account Does not Exist', 'statusCode' => 200);
        
        $confirmation_code = sprintf("%06d", rand(0,999999));
        $this->db->query("Update Users set accountCode = ? where id = ?", array($confirmation_code, $player->id));
        
        if(!is_numeric($email))
        {     
            $body = $this->load->view("emails/wrapper", array('content' => $this->load->view('emails/verificationCode', array('code' => $confirmation_code), true), 'emailCode' => md5($player->accountName)), true);
            $isSent = $this->sendGenericEmail($data['email'], "Kizzang - Verification Code", $body);
            if(!$isSent)
                return array( 'code' => 3, 'message' => 'Error Sending Email', 'statusCode' => 200 );
        }
        else
        {        
            $newAccountMsg = "Kizzang registration. Confirmation code: " . $confirmation_code;
            $result = $this->sendSMS( "+1" . ltrim($data['email'], "+1" ), $newAccountMsg, $confirmation_code );

            if(isset($result['code']) && $result['code'] != 0)
                return array( 'code' => 3, 'message' => 'Error Sending SMS Text', 'statusCode' => 200 );
        }
        return array( 'code' => 0, 'message' => 'Success', 'statusCode' => 200 );
    }
    
    public function emailCreate($data)
    {
        $this->load->model("playerlogin");
         $validate = array(
            'email' => array(
                'field' => 'email', 
                'label' => 'Email or phone number',
                'rules' => 'required|valid_email_phone|max_length[120]|trim'
            ),            
            'mobileType' => array(
                'field' => 'mobileType',
                'rules' => 'xss_clean|max_length[10]|trim'
            ),
            'loginSource' => array(
                'field' => 'loginSource',
                'rules' => 'xss_clean|max_length[10]|trim'
            ),
            'ipAddress' => array(
                'field' => 'ipAddress',
                'rules' => 'xss_clean|max_length[45]|trim'
            ),
            'userAgent' => array(
                'field' => 'userAgent',
                'rules' => 'xss_clean|max_length[2000]|trim'
            ),            
            'firstName' => array(
                'field' => 'firstName',
                'label' => 'First Name',
                'rules' => 'required|max_length[25]|trim|regex_match["^([a-zA-Z\s]+)$"]'
            ),            
            'lastName' => array(
                'field' => 'lastName',
                'label' => 'Last Name',
                'rules' => 'required|max_length[25]|trim|regex_match["^([a-zA-Z\s]+)$"]'
            ),
            'dob' => array(
                'field' => 'dob',
                'label' => 'Date of birth',
                'rules' => 'required|valid_db_date'
            ),
            'appId' => array(
                'field' => 'appId',
                'rules' => 'xss_clean|max_length[10]|trim'
            )
            
        );

         if(isset($data['first_name']))
         {
             $data['firstName'] = $data['first_name'];
             unset($data['first_name']);
         }
         
         if(isset($data['last_name']))
         {
             $data['lastName'] = $data['last_name'];
             unset($data['last_name']);
         }
         
        $this->form_validation->reset_validation();
        $this->form_validation->set_params( $data );
        $this->form_validation->set_rules( $validate );

        if ( $this->form_validation->run() === FALSE )      
        {
            $error = $this->form_validation->validation_errors();
            return array( 'code' => 1, 'message' => $error[0], 'statusCode' => 200 );
        }
        foreach($data as $key => $value)
            $data[$key] = trim($value);
        
        $emailHash = md5(strtolower($data['email']));
        $passwordHash = md5($data['password']);
        $token = $this->base64Encode( array( $emailHash, $passwordHash ) );
        $device_id = isset($data['deviceId']) ? $data['deviceId'] : rand(1, 1000000);
        unset($data['deviceId']);
        
        $dup = $this->get_by(array('accountName' => $emailHash));
        if($dup)
            return array( 'code' => 2, 'message' => 'Duplicate Email Found', 'statusCode' => 200 );
                
        $data['dob'] = date('Y-m-d', strtotime($data['dob']));
        $date = date("Y-m-d H:i:s");
        $data = array_merge($data, array('passwordHash' => $passwordHash,            
            'screenName' => ucfirst(strtolower($data['firstName'])) . " " . strtoupper(substr($data['lastName'], 0, 1)),            
            'gender' => 'None',
            'accountName' => $data['email']));
 
        $rec = array('firstName' => $data['firstName'],
            'lastName' => $data['lastName'],
            'dob' => $data['dob'],
            'userType' =>'User',
            'screenName' => ucfirst(strtolower($data['firstName'])) . " " . strtoupper(substr($data['lastName'], 0, 1)),
            'accountName' => $data['email'],
            'accountCode' => md5($data['email']),
            'passwordHash' => $passwordHash,
            'email' => $data['email'],
            'gender' => 'None',
            'lastApprovedTOS' => $date,
            'lastApprovedPrivacyPolicy' => $date);
                
        $insertId = $this->insert($rec, true);        
        
        if(!$insertId)
            return array('code' => 3, 'message' => 'Error Adding user', 'statusCode' => 200);
        
        $player = $this->get_by(array('id' => $insertId));        

        if(strstr($_SERVER['HTTP_USER_AGENT'], "iOS"))
                $data['mobileType'] = 'iOS';
        elseif(strstr($_SERVER['HTTP_USER_AGENT'], "Android"))
                $data['mobileType'] = "Android";
        
        $ip = $this->getIpAddress();
        $playerLoginData = array('playerId'         => $player->id, 
                    'lastLogin'        => date( 'Y-m-d H:i:s' ), 
                    'loginType'        => 'Normal', 
                    'loginSource'      => isset( $data['loginSource'] ) ? $data['loginSource'] : 'Web', 
                    'ipAddress'        => $ip,
                    'appId'            => isset( $data['appId'] ) ? $data['appId'] : 0,                    
                    'userAgent'        => $_SERVER['HTTP_USER_AGENT'],
                    'mobileType'       => isset( $data['mobileType'] ) ? $data['mobileType'] : null
            );
        
        $this->playerlogin->insert($playerLoginData);
        
        $this->sessions->destroy($token);
        $player_data = array('playerId' => $player->id,
            'playerRole' => $player->userType,
            'playerEmail' => $player->accountEmail,
            'isSuspended' => $player->isSuspended,
            'isDeleted' => $player->isDeleted,            
            'newUserFlow' => $player->newUserFlow ? true : false,
            'screenName' => $player->screenName,
            'emailCode' => $player->accountCode,
            'phoneCode' => $player->accountCode
        );
        
        $this->load->config('rest');        
        $version    = config_item('rest_version');

        $this->load->config('app');
        $app_version    = config_item('app_version');

        $session_data = array('version' => $version,
            'appVersion' => $app_version);
        
        $rec = array(
            'player_data' => json_encode($player_data),
            'session_data' => json_encode($session_data));
        
        $this->sessions->add($token, $device_id, $rec);
               
        $body = $this->load->view("emails/welcome", array('url' => $this->getSiteUrl(), 'emailCode' => $player->emailCode, 'name' => $player->accountData['firstName'] . " " . $player->accountData['lastName']), true);
        $this->sendGenericEmail($data['email'], "Welcome", $body);
        
        $ret = array('playerId' => $insertId,
            'playerRole' => $player->userType,
            'email' => $data['email'],
            'newUserFlow' => $player->newUserFlow ? true : false,
            'screenName' => $player->screenName,
            'gender' => $player->gender,
            'appVersion' => $app_version,           
            'token' => $token,
            'now' => $date,
            'code' => 0,
            'statusCode' => 200);
        
        return $ret;
    }
    
    private function getIpAddress()
    {
        return getenv('HTTP_CLIENT_IP')?:
                getenv('HTTP_X_FORWARDED_FOR')?:
                getenv('HTTP_X_FORWARDED')?:
                getenv('HTTP_FORWARDED_FOR')?:
                getenv('HTTP_FORWARDED')?:
                getenv('REMOTE_ADDR');
    }
  
    public function login( $data, $fbLogin = FALSE ) {
        
        // Ignore the isDeleted key when making db queries for logins 
        // so we can respond to accounts that have been deleted
        $this->soft_delete = FALSE;
        $this->load->model("playerlogin");
        $this->load->model("playperiod");
                
        // defined rule validation
        $validate = array(
            'email' => array(
                'field' => 'email', 
                'label' => 'Email or phone number',
                'rules' => 'required|valid_email_phone|max_length[120]|trim'
            ),
            'deviceId' => array(
                'field' => 'deviceId',
                'label' => 'Device Id',
                'rules' => 'required|trim'
            ),
            'isContinue' => array(
                'field' => 'isContinue',
                'label' => 'isContinue',
                'rules' => 'regex_match["^([0,1]+)$"]'
            ),
            'mobileType' => array(
                'field' => 'mobileType',
                'rules' => 'xss_clean|max_length[10]|trim'
            ),
            'loginSource' => array(
                'field' => 'loginSource',
                'rules' => 'xss_clean|max_length[10]|trim'
            ),
            'ipAddress' => array(
                'field' => 'ipAddress',
                'rules' => 'xss_clean|max_length[45]|trim'
            ),
            'userAgent' => array(
                'field' => 'userAgent',
                'rules' => 'xss_clean|max_length[2000]|trim'
            ),
            'isRegistration' => array(
                'field' => 'isRegistration',
                'rules' => 'is_numeric'
            ),
            'appId' => array(
                'field' => 'appId',
                'rules' => 'xss_clean|max_length[10]|trim'
            )
            
        );        

        // if login from account kizzang will required password
        if ( ! $fbLogin ) 
        {
            $validate['password'] = array(
                'field' => 'password',
                'label' => 'Password',
                'rules' => 'required|xss_clean|max_length[32]|trim'
            );
        }                
        
        $this->form_validation->reset_validation();
        $this->form_validation->set_params( $data );
        $this->form_validation->set_rules( $validate );

        // if validation fail
        if ( $this->form_validation->run() === FALSE ) 
        {
            return array( 'code' => 1, 'message' => $this->form_validation->validation_errors(), 'statusCode' => 200 );
        }
        else 
        {    
            // encrypt email, password and deviceId
            $emailHash      = md5( $data['email'] );           
            $passwordHash   = md5( NULL );
            $player = NULL;
            
            if ( ! $fbLogin )
                $passwordHash = md5( $data['password'] );
            
            if($fbLogin)
            {
                $player = $this->get_by(array('fbId' => $data['fbId']));
                if(!$player)
                    return array( 'code' => 1, 'message' => 'Invalid email or password', 'statusCode' => 200 );                
            }
            else
            {
                $player = $this->get_by(array('accountName' => trim($data['email']), 'passwordHash' => $passwordHash));
                
                if(!$player)
                    return array( 'code' => 1, 'message' => 'Invalid email or password', 'statusCode' => 200 );                
            }                        

            $playerLoginData = array('playerId'         => $player->id, 
                    'lastLogin'        => date( 'Y-m-d H:i:s' ), 
                    'loginType'        => $fbLogin ? 'Facebook' : 'Normal', 
                    'loginSource'      => isset( $data['loginSource'] ) ? $data['loginSource'] : 'Web', 
                    'ipAddress'        => $this->getIpAddress(),
                    'appId'            => isset( $data['appId'] ) ? $data['appId'] : 0,
                    'isRegistration'   => isset( $data['isRegistration'] ) ? (int)$data['isRegistration'] : 0,
                    'userAgent'        => $_SERVER['HTTP_USER_AGENT'],
                    'mobileType'       => isset( $data['mobileType'] ) ? $data['mobileType'] : null
            );
            
            $this->playerlogin->insert($playerLoginData, true);            
            
            // generate token with encode base64
            $id = $this->base64Encode( array( $emailHash, $passwordHash ) );
            
            //Destroy old session and start from new
            $this->sessions->destroy($id);
            $player_data = array('playerId' => $player->id,
                'playerRole' => $player->userType,
                'playerEmail' => $data['email'],
                'isSuspended' => $player->accountStatus == "Suspended" ? 1 : 0,
                'isDeleted' => $player->accountStatus == "Deleted" ? 1 : 0,
                'emailNotificationId' => 0,
                'screenName' => $player->screenName,
                'emailCode' => md5($player->email),
                'phoneCode' => md5($player->phone)
            );
                        
             if($fbLogin)
                $player_data['fbid'] = $data['fbId'];
            
            $this->load->config('rest');        
            $version    = config_item('rest_version');

            $this->load->config('app');
            $app_version    = config_item('app_version');

            $session_data = array('version' => $version,
                'appVersion' => $app_version);
            
            $device_id = $data['deviceId'];
            $rec = array(
                'player_data' => json_encode($player_data),
                'session_data' => json_encode($session_data));
            
            $this->sessions->add($id, $device_id, $rec);
            
            $ret = array('accountCreatedDate' => $player->created,
                'appVersion' => $app_version,
                'dob' => $player->dob,                                
                'gender' => $player->gender,
                'isSuspended' => $player->accountStatus == "Suspended"? 1: 0,
                'isDeleted' => $player->accountStatus == "Deleted"? 1: 0,                
                'newUserFlow' => $player->newUserFlow ? true : false,
                'now' => date("Y-m-d H:i:s"),
                'phoneCode' => $player->accountCode,
                'playerId' => $player->id,
                'playerRole' => $player->userType,
                'screenName' => $player->screenName,                
                'token' => $id,
                'version' => $version);                       
            
            if($player->accountStatus != "Active")
            {
                $this->sessions->destroy($id);
                $ret['error'] = "Account has been Suspended";
                $ret['code'] = 2;
            }                                         
            
            $ret['statusCode'] = 200;
            return $ret;
        }
    }
    
    public function base64Encode( $data ) 
    {
        if ( ! empty( $data) ) 
        {
            $dataString = implode( User::ENCRYPTION_KEY, $data );

            $result = base64_encode( $dataString );
            return $result;
        }

        return NULL;
    }
    
    public function loginGuest($data)
    {
        $this->load->config('rest');        
        $version    = config_item('rest_version');

        $this->load->config('app');
        $app_version    = config_item('app_version');

        $session_data = array('version' => $version,
            'appVersion' => $app_version);

        $device_id = rand(1,5000000);
        
        if(isset($data['playerId']) && is_numeric($data['playerId']) && $data['playerId'])
        {
            $player = $this->get_by(array('userType' => 'Guest', 'id' => $data['playerId']));
            if(!$player)
            {
                $player = $this->get_by(array('userType' => 'User', 'id' => $data['playerId']));
                if($player)
                    return array('code' => 1, 'error' => 'Account is already a User Account', 'statusCode' => 200);
                else
                    return array('code' => 2, 'error' => 'Guest Account not Found', 'statusCode' => 200);
            }
            else
            {
                $player_data = array('playerId' => $player->id,
                    'playerRole' => $player->userType,
                    'playerEmail' => $player->accountName,
                    'isSuspended' => $player->accountStatus == "Suspended" ? 1 : 0,
                    'isDeleted' => $player->accountStatus == "Deleted" ? 1 : 0,
                    'newUserFlow' => $player->newUserFlow ? true : false,
                    'screenName' => $player->screenName,
                    'emailCode' => $player->accountCode,
                    'phoneCode' => $player->accountCode
                );
                
                $id = $this->base64Encode( array( md5($player->accountCode), $player->passwordHash ) );
                
                $rec = array(
                    'player_data' => json_encode($player_data),
                    'session_data' => json_encode($session_data));

                $this->sessions->add($id, $device_id, $rec);

                $ret = array('accountCreatedDate' => $player->created,
                    'appVersion' => $app_version,
                    'dob' => $player->dob,
                    'fbId' => $player->fbId,
                    'gender' => $player->gender,
                    'deviceId' => $device_id,
                    'isSuspended' => 0,
                    'isDeleted' => 0,
                    'newUserFlow' => false,
                    'now' => date("Y-m-d H:i:s"),
                    'phoneCode' => $player->accountCode,
                    'playerId' => $player->id,
                    'playerRole' => $player->userType,
                    'screenName' => $player->screenName,                
                    'token' => $id,
                    'version' => $version,
                    'statusCode' => 200);
                
                return $ret;
            }
        }
        else //create account
        {
            $now = date("Y-m-d H:i:s");
            $rand = date("YmdHis") . rand(1, 1000000);
            $rec = array('firstName' => 'Guest', 
                'lastName' => 'User',
                'dob' => '1980-01-01',
                'userType' => 'Guest',
                'accountName' => $rand,
                'payPalEmail' => $rand,
                'accountCode' => $rand,
                'lastApprovedTOS' => $now,
                'lastApprovedPrivacyPolicy' => $now
                );
            $insertId = $this->insert($rec, TRUE);
            if(!$insertId)
                return array('code' => '3', 'error' => 'Error creating account', 'statusCode' => 200);

            $rec = array('accountName' => "guest_" . $insertId . "@kizzang.com",
                'payPalEmail' => "guest_" . $insertId . "@kizzang.com",
                'screenName' => "Guest " . $insertId);
            
            //print_r(compact('rec', 'insertId')); die();
            $this->db->where("id", $insertId);
            $this->db->update("Users",  $rec);

            $player = $this->get_by(array('id' => $insertId));
            
            $player_data = array('playerId' => $player->id,
                'playerRole' => $player->userType,
                'playerEmail' => $player->accountName,
                'isSuspended' => $player->accountStatus == "Suspended" ? 1 : 0,
                'isDeleted' => $player->accountStatus == "Deleted" ? 1 : 0,
                'newUserFlow' => $player->newUserFlow ? true : false,
                'screenName' => $player->screenName,
                'emailCode' => $player->accountCode,
                'phoneCode' => $player->accountCode
            );

            $id = $this->base64Encode( array( md5($player->accountCode), $player->passwordHash ) );

            $rec = array(
                'player_data' => json_encode($player_data),
                'session_data' => json_encode($session_data));

            $this->sessions->add($id, $device_id, $rec);

            $ret = array('accountCreatedDate' => $player->created,
                'appVersion' => $app_version,
                'dob' => $player->dob,                                
                'gender' => $player->gender,
                'deviceId' => $device_id,
                'isSuspended' => 0,
                'isDeleted' => 0,
                'newUserFlow' => false,
                'now' => date("Y-m-d H:i:s"),
                'phoneCode' => $player->accountCode,
                'playerId' => $player->id,
                'playerRole' => $player->userType,
                'screenName' => $player->screenName,                
                'token' => $id,
                'version' => $version,
                'statusCode' => 200);

            return $ret;
        }
    }
    
    public function linkFacebook($playerId, $data)
    {
        if($data['userType'] == "Guest")
        {
            $data['fbId'] = $data['FBID'];
            unset($data['FBID']);
            $data['dob'] = date("Y-m-d", strtotime($data['dob']));
            
            $validate['FBID'] = array('field' => 'fbId', 'rules' => 'required|is_numeric');
            $validate['firstName'] = array('field' => 'firstName', 'rules' => 'required|xss_clean');
            $validate['lastName'] = array('field' => 'lastName', 'rules' => 'required|xss_clean');
            $validate['email'] = array('field' => 'email', 'rules' => 'required|valid_email|max_length[120]|trim');
            $validate['dob'] =  array('field' => 'dob', 'rules' => 'required');

            $this->form_validation->reset_validation();
            $this->form_validation->set_params( $data );
            $this->form_validation->set_rules( $validate );

            if ( $this->form_validation->run() === FALSE )
                return array( 'code' => 1, 'message' => $this->form_validation->validation_errors(), 'statusCode' => 200 );

            $rs = $this->db->query("Select * from Users where fbId = ?", array($data['fbId']));
            if($rs->num_rows())
                return array( 'code' => 3, 'message' => 'This Facebook account is associated with another Kizzang account.', 'statusCode' => 200 );
            
            $this->db->where('id', $playerId);
            if(!$this->db->update('Users', array('firstName' => $data['firstName'], 'lastName' => $data['lastName'], 'fbId' => $data['fbId'], 'email' => $data['email'], 'dob' => $data['dob'])))
                return array('code' => 2, 'message' => 'Error Updating information', 'statusCode' => 200);
        }
        else
        {
            if(!isset($data['FBID']) || !is_numeric($data['FBID']))
                return array('code' => 4, 'message' => 'Invalid FB Id', 'statusCode' => 200);
            
            $rs = $this->db->query("Select * from Users where fbId = ?", array($data['FBID']));
            if($rs->num_rows())
                return array( 'code' => 3, 'message' => 'This Facebook account is associated with another Kizzang account.', 'statusCode' => 200 );
        
            $this->db->where('id', $playerId);
            if(!$this->db->update('Users', array('fbId' => $data['FBID'])))
                return array('code' => 2, 'message' => 'Error Updating information', 'statusCode' => 200);
            
        }
        return array('code' => 0, 'message' => 'Update Complete', 'statusCode' => 200);
    }
    
    public function loginAll($data)
    {
        $player = NULL;
        //print_r($data);
        $this->load->model("playerlogin");
        $types = array('Facebook','Normal','Guest');
        if(!isset($data['loginType']) || !in_array($data['loginType'], $types))
                return array('code' => 2, 'message' => 'Invalid Login Type', 'statusCode' => 200);
        
        $validate = array(        
            'mobileType' => array(
                'field' => 'mobileType',
                'rules' => 'xss_clean|max_length[10]|trim'
            ),
            'loginSource' => array(
                'field' => 'loginSource',
                'rules' => 'xss_clean|max_length[10]|trim'
            ),            
            'userAgent' => array(
                'field' => 'userAgent',
                'rules' => 'xss_clean|max_length[2000]|trim'
            ),            
            'appId' => array(
                'field' => 'appId',
                'rules' => 'xss_clean|max_length[10]|trim'
            )
        );
                
        switch($data['loginType'])
        {
            case 'Facebook': 
                $validate['FBID'] = array('field' => 'FBID', 'rules' => 'required|is_numeric');
                $validate['firstName'] = array('field' => 'firstName', 'rules' => 'required|xss_clean|trim');
                $validate['lastName'] = array('field' => 'lastName', 'rules' => 'required|xss_clean|trim');
                $validate['email'] = array('field' => 'email', 'rules' => 'required|valid_email|max_length[120]|trim');
                $validate['dob'] =  array('field' => 'dob', 'rules' => 'required');
                break;
            
            case 'Normal':
                $validate['accountName'] = array('field' => 'accountName', 'rules' => 'required|valid_email_phone');
                $validate['password'] = array('field' => 'password', 'rules' => 'required');
                break;
        }
        
        $this->form_validation->reset_validation();
        $this->form_validation->set_params( $data );
        $this->form_validation->set_rules( $validate );
        
        if ( $this->form_validation->run() === FALSE )
            return array( 'code' => 1, 'message' => $this->form_validation->validation_errors(), 'statusCode' => 200 );
        
        $player = NULL;
        if($data['loginType'] == "Facebook")
        {
            //Primary Search
            $player = $this->get_by(array('fbId' => $data['FBID']));
            if(!$player)
            {
                //Secondary Search
                $player = $this->get_by(array('email' => $data['email']));
                if($player) //Update the account
                {
                    $this->db->query("Update Users set fbId = ? where email = ? limit 1", array($data['FBID'], $data['email']));
                    $player = $this->get_by(array('fbId' => $data['FBID']));
                }
                else
                {
                    $rec = array('firstName' => $data['firstName'],
                        'lastName' => $data['lastName'],
                        'userType' => 'Guest',
                        'dob' => date("Y-m-d", strtotime($data['dob'])),
                        'fbId' => $data['FBID'],
                        'email' => $data['email'],
                        'screenName' => ucfirst($data['firstName']) . " " . substr(ucfirst($data['lastName']), 0, 1),
                        'accountName' => $data['email']);
                    $this->db->insert("Users", $rec);
                    $player = $this->get_by(array('fbId' => $data['FBID']));
                }
            }
        }
        elseif($data['loginType'] == "Normal")
        {
            $player = $this->get_by(array("accountName" => $data['accountName'], 'passwordHash' => md5($data['password'])));
            if(!$player)
            {
                if(is_numeric($data['accountName']))
                    $player = $this->get_by (array("phone" => $data['accountName'], 'passwordHash' => md5($data['password'])));
                else
                    $player = $this->get_by (array("email" => $data['accountName'], 'passwordHash' => md5($data['password'])));
            }
        }
        else 
        {                    
            $ret = $this->loginGuest($data);
            if(isset($ret['playerId']))
            {
                $player = $this->get_by(array('id' => $ret['playerId']));
            }
        }
        
        if(!$player)
            return array('code' => 2, 'message' => 'Login Failed', 'statusCode' => 200);
        
        $this->load->config('rest');        
        $version    = config_item('rest_version');

        $this->load->config('app');
        $app_version    = config_item('app_version');

        $session_data = array('version' => $version,
            'appVersion' => $app_version);

        $device_id = rand(1,5000000);
        
        $player_data = array('playerId' => $player->id,
            'playerRole' => $player->userType,
            'playerEmail' => $player->accountName,
            'isSuspended' => $player->accountStatus == "Suspended" ? 1 : 0,
            'isDeleted' => $player->accountStatus == "Deleted"? 1 : 0,
            'newUserFlow' => $player->newUserFlow ? true : false,
            'screenName' => $player->screenName,
            'emailCode' => $player->accountCode,
            'phoneCode' => $player->accountCode
        );

        $id = $this->base64Encode( array( md5($player->accountCode), $player->passwordHash ) );
        $this->sessions->destroy($id);
        
        $rec = array(
            'player_data' => json_encode($player_data),
            'session_data' => json_encode($session_data));

        $this->sessions->add($id, $device_id, $rec);
        
         $playerLoginData = array('playerId'         => $player->id, 
                'lastLogin'        => date( 'Y-m-d H:i:s' ), 
                'loginType'        => $data['loginType'] == 'Facebook' ? 'Facebook' : 'Normal', 
                'loginSource'      => isset( $data['loginSource'] ) ? $data['loginSource'] : 'Web', 
                'ipAddress'        => $this->getIpAddress(),
                'appId'            => isset( $data['appId'] ) ? $data['appId'] : 0,
                'isRegistration'   => isset( $data['isRegistration'] ) ? (int)$data['isRegistration'] : 0,
                'userAgent'        => $_SERVER['HTTP_USER_AGENT'],
                'mobileType'       => isset( $data['mobileType'] ) ? $data['mobileType'] : null
        );

        $this->playerlogin->insert($playerLoginData, true); 
        $this->load->model('configs');        

        $ret = array('accountCreatedDate' => $player->created,
            'appVersion' => $app_version,
            'code' => 0,
            'dob' => $player->dob,
            'fbId' => $player->fbId,
            'bgVersion' => $this->configs->getConfigElement('Config','Background Version')->info,
            'gender' => $player->gender,
            'deviceId' => $device_id,
            'isSuspended' => $player->accountStatus == "Suspended" || $player->accountStatus == "Email Suspended" ? 1 : 0,
            'isDeleted' => $player->accountStatus == "Deleted" ? 1 : 0,
            'isW2Blocked' => 0,
            'newUserFlow' => false,
            'now' => date("Y-m-d H:i:s"),
            'accountCode' => $player->accountCode,
            'playerId' => $player->id,
            'playerRole' => $player->userType,
            'screenName' => $player->screenName,                
            'token' => $id,
            'version' => $version,
            'statusCode' => 200);
        
        return $ret;
    }    

    public function loginFacebook( $data ) {

        $this->load->model( 'masteremail' );
        $this->load->model( 'masteraccount' );

        // defined rule validation
        $validate = array(
            'email' => array(
                'field' => 'email', 
                'label' => 'email',
                'rules' => 'required|valid_email|max_length[120]|trim'
            ),
            'FBID' => array(
                'field' => 'FBID',
                'label' => 'FBID',
                'rules' => 'required|xss_clean|greater_than[0]|max_length[30]|trim'
            ),
            'deviceId' => array(
                'field' => 'deviceId',
                'label' => 'Device Id',
                'rules' => 'required|trim|greater_than[0]'
            ),
            'gender' => array(
                'field' => 'gender',
                'label' => 'gender',
                'rules' => 'required|trim|greater_than[0]'
            ),
            'dob' => array(
                'field' => 'dob',
                'label' => 'Birth Date',
                'rules' => 'required|date_of_birth'
            ),
            'screenName' => array(
                'field' => 'screenName',
                'label' => 'Screen Name',
                'rules' => 'required'
            ),
            'firstName' => array(
                'field' => 'firstName',
                'label' => 'First Name',
                'rules' => 'required'
            ),
            'lastName' => array(
                'field' => 'lastName',
                'label' => 'Last Name',
                'rules' => 'required'
            ),
            'mobileType' => array(
                'field' => 'mobileType',
                'rules' => 'xss_clean|max_length[10]|trim'
            ),
            'loginSource' => array(
                'field' => 'loginSource',
                'rules' => 'xss_clean|max_length[10]|trim'
            ),
            'ipAddress' => array(
                'field' => 'ipAddress',
                'rules' => 'xss_clean|max_length[45]|trim'
            ),
            'userAgent' => array(
                'field' => 'userAgent',
                'rules' => 'xss_clean|max_length[2000]|trim'
            ),
            'isRegistration' => array(
                'field' => 'isRegistration',
                'rules' => 'is_numeric'
            ),
            'appId' => array(
                'field' => 'appId',
                'rules' => 'xss_clean|max_length[10]|trim'
            )
        );

        $data['dob'] = array(
                'bday' => isset( $data['birthDay'] ) ? $data['birthDay'] : 0,
                'bmonth' => isset( $data['birthMonth'] ) ? $data['birthMonth'] : 0,
                'byear' => isset( $data['birthYear'] ) ? $data['birthYear'] : 0
            );

        $this->form_validation->reset_validation();
        $this->form_validation->set_params( $data );
        $this->form_validation->set_rules( $validate );
        
       $maintenance = $this->maintenanceMode() ;

        if ( $maintenance ) {

            return array( 
                'code' => 325, 
                'message' => "Currently in maintenance mode ending $maintenance", 
                'statusCode' => 400  
                );
        }

        // if validation fail
        if ( $this->form_validation->run() === FALSE )
            return array( 'code' => 1, 'message' => $this->form_validation->validation_errors(), 'statusCode' => 200 );
        
        $deviceId = $data['deviceId'];        

        $ipAddress = $this->getIpAddress();

        // get player by facebook ID
        $fbIdPlayer = $this->get_by( 'fbId', $data['FBID'] );
        $emailPlayer = $this->get_by( 'accountName', $data['email'] );

        // Default to Web as login source unless sent by the app
        $loginSource = isset( $data['loginSource'] ) ? $data['loginSource'] : 'Web';

        // set fbLogin = TRUE
        $appId = isset( $data['appId'] ) ? $data['appId'] : 0;
        $isRegistration = isset( $data['isRegistration'] ) ? (int)$data['isRegistration'] : 0;
        $userAgent = isset( $data['userAgent'] ) ? $data['userAgent'] : null;
        $mobileType = isset( $data['mobileType'] ) ? $data['mobileType'] : null;
        
        $fbData = array(
                'email'         => $data['email'],
                'fbId'          => $data['FBID'],
                'dob'           => $data['dob'],
                'gender'        => $data['gender'],
                'screenName'    => $data['screenName'],
                'firstName'     => $data['firstName'],
                'lastName'      => $data['lastName']
        );
        
        $loginData = array(
                'email' => $data['email'],
                'deviceId' => $deviceId,              
                'fbId' => $data['FBID'],
                'appId' => $appId,
                'isRegistration' => $isRegistration,
                'userAgent' => $userAgent,
                'loginSource' => $loginSource,
                'ipAddress' => $ipAddress,
                'mobileType' => $mobileType
        );
        
        if(!$fbIdPlayer && !$emailPlayer)
        {
            $result = $this->add( $fbData, TRUE );
            if(is_array($result))
                return $result;
            
            return  $this->login( $loginData , TRUE );
        }
        elseif(!$fbIdPlayer && $emailPlayer)
        {
            //Update the current account
            $this->db->where("accountName", $data['email']);
            $this->db->update("Users", array("fbId" => $data['FBID']));
            
            return  $this->login( $loginData , TRUE );
        }
        else
        {
            return  $this->login( $loginData , TRUE );
        }
            
        return array('code' => 2, 'message' => 'Unknown Error', 'statusCode' => 200);
    }

    public function logout() {

        if ( $this->token ) 
        {
            $this->sessions->destroy($this->token);
            $result = array( 'code' => 0, 'message' => 'Logout successfully', 'statusCode' => 200 );            
        }
        else
        {
            $result = array( 'code' => 1, 'message' => 'Can\'t logout', 'statusCode' => 400 );
        }
        
        return $result;
    }

    public function checkActionOwner( $id ) {

        $this->load->model('sessions');
        $error = array( 'message' => 'Not authorized', 'statusCode' => 403 );

        // validation id
        if ( ! is_numeric($id) || $id <= 0 ) {

            $result = array( 'code' => 1, 'message' => 'Id must be a numeric and greater than zero', 'statusCode' => 400 );

            return $result;
        }

        $player = $this->sessions->getPlayerData($this->token);

        if ( ! empty( $player ) ) 
        {
            $playerId = $player['playerId'];
            $playerRole = $player['playerRole'];

            if ( $playerRole != 'Administrator' && $id != $playerId ) 
                return $error;            

            return TRUE;
        }
        else
        {
            $error = array( 'message' => 'Player key not found:' . $this->token . ",id: ". $id . ",token:". $this->token, 'statusCode' => 403 );
        }

        return $error;
    }
    
    public function resetPassword( $data ) {

        $validate = array( 'email' => array( 
            'field' => 'email', 
            'label' => 'Email or phone number',
            'rules' => 'required|valid_email_phone|max_length[120]|trim'
        ) );

        $this->form_validation->reset_validation();
        $this->form_validation->set_params( $data );
        $this->form_validation->set_rules( $validate );

        // if validation fail
        if ( $this->form_validation->run() === FALSE )
            return array( 'code' => 1, 'message' => $this->form_validation->validation_errors(), 'statusCode' => 200 );

        $player = NULL;
        $email = strtolower( $data['email'] );
        if(is_numeric($email))
        {
            $rs = $this->db->query("Select * from Users where accountName = ? or phone = ?", array($email, $email));
            if($rs->num_rows())
                $player = $rs->row();
        }
        else
        {
            $rs = $this->db->query("Select * from Users where accountName = ? or email = ?", array($email, $email));
            if($rs->num_rows())
                $player = $rs->row();
        }        

        // if exists email
        if ($player) 
        {    
            $isEmail = FALSE;

            if ( $this->form_validation->valid_email( $email ) ) 
            {
                $isEmail = TRUE;

                // generate password template
                $resetToken = md5( uniqid(rand(), true ) );
                $emailCode  = $player->emailCode;

                $resetData = array( 'email' => $email, 'emailCode' => $emailCode );
            }
            else 
            {
                $resetToken = substr( base_convert( uniqid(rand(), true ), 16, 10 ), 0, 6 );
                $resetData = array( 'email' => $email );
            }

            $key = 'KEY-resetToken' . md5( $resetToken );
            $this->updateMemcache( $key, $resetData, 60*60*24 );
            
            if ( $isEmail ) 
            {                                                        
                $body = $this->load->view("emails/resetpw", array('url' => $this->getSiteUrl(), 'resetToken' => $resetToken), true);
                $isSent = $this->sendGenericEmail($email, "Password Reset - Kizzang", $body);

                if ( $isSent === TRUE )                 
                    $result = array( 'code' => 0, 'message' => 'An email has been sent to ' . $email, 'resetToken' => $resetToken, 'statusCode' => 200 );                
                else
                    $result = array( 'code' => 2, 'message' => $isSent['message'], 'statusCode' => 200 );
            }            
            else 
            {
                $content = "Reset your Kizzang password using this link: https://" . getenv("SWF_SERVER_NAME") . "/accounts/resetPassword/".$resetToken;
                $result = $this->sendSMS( $email, $content, $resetToken );
            }
        }
        else {

            $result = array( 'code' => 3, 'message' => 'Email or phone number does not exist', 'statusCode' => 200 );
        }

        return $result;

    }

    protected function sendSMS( $to, $content, $phoneCode ) 
    {
        $result = TRUE;

        // send a message to phone
        $this->load->library('twilio');

        $to = preg_replace( "/[^0-9]/", '', $to );
        $to = '+' . $to;
     
        $this->load->config('twilio');

        try
        {
            $response = $this->twilio->sms( $to, $content );

            $result = array('code' => 0, 
                            'phoneCode' => $phoneCode,
                            'smsid' => $response->sid,
                            'statusCode' => 200 );
        }
        catch (Services_Twilio_RestException $e)
        {
            $result = array( 'code' => 1, 'message' => $e->getMessage(), 'statusCode' => 200 );
        }
            
        return $result;
    }

    /**
     * change password when player has been logged in
     * @param  string $emailCodeOrPhoneCode
     * @param  array $data 
     * @return array
     */
    public function changePassword( $emailCodeOrPhoneCode, $data ) {

        $validate = array(
            'password' => array(
                'field' => 'password',
                'label' => 'Password',
                'rules' => 'required|xss_clean|max_length[20]|trim'
            ),
            'passwordConfirm' => array(
                'field' => 'passwordConfirm',
                'label' => 'Password Confirm',
                'rules' => 'required|trim|matches[password]'
            ),
        );

         // validate data insert 
        if ( empty( $data ) )
            return array( 'code' => 1, 'message' => 'Please the required enter data', 'statusCode' => 400 );

        $this->form_validation->reset_validation();
        $this->form_validation->set_params( $data );
        $this->form_validation->set_rules( $validate );

        // if validation fail
        if ( $this->form_validation->run() === FALSE ) 
        {
            $errors = $this->form_validation->validation_errors();

            if ( isset( $errors[0] ) && ! $errors[0] )
                $result = array( 'code' => 2, 'message' => 'Please enter the required data', 'statusCode' => 400 );            
            else
                $result = array( 'code' => 3, 'message' => $errors, 'statusCode' => 400 );            

            return $result;
        }
        else 
        {
            $player = $this->sessions->getPlayerData($this->token);

            // if username is phone number
            if ( is_numeric( $emailCodeOrPhoneCode ) && strlen( $emailCodeOrPhoneCode ) === 6 ) 
            {
                $where = array( 'accountName' => $emailCodeOrPhoneCode );
                $emailCodeOrPhoneCodeCache = $player['phoneCode'];
            }
            else {

                $where = array( 'accountName' => $emailCodeOrPhoneCode );
                $emailCodeOrPhoneCodeCache = $player['emailCode'];
            }

            // check email code param
            if ( $emailCodeOrPhoneCodeCache !== $emailCodeOrPhoneCode )             
                return array( 'code' => 4, 'message' => 'Invalid Email Or Phone Code', 'statusCode' => 400 );                       

            // update password by email code
            $isUpdated = $this->db->set( 'passwordHash', md5( $data['password'] ) )
                        ->where( $where )
                        ->update( $this->_table );

            if ( $isUpdated )
                return array( 'code' => 0, 'message' => 'Updated the password successfully', 'statusCode' => 200 );            

            $message = $this->db->_error_message();
            log_message( 'error', 'Change password: ' . $message );

            $result = array( 'code' => 5, 'message' => $message, 'statusCode' => 400 );            

            return $result;
        }
    }

    /**
     * update password when player forget
     * @param  array $data
     * @return array
     */
    public function updatePassword( $data ) {

        $validate = array( 
            'password' => array(
                'field' => 'password',
                'label' => 'Password',
                'rules' => 'required|xss_clean|max_length[20]|trim'
            ),
            'resetToken' => array(
                'field' => 'resetToken',
                'label' => 'resetToken',
                'rules' => 'required|trim'
            ),
        );

        $this->form_validation->reset_validation();
        $this->form_validation->set_params( $data );
        $this->form_validation->set_rules( $validate );

        // if validation fail
        if ( $this->form_validation->run() === FALSE )
            return array( 'code' => 1, 'messages' => $this->form_validation->validation_errors(), 'statusCode' => 400 );

        $resetToken     = $data['resetToken'];
        $key            = 'KEY-resetToken' . md5( $resetToken );
        $resetMemcache  = $this->memcacheInstance->get( $key );
        $email          = $resetMemcache['email'];

        if ( $email ) 
        {
            if ( $this->form_validation->valid_email( $email ) ) 
            {
                $isUpdated = $this->db->set( 'passwordHash', md5( $data['password'] ) )
                    ->where( 'accountName', $email )
                    ->update( $this->_table );
            }
            else 
            {
                $phoneNumber = preg_replace('/[^0-9]/', '', $email );

                $isUpdated = $this->db->set( 'passwordHash', md5( $data['password'] ) )
                    ->where( 'accountName', $phoneNumber )
                    ->update( $this->_table );
            }

            if ( $isUpdated ) 
            {
                $result = array( 'code' => 0, 'message' => 'Updated the password successfully', 'email' => $email, 'statusCode' => 200 );
                $this->updateMemcache( $key, null );
            }
            else 
            {
                // get and log error message
                $errorMessage = $this->db->_error_message();
                log_message( 'error', 'Update Password: ' . $errorMessage );

                $result = array( 'code' => 2, 'message' => $errorMessage, 'statusCode' => 400 );
            }
        }
        else 
        {
            $result = array( 'code' => 3, 'message' => 'The reset token is incorrect', 'statusCode' => 400 );
        }
            
        return $result;
    }

    public function updateEmail( $data ) 
    {
        $email = trim($data['email']);
        //Check for valid email address
        if(!preg_match("/^(|(([A-Za-z0-9]+_+)|([A-Za-z0-9]+\-+)|([A-Za-z0-9]+\.+)|([A-Za-z0-9]+\++))*[A-Za-z0-9]+@((\w+\-+)|(\w+\.))*\w{1,63}\.[a-zA-Z]{2,6})$/ix", $email))
            return array('code' => 1, 'message' => 'Invalid Email Addresss', 'statusCode' => 200);
        
        $email_hash = md5($email);
        $rs = $this->db->query("Select id from Users where accountName = ?", array($email));
        if($rs->num_rows())
            return array('code' => 2, 'message' => 'Duplicate email address in the system', 'statusCode' => 200);
        
        $rs = $this->db->query("Select * from Users where id = ?", array($data['playerId']));
        if(!$rs->num_rows())
            return array('code' => 3, 'message' => 'Player does not exist', 'statusCode' => 200);
        
        $rec = $rs->row();
        $accountData = $this->createDecryptedArray($rec->accountData);
        if(!is_array($accountData))
            return array('code' => 4, 'message' => 'Corrupt Data Account');                
        
        //$this->db->where("id", $data['playerId']);
        //$this->db->update("Players", $rec);
        
        return array('code' => 0, 'message' => 'Email Updated', 'statusCode' => 200);
    }

    public function resendEmail( $data ) 
    {
        $validate = array(
            'email' => array( 
                'field' => 'email', 
                'label' => 'email',
                'rules' => 'required|valid_email|max_length[120]|trim'
            ),
        );

        if ( empty( $data ) )
            return array( 'code' => 1, 'message' => 'Please enter an email', 'statusCode' => 400 );

        $this->form_validation->reset_validation();
        $this->form_validation->set_params( $data );
        $this->form_validation->set_rules( $validate );

        // if validation fail
        if ( $this->form_validation->run() === FALSE )
            return array( 'code' => 1, 'messages' => $this->form_validation->validation_errors(), 'statusCode' => 400 );

        $email  = strtolower( $data['email'] );
        $player = $this->getByEmailPassword( $email );

        // if not execute from unit testing and found player
        if (is_object( $player ) && $player ) 
        {
            $body = $this->load->view("emails/wrapper", array('content' => $this->load->view('emails/verificationCode', array('code' => $player->accountCode), true), 'emailCode' => md5($player->accountName)), true);
            $isSent = $this->sendGenericEmail($email, "Email Confirmation - Kizzang", $body);

            if ( $isSent === TRUE )
                $result = array( 'code' => 0, 'message' => 'A confirmation email was sent to ' . $email, 'statusCode' => 200 );                
            else 
                $result = array( 'code' => 1, 'message' => $isSent['message'], 'statusCode' => 400 );
        }
        else 
        {
            $result = array_merge( array( 'code' => 2 ), $player );
        }

        return $result;

    }

    public function profile( $id, $limit, $offset ) {

        $this->load->model( 'facebookinvite' );
        $this->load->model( 'position' );
        $this->load->model( 'gamecount' );

        $player = $this->getById( $id );
        $rs = $this->db->query("Select sum(amount) as aggWin, max(amount) as maxWin from Winners where player_id = ? and processed = 1 group by player_id", array($id));
        $wins = $rs->row();

        // in the case error then return
        if ( is_array( $player ) ) {

            return $player;
        }

        // get highest and total aggregate win        
        $friendList         = $this->facebookinvite->getFriendList( $id, $limit, $offset );        
        $favoriteGame       = $this->gamecount->getFavoriteGame( $id );

        $profile = array(
            'highestWin'        => $wins->maxWin ? $wins->maxWin : 1,
            'totalAggregateWin' => $wins->aggWin ? $wins->aggWin : 1,
            'currentPosition'   => 0,
            'highestPosition'   => 0,
            'favoriteGame'      => $favoriteGame,
            'playerName'        => $player->accountData['firstName'] . ' ' . $player->accountData['lastName'],
            'facebookId'        => $player->fbid,
            'friendList'        => $friendList,
        );

        $result = array( 'profile' => $profile, 'statusCode' => 200 );
        
        return $result;
    }   

    public function setNewUserFlow( $data ) {

        $validate = array( 
            'playerId' => array(
                'field' => 'playerId',
                'label' => 'Player Id',
                'rules' => 'required|greater_than[0]'
            ),
            'newUserFlow' => array(
                'field' => 'newUserFlow',
                'label' => 'New User Flow',
                'rules' => 'required|trim'
            ),
        );
        
        $this->form_validation->reset_validation();
        $this->form_validation->set_params( $data );
        $this->form_validation->set_rules( $validate );

        // if validation fail
        if ( $this->form_validation->run() === FALSE )
            return array( 'code' => 1, 'messages' => $this->form_validation->validation_errors(), 'statusCode' => 200 );

        if ( ! in_array( $data['newUserFlow'], array( 'true', 'false' ) ) ) 
            return array( 'code' => 2, 'message' => "The New User Flow Only Allows values 'true' or 'false'", 'statusCode' => 200 );


        $isUpdated = $this->db->set( 'newUserFlow', filter_var( $data['newUserFlow'], FILTER_VALIDATE_BOOLEAN) )
                    ->where( 'id', $data['playerId'] )
                    ->update( $this->_table );

        if ( $isUpdated ) {

            $result = array( 'code' => 0, 'message' => 'Updated successfully', 'statusCode' => 200 );
        }
        else {

            $msg = $this->db->_error_message();
            $result = array( 'code' => 3, 'message' => $msg, 'statusCode' => 200 );
        }

        return $result;
    }
}
