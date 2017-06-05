<?php

class Players extends MY_Controller {
	
    public function __construct() 
    {
            parent::__construct(
                TRUE, // Controller secured
                array(
                   'getOne'         => array( 'Administrator', 'User', 'Guest' ),
                   'destroy'        => array( 'Administrator', 'User', 'Guest' ),
                   'link'        => array( 'Administrator', 'User', 'Guest' ),
                   'update'         => array( 'Administrator', 'User', 'Guest' ),
                   'logout'         => array( 'Administrator', 'User', 'Guest' ),
                   'getProfile'     => array( 'Administrator', 'User', 'Guest' ),                   
                   'getCurrentData' => array( 'Administrator', 'User', 'Guest' ),
                   'changePassword' => array( 'Administrator', 'User', 'Guest' ),
                   'updateEmail' => array( 'Administrator', 'User', 'Guest' ),
                   'setNewUserFlow' => array( 'Administrator', 'User', 'Guest' ),
                   'transactions' => array( 'Administrator', 'User', 'Guest' )
            )//secured action
        );

        // load email library
        $this->load->library('email');
        $this->load->model('user');

        // set token to player model use this variable
        if ( $this->token )
            $this->user->setToken( $this->token );

    }

    public function getCurrentGame_post($theme)
    {
        $result = $this->user->getCurrentGame($theme);
        $this->formatResponsePlayer( $result );
    }

    public function link_post()
    {
        $playerId = $this->_get_player_memcache("playerId");
        $result = $this->user->linkFacebook( $playerId, $_POST );
        $this->formatResponsePlayer( $result );
    }

    public function transactions_post()
    {
        $playerId = $this->_get_player_memcache("playerId");
        $result = $this->user->getTransactions( $playerId );
        $this->formatResponsePlayer( $result );
    }
        
	public function getProfile_get( $id, $limit, $offset ) {

		// call profile function from player model
		$result = $this->user->profile( $id, $limit, $offset );
	
		// format result before return
		$this->formatResponsePlayer( $result );
	}

	public function getProfile_post( $id, $limit, $offset ) {

		$this->getProfile_get( $id, $limit, $offset );
	}
        
    public function emailCreate_post() 
    {
        $result = $this->user->emailCreate($_POST);
        $this->formatResponsePlayer( $result );
    }

    public function phoneCreate_post() 
    {
        $result = $this->user->phoneCreate($_POST);
        $this->formatResponsePlayer( $result );
    }

    public function accountVerify_post() 
    {
        $result = $this->user->accountVerify($_POST);
        $this->formatResponsePlayer( $result );
    }

    public function verifyResend_post()
    {
        $result = $this->user->verifyResend($_POST);
        $this->formatResponsePlayer( $result );
    }
	
	/**
	 * get all player
	 * GET /api/players
	 * 	or /api/players/5/10
	 */
	public function getAll_get( $limit = 10, $offset = 0 ) {

		// call getAll function from player model
		$result = $this->user->getAll( $offset, $limit );

		// format result before return
		$this->formatResponsePlayer( $result );
		
	}
	public function getAll_post( $limit = 10, $offset = 0 ) {

		$this->getAll_get($limit, $offset);
	}

	/**
	 * get One player by id
	 * GET /api/players/1
	 */
    public function getOne_post( $id ) 
    {
        // call getById function from player model
        $playerId = $this->_get_player_memcache("playerId");
        if($playerId != $id)
            $result = array('code' => 1, 'message' => 'Access Violation', 'statusCode' => 200);
        else
            $result = $this->user->getById( $id );

        // format result before return
        $this->formatResponsePlayer( $result );
    }
	
	/**
	 * Insert a players
	 * POST /API/players
	 */
	public function add_post() {

		// call add function from player model
		$result = $this->user->add( $this->post() );

		// format result before return
		$this->formatResponsePlayer( $result );
	}

	/**
	 * Update a player
	 * PUT /API/players/1
	 */
	public function update_put( $id ) {

		if ( $_SERVER['REQUEST_METHOD'] === 'PUT' ) {

			$data = $this->put();
		}
		else {

			$data = $this->post();
		}

		// call edit function from player model
		$result = $this->user->edit( $id, $data );

		// format result before return
		$this->formatResponsePlayer( $result );
	}

	public function update_post( $id ) {

		$this->update_put($id);
	}

	/**
	 * delete a player by id
	 * @param  int $id
	 */
	public function destroy_delete( $id ) {

		// call destroy funciton from player model
		$result = $this->user->destroy( $id );

		// format result before return
		$this->formatResponsePlayer( $result );
	}
	public function destroy_post( $id ) {
		$this->destroy_delete($id);
	}

	/**
	 * format result before return
	 * @param  array or object $result
	 * @return json
	 */
	protected function formatResponsePlayer( $result) {
            // Default to success
            $code = 0;

            // in the case error or getAll
            if ( is_array( $result ) ) {

                // get element last is statusCode and remove it from result
                $status = array_pop( $result );
                
                if (isset($result["code"]))
                    $code = $result["code"];
                else
                    $code = 100;
            }
            else {

                // get statusCode
                $status = $result->statusCode;

                // remove statusCode element
                unset($result->statusCode);
            }

            // if request success or player was created
            if ( $code == 0 || $status === 201 ) {

                // remove '', null and false in the case a player object
                if ( is_object( $result ) ) {
                    $result->accountData = array_filter( $result->accountData );
                }
                // in the case list players
                elseif ( isset( $result['players'] ) ) {

                    $this->filterAccountData( $result );
                }
            }
            
        $this->response( $result, $status );
	}

	/**
	 * remove '', null and false in account data
	 * @param  object  $result [description]
	 * @return void
	 */
	public function filterAccountData( & $result ) {

		// remove each element accountDataTmp from result
		foreach ($result['players'] as $key => $value) {
			$value->accountData = array_filter( $value->accountData );
		}
	}

    /**
     * resend confirmation email
     * POST api/players/resendEmail
     */
    public function resendConfirmationEmail_post() 
    {
        $player = $this->user->resendEmail( $this->post() );
        $this->formatResponse( $player );
    }

    /**
     * Verify email
     * @param  string $emailCode
     * @return json
     */
    public function emailVerified_get( $emailCode ) {

    	// call emailVerified function from player model
    	$result = $this->user->emailVerified( $emailCode );

    	// format result before return
    	$this->formatResponsePlayer( $result );
    }
    public function emailVerified_post( $emailCode ) {
        $this->emailVerified_get($emailCode);
    }

    /**
     * Verify phone
     * @return json
     */
    public function phoneVerified_post() {

        // call phoneVerified function from player model
        $result = $this->user->phoneVerified( $this->post() );

        // format result before return
        $this->formatResponsePlayer( $result );
    }

    /**
     * Verify phone
     * @return json
     */
    public function phoneResendCode_post() {

        // call phoneVerified function from player model
        $result = $this->user->phoneResendCode( $this->post() );

        // format result before return
        $this->formatResponsePlayer( $result );
    }
    
    /**
     * ignore email if to be a mistake
     * @param  string $emailCode
     * @return json
     */
    public function ignoreEmail_get( $emailCode ) {

        $data = $this->input->post();
    	// call ignoreVerified function from player model
    	$result = $this->user->ignoreVerified($emailCode, $data);

    	// format result before return
    	$this->formatResponsePlayer( $result );
    }
    public function ignoreEmail_post( $emailCode ) {
		$this->ignoreEmail_get($emailCode);
    }

    /**
     * login user
     * @return json
     */
    public function login_post() {
    	// call login function from player model
    	$result = $this->user->login( $this->post() );
    	// format result before return
    	$this->formatResponsePlayer( $result );
    }

    /**
     * login with facebook account
     * @return json
     */
    public function loginFacebook_post() {

    	// call loginFacebook function from player model
    	$result = $this->user->loginFacebook( $this->post() );

    	// format result before return
    	$this->formatResponsePlayer( $result );
    }
    
    public function loginGuest_post()
    {
        $result = $this->user->loginGuest( $this->post() );
        $this->formatResponsePlayer( $result );
    }
    
    public function loginAll_post()
    {
        $result = $this->user->loginAll( $this->post() );
        $this->formatResponsePlayer( $result );
    }

    /**
     * logout user
     * @return json
     */
    public function logout_get() {

    	// call logout funciton from player model
    	$result = $this->user->logout();

    	// format result before return
    	$this->formatResponsePlayer( $result );
    }
    public function logout_post() {

		$this->logout_get();
    }

    /**
     * get current play period, game counts and position
     * @param  int $playerId
     * @return json
     */
    public function getCurrentData_get( $playerId ) {

    	$result = $this->user->getCurrentData( $playerId );

    	// format result before return
    	$this->formatResponsePlayer( $result );
    }

    public function getCurrentData_post( $playerId ) {
		
		$this->getCurrentData_get($playerId);
    }

    /**
     * reset password
     * @return json
     */
    public function resetPassword_post() {

    	$result = $this->user->resetPassword( $this->post() );

    	$this->formatResponse( $result );
    }
    
    public function updateEmail_post()
    {
        $data = $this->post();
        $data['playerId'] = $this->_get_player_memcache("playerId");
        $result = $this->user->updateEmail($data);
        $this->formatResponse( $result );
    }

    /**
     * update password
     * PUT /api/players/updatepassword
     * @return json
     */
    public function updatePassword_put() {
		
		$data = ( $_SERVER['REQUEST_METHOD'] === 'PUT' ) ? $this->put() : $this->post();

    	$result = $this->user->updatePassword( $data );

    	$this->formatResponse( $result );
    }
   
    /**
     * change password with post method
     * POST /api/players/resetPassword/<$emailCodeOrPhoneCode>
     * @return json
     */
    public function changePassword_post( $emailCodeOrPhoneCode ) {

    	$result = $this->user->changePassword( $emailCodeOrPhoneCode, $this->post() );

    	$this->formatResponse( $result );
    }

    public function setNewUserFlow_put() {

    	$data = ( $_SERVER['REQUEST_METHOD'] === 'PUT' ) ? $this->put() : $this->post();

    	$result = $this->user->setNewUserFlow( $data );

    	$this->formatResponse( $result );
    }

    public function setNewUserFlow_post() {

    	$this->setNewUserFlow_put();
    }
}
