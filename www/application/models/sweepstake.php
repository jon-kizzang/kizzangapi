<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class sweepstake extends MY_Model 
{

    // set table is Sweepstakes
    protected $_table = 'Sweepstakes';

    // set TRUE will not delete record, only set $soft_delete_key field to 1
    protected $soft_delete = TRUE;
    protected $soft_delete_key = 'isDeleted';

    // will call convertDateToMysql function before insert into db
    protected $before_create = array('convertDateToMysql');

    // will call convertDateToMysql function before update into db
    protected $before_update = array('convertDateToMysql');
    protected $after_get = array('relate', 'convertDateToPHP');

    // set validations rules
    protected $validate = array(

        // verify name must be is required
        'name' => array( 
            'field' => 'name', 
            'label' => 'name',
            'rules' => 'required'
        ),

        // verify description must be is required
        'description' => array(
            'field' => 'description',
            'label' => 'description',
            'rules' => 'required'
        ),

        // verify startDate must be is required and validate date function
        'startDate' => array(
            'field' => 'startDate',
            'label' => 'startDate',
            'rules' => 'required|valid_datetime'
        ),

        // verify end date must be is required and validate date function
        'endDate' => array(
            'field' => 'endDate',
            'label' => 'endDate',
            'rules' => 'required|valid_datetime'
        ),

        // verify image URL must be is required 
        'imageURL' => array(
            'field' => 'imageURL',
            'label' => 'imageURL',
            'rules' => 'required'
        ),

        // verify titleImageURL must be is required 
        'titleImageURL' => array(
            'field' => 'titleImageURL',
            'label' => 'titleImageURL',
            'rules' => 'required'
        ),

        // verify type must be is required 
        'sweepstakeType' => array(
            'field' => 'sweepstakeType',
            'label' => 'sweepstakeType',
            'rules' => 'required|valid_type_sweepstake'
        ),

        // verify maxWinners must be is required and greater than one
        'maxWinners' => array(
            'field' => 'maxWinners',
            'label' => 'maxWinners',
            'rules' => 'required|greater_than[0]'
        ),

        // verify displayValue must be is required 
        'displayValue' => array(
            'field' => 'displayValue',
            'label' => 'displayValue',
            'rules' => 'required'
        ),

        // verify taxValue must be is required 
        'taxValue' => array(
            'field' => 'taxValue',
            'label' => 'taxValue',
            'rules' => 'required'
        ),

        // verify taxValue must be is required 
        'ratioTicket' => array(
            'field' => 'ratioTicket',
            'label' => 'ratioTicket',
            'rules' => 'required|greater_than[0]'
        ),

        // verify color must be is required
        'color' => array(
            'field' => 'color',
            'label' => 'color',
            'rules' => 'required'
        ),
    );

    protected $public_attributes = array(
                    'id',
                    'name',
                    'description',
                    'startDate',
                    'endDate',
                    'createdDate',
                    'imageURL',
                    'titleImageURL',
                    'sweepstakeType',
                    'maxEntrants',
                    'maxWinners',
                    'displayValue',
                    'taxValue',
                    'isDeleted',
                    'ratioTicket',
                    'entryCount',
                    'color'
                );	
	
    public function convertDateToMysql( $sweepstake ) 
    {
        // convert time startdate to 'Y-m-d H:i:s'
        if ( isset( $sweepstake['startdate'] ) )
            $sweepstake['startdate'] = date('Y-m-d H:i:s', strtotime( str_replace('-', '/', $sweepstake['startdate'] ) ) );

        // convert time enddate to 'Y-m-d H:i:s'
        if ( isset( $sweepstake['enddate'] ) )
            $sweepstake['enddate'] = date('Y-m-d H:i:s', strtotime( str_replace('-', '/', $sweepstake['enddate'] ) ) );

        // return time convert of startTime and endTime
        return $sweepstake;
    }

    /**
     * convert format date mysql to m-d-Y H:i:s
     * @return object
     */
    protected function convertDateToPHP( $sweepstake ) 
    {
        // convert time startdate to 'm-d-Y H:i:s'
        if ( isset( $sweepstake->startDate ) && $sweepstake->startDate )
            $sweepstake->startDate = date( 'm-d-Y H:i:s', strtotime( $sweepstake->startDate ) );        

        // convert time enddate to 'm-d-Y H:i:s'
        if ( isset( $sweepstake->endDate ) && $sweepstake->endDate ) 
            $sweepstake->endDate = date( 'm-d-Y H:i:s', strtotime( $sweepstake->endDate ) );        

        // return time convert of startTime and endTime
        return $sweepstake;
    }
    
    /**
    * get all sweepstakes from database
    * @param  int $limit
    * @param  int $offset
    * @return array
    */
    protected function getAllActiveFromDatabase() {

        $this->load->model( 'gamerule' );
        $where = array(
            'endDate >=' => date( 'Y-m-d H:i:s' ),
            'startDate <=' => date('Y-m-d H:i:s'),
            'sweepstakeType' => 'open'
        );

        // get all sweepstake is not deleted from database by offset and limit
        $sweepstakes = $this->order_by("isImportant",  "DESC")->order_by("endDate","ASC")->get_many_by( $where );

        if ( empty( $sweepstakes ) ) 
        {
            $result = array( 'code' => 1, 'message' => 'Sweepstakes Not Found', 'statusCode' => 404 );
        }
        else 
        {
            //Manually attach the rules since
            foreach($sweepstakes as &$row)            
            {
                $rule = $this->gamerule->getGameRules(sprintf("KW%05d", $row->id));
                if(isset($rule->ruleURL))
                    $row->rulesUrl = $rule->ruleURL;
                
                if(preg_match("/^([0-9]{2})-([0-9]{2})-([0-9]{4})/", $row->endDate, $matches))
                    $row->daysLeft = floor((strtotime($matches[3] . "-" . $matches[1] . "-" . $matches[2] . " 23:59:59") - strtotime("now")) / 86400);
            }

            // return all list of sweepstakes
            $result = array( 'code' => 0, 'sweepstakes' => $sweepstakes, 'count' => count( $sweepstakes ), 'statusCode' => 200 );
        }

        return $result;
    }
        
    /**
    * get all sweepstakes from database
    * @param  int $limit
    * @param  int $offset
    * @return array
    */
    protected function getAllFromDatabase( $limit, $offset ) 
    {
        // get all sweepstake is not deleted from database by offset and limit
        $sweepstakes = $this->limit( $limit, $offset )->get_all('isDeleted', 0);

        // get count sweepstake is'nt deleted from database 
        $count = $this->count_by( 'isDeleted', 0 );

        if ( empty($sweepstakes) ) 
        {
            // return log errors when sweepstake return null
            $errors = array( 'code' => 1, 'message' => 'Sweepstakes Not Found', 'statusCode' => 404 );
            return $errors; 
        }
        else 
        {
            // return all list of sweepstakes            
            $result = array( 'code' => 0, 'sweepstakes' => $sweepstakes, 'offset' => (int)$offset, 'limit' => (int)$limit, 'count' => $count, 'statusCode' => 200 );
            return $result;
        }
    }

    /**
    * get all sweepstakes
    * @param  int $offset
    * @param  int $limit
    * @return array
    */
    public function getAll( $limit, $offset ) 
    {
        if ( $this->memcacheEnable ) 
        {
            $key = "KEY-Sweepstakes-$offset-$limit";
            // the first at all, get the result from memcache
            $result = $this->memcacheInstance->get( $key );
            if ( $result ) return $result;
        }

        // return an array
        $result = $this->getAllFromDatabase( $limit, $offset );

        if ( $this->memcacheEnable && (int)$result['code'] === 0 ) 
        {
            // set the result to memcache
            $this->user->updateMemcache( $key, $result, 1800); 
        }

        return $result;
    }

	/**
	* get all active sweepstakes
	* @param  int $offset
	* @param  int $limit
	* @return array
	*/
	public function getAllActive($playerId) 
            {		
                $this->load->model( 'gamerule' );
                $date = date("Y-m-d");
                
                $rs = $this->db->query("Select s.imageURL, endDate, s.id, rulesUrl, description, displayName, if(m.ticketDate is NULL, 1, 0) as isActive, sum(if(t.playerId = ?, 1, 0)) as playerEntries, 
                    count(t.ticketDate) as totalEntries 
                    From Sweepstakes s 
                    Left Join Tickets t on t.sweepstakesId = s.id
                    Left Join Tickets m on m.sweepstakesId = s.id and m.playerId = ? and m.ticketDate = ?
                    Where sweepstakeType = 'open' and convert_tz(now(), 'GMT', 'US/Pacific') between startDate and endDate Group by s.id Order by isImportant DESC, endDate ASC", array($playerId, $playerId, $date));
                
                if ( !$rs->num_rows )                 
                    return array( 'code' => 1, 'message' => 'Sweepstakes Not Found', 'statusCode' => 404 );
                
                $sweepstakes = $rs->result();                
                foreach($sweepstakes as &$row)            
                {
                    $rule = $this->gamerule->getGameRules(sprintf("KW%05d", $row->id));
                    if(isset($rule->ruleURL))
                        $row->rulesUrl = $rule->ruleURL;

                    if(preg_match("/^([0-9]{2})-([0-9]{2})-([0-9]{4})/", $row->endDate, $matches))
                        $row->daysLeft = floor((strtotime($matches[3] . "-" . $matches[1] . "-" . $matches[2] . " 23:59:59") - strtotime("now")) / 86400);
                }
                
                return array( 'code' => 0, 'sweepstakes' => $sweepstakes, 'count' => count( $sweepstakes ), 'statusCode' => 200 );                
	}
        
	/**
	 * get sweepstask from database
	 * @param  int $id
	 * @return array or object
	 */
	protected function getByIdFromDb( $id ) {

            // get object sweepstake by if from database
            $result = $this->get( $id );

            if ( empty($result) ) {

                // return log errors when return empty result
                $errors = array( 'code' => 1, 'message' => 'Sweepstake Not Found', 'statusCode' => 404 ); 
                
                return $errors; 
            } 
            else {

                $result->statusCode = 200;

                // return object of sweepstake
                return $result;
            }
	}

    /**
    * get sweepstake by id
    * @param  int $id sweepstake id
    * @return array
    */
    public function getById( $id ) {
        
            // validate the id.
            if ( ! is_numeric($id) || $id <= 0 ) {
            
                // return log errors when id input is invalid
                $errors = array( 'code' => 1, 'message' => 'Id must is a numeric and greater than zero', 'statusCode' => 400 );
			
                // array errors
                return $errors; 
            }

            if ( $this->memcacheEnable ) {
			
                $key = "KEY-Id-Sweepstake" . md5( "getOne_get-sweepstake_id:$id" );

                // the first at all, get the result from memcache
                $result = $this->memcacheInstance->get( $key );

                if ( $result !== FALSE ) { return $result; }
                
            }
  
            // if not enabled caching, just return the data form database.
            $result = $this->getByIdFromDb( $id );

            if ( $this->memcacheEnable && is_object( $result ) ) {

                // set the result to memcache for use later
                $this->user->updateMemcache( $key, $result );
            }

            return $result;
    
    }

    /**
     * insertSweepstakeDB data into Database
     * @param  obj $data 
     * @return array
     */
    protected function insertSweepstakeDB( $data ) {

    	// set skip_validation = TRUE in 2nd parameter
        $insertId = $this->insert( $data, TRUE );

        if ( $insertId ) {
            
            // get object sweepstake by id 
            $result = $this->getById( $insertId );
            $result->statusCode = 201;

            if ( $this->memcacheEnable ) {

                $this->user->flushGetAll( 'KEY-Sweepstake' );
            }
            
        } 
        else {

            // get and log error message
            $errorMessage = $this->db->_error_message();
            $errorMessage = log_message( 'error', 'Insert sweepstake failed: ' . $errorMessage );

            $result = array( 'code' => 1, 'message' => $errorMessage, 'statusCode' => 400 );
        }

        return $result;
    }

	/**
	* add a sweepstake
	* @param array $data form post
	*/
	public function add( $data ) {

        // validate data insert 
		if ( empty( $data ) ) {

            // return log errors when data miss/ invalid
            $errors =  array( 'code' => 1, 'message' => 'Please the required enter data', 'statusCode' => 400 );

            return $errors;
        } 
        else {

	        // reset errors messages
	        $this->form_validation->reset_validation();

            // set data for all field to validation
			$this->form_validation->set_params( $data );

            // set rules validation
			$this->form_validation->set_rules( $this->validate );

			if ( $this->form_validation->run() === FALSE ) {
				
				$errors = $this->form_validation->validation_errors();

                // return result errors log
				$result = array( 'code' => 2, 'message' => $errors, 'statusCode' => 400 );
			}
			else {

                // conver string time for field startDate
				$startDateInt = strtotime( str_replace( '-', '/', $data['startDate'] ) );

                // conver string time for field endDate
				$endDateInt = strtotime( str_replace( '-', '/', $data['endDate'] ) );
				
				if ( $startDateInt >= $endDateInt ) {

					$result = array( 'code' => 3, 'message' => 'End Date must greater than Start Date', 'statusCode' => 400 );
				}
				else {

                    $checkSweepstakeType = $data['sweepstakeType']; 

                    if ( $checkSweepstakeType === "closed" ) {

                        if ( isset( $data['maxEntrants'] ) && is_numeric( $data['maxEntrants']) && (int)$data['maxEntrants'] > 0) {

                            if ( (int)$data['maxEntrants'] < (int)$data['ratioTicket'] ) {

                                $result = array('code' => 4, 'message'=> "The maxEntrants must be greater than ratioTicket", 'statusCode' => 400);
                                
                            } 
                            else {

                                $result = $this->insertSweepstakeDB( $data );
                            }
                        } 
                        else {

                            $result = array('code' => 5, 'message' => "The maxEntrants should be required, greater than zero", 'statusCode' => 400);
                        }
                    } 
                    else {

                        $result = $this->insertSweepstakeDB( $data );
                    }

				}
			}
            
            // return object sweepstake when create new sweepstake successful
			return $result;
		}
	}

	/**
	* update a sweepstake by id
	* @param  int $id  sweepstake id
	* @param  [type] $data [description]
	* @return [type]       [description]
	*/
	public function edit( $id, $data ) {

        // validate data edit
		if ( empty( $data ) ) {

            // return errors when data requires is invalid/ miss
            $errors = array( 'code' => 1, 'message' => 'Please the required enter data', 'statusCode' => 400 );

            // return errors
            return $errors;
        } 
        else {

            // validate id 
			if ( ! is_numeric($id) || $id <= 0 ) {

                // return array errors when id is valid
                $logErrors = array( 'code' => 2, 'message' => 'Id must is a numeric and greater than zero', 'statusCode' => 400 );     
				
                return $logErrors;
			}

	        // reset form validation
	        $this->form_validation->reset_validation();

			// set form data to validate
			$this->form_validation->set_params( $data );

			// remove validate element if field not exists in data array
	        $validate = array_intersect_key( $this->validate, $data );

            // set rule validation
			$this->form_validation->set_rules( $validate );

			if ( $this->form_validation->run() === FALSE ) {

				$errors = $this->form_validation->validation_errors();

				if ( isset( $errors[0]) && ! $errors[0] ) {
	                $result = array( 'code' => 6, 'message' => 'Please enter the required data', 'statusCode' => 400 );
	            }
	            else {
	                // return log errors when validation reture FASLE
	                $result = array( 'code' => 3, 'message' => $errors, 'statusCode' => 400 );
				}

                return $result;
			} 
			else {

                // check isset start date and end date
				if ( isset( $validate['startDate'] ) || isset( $validate['endDate'] ) ) {
					
                    // get sweepstake by id 
					$sweepstake = $this->getById( $id );

					if ( ! isset( $validate['endDate'] ) ) {

                        // if empty validate endDate will set endDate for sweepstake 
						$data['endDate'] = $sweepstake->endDate;
					}

					if ( ! isset( $validate['startDate'] ) ) {

                        //if empty validate start date will set start date for sweepstake 
						$data['startDate'] = $sweepstake->startDate;
					}

                    // convert start date, end date to string
					$startDateInt = strtotime( str_replace( '-', '/', $data['startDate'] ) );
					$endDateInt = strtotime( str_replace( '-', '/', $data['endDate'] ) );
					
                    // if start time greater than end time   
					if ( $startDateInt >= $endDateInt ) {
						
                        // return log errors when start time greater than end time
                        $logErrors = array( 'code' => 4, 'message' => "End Date ({$data['endDate']}) must greater than Start Date ({$data['startDate']})", 'statusCode' => 400 );

						return $logErrors;
					}
				}

				// set skip_validation = TRUE in 3rd parameter
				$isUpdated = $this->update( $id, $data, TRUE );

				if ( $isUpdated ) {

                    // get sweepstake by id from database
					$result = $this->getByIdFromDb( $id );

					// update memcache
					if ($this->memcacheEnable) {

                        $keyOne = "KEY-Id-Sweepstake" . md5( "getOne_get-sweepstake_id:$id");
                
		                $this->user->updateMemcache( $keyOne, $result );
		                $this->user->flushGetAll( 'KEY-Sweepstake' );
						
					}
				} 
                else {

					// get and log error message
					$errorMessage = $this->db->_error_message();
					log_message( 'error', 'Update Sweepstake: ' . $errorMessage );

					$result = array( 'code' => 5, 'message' => $errorMessage, 'statusCode' => 400  );
				}

                // return result when update successful
				return $result;
			}
		}

	}

	/**
	* detele a sweepstake -> only update isDeleted to 1
	* @param  int $id
	* @return $result  
	*/
	public function destroy( $id ) {

        // validate id destroy
		if ( ! is_numeric($id) || $id <= 0 ) {
            
            // return array log errors when id input is invalid
            $errors = array( 'code' => 1, 'message' => 'Id must is a numeric and greater than zero', 'statusCode' => 400 ); 
			
            return $errors;
		}

        // delete sweepstake by id from database
		$isDeleted = $this->delete( $id );

		if ( $isDeleted ) {

			// update memcache if enbale caching
			if ($this->memcacheEnable) {
				
				$key = "KEY-Id-Sweepstake" . md5( "getOne_get-sweepstake_id:$id" );

                // set result into memcache of player
				$this->user->updateMemcache( $key, null );
                $this->user->flushGetAll( 'KEY-Sweepstake' );
			}

            $results = array( null, 'statusCode' => 204 ); 
			
            return $results;
		
		}
		else {

			// get and log error message
			$errorMessage = $this->db->_error_message();
			log_message( 'error', 'Delete Sweepstake: ' . $errorMessage );

            // return array log error when delele sweepstake unsuccessful
            $errors = array( 'code' => 2, 'message' => $errorMessage, 'statusCode' => 400  );

			return $errors;
		}
	}
    /**
     * listEnterTicketByPlayerId get list ticket entered into sweepstake by player
     * @param  int  $playerId     
     * @param  int  $sweepstakeId 
     * @param  int $limit        
     * @param  int $offset       
     * @return array                
     */
    public function listEnteredBySweepstakeFromDB( $playerId, $sweepstakeId, $limit , $offset ) {

        // loading model sweepstaketicket
        $playerId = (int) $playerId;
        $sweepstakeId = (int) $sweepstakeId;
        $this->load->model('sweepstaketicket');
        $count = 0;

        // get list sweepstake has entered by player
        $this->db->select("Tickets.*, (SELECT count(t.id) FROM `Tickets` AS t LEFT JOIN `SweepstakeTickets` AS s ON s.ticketId = t.id WHERE t.playerId = {$playerId} AND s.sweepstakeId = '{$sweepstakeId}') as count", FALSE);
        $this->db->where(array('Tickets.playerId' => $playerId, 'SweepstakeTickets.sweepstakeId' => $sweepstakeId));
        $this->db->join('SweepstakeTickets', 'SweepstakeTickets.ticketId = Tickets.id', 'left'); 
        $query = $this->db->get('Tickets', $limit, $offset);
        $list = $query->result();

        // check query return
        if ( empty( $list ) ) {

            $result = array( 'code' => 1, 'message'=> 'Ticket Not Found with Sweepstake Id ' . $sweepstakeId, 'statusCode' => 404 );
        } 
        else {

            // get count list return from list object sweepstake 
            $count = (int)$list[0]->count;

            // unset count on list return
            foreach ($list as $key => $value) {

                unset( $value->count );
            }

            // return result
            $result = array( 'code' => 0, 'sweepstakes' => $list, 'limit' => (int)$limit, 'offset' => (int)$offset, 'count' => $count, 'statusCode' => 200 );
        }
        
        return $result;
    }
    
    public function listEnteredBySweepstake( $playerId, $sweepstakeId, $limit , $offset ) {

         // if enable memcache
        if ( $this->memcacheEnable ) {

            $key = "KEY-Sweepstake-playerId-$playerId-" . md5( "getListEntered_getByPlayer:$playerId-sweepstakeId:$sweepstakeId-offset:$offset-limit:$limit" );

            // the first at all, get the result from memcache
            $result = $this->memcacheInstance->get( $key );

            if ( $result ) return $result;

        } 

        $result = $this->listEnteredBySweepstakeFromDB( $playerId, $sweepstakeId, $limit , $offset );

        if ( $this->memcacheEnable && (int)$result['code'] === 0 ) {

	        // set the result to memcache
	        $this->user->updateMemcache( $key, $result );
	    }

        // if not enable memcache will get from db
        return $result;
    }

    public function listEntered( $playerId, $limit, $offset ) {

    	 // if enable memcache
        $this->load->model("ticketaggregate");
        if ( $this->memcacheEnable ) {

            $key = "KEY-Sweepstake-playerId-$playerId-" . md5( "listEntered:$playerId-offset:$offset-limit:$limit" );

            // the first at all, get the result from memcache
            $result = $this->memcacheInstance->get( $key );

            if ( $result ) return $result;
        } 

        // select ticket has been enetered by player id
        // TODO - with admin can show all ticket not set where player?
        
        $list = $this->ticketaggregate->getAll($playerId);
       
        if ( ! empty( $list ) ) {	        			
        	$result = array( 'code' => 0, 'tickets' => $list, 'limit' => (int)$limit, 'offset' => (int)$offset, 'count' => (int)count($list), 'statusCode' => 200 );
		    
		    if ( $this->memcacheEnable ) {  
		        
		        // set the result to memcache
		        $this->user->updateMemcache( $key, $result,10);
		    }
		}
		else {

            $result = array( 'code' => 1, 'message'=> 'Ticket Not Found', 'statusCode' => 404 );
		}

        return $result;
    }

    /**
     * getAllTicketBySweepstakesId get all ticket had entered into sweepstakes
     * @param  int $sweepstakeId 
     * @return array
     */
    protected function getAllTicketEnteredBySweepstakesId( $sweepstakeId ) {

        // loading model sweepstaketicket
        $this->load->model('sweepstaketicket');
        $count = 0;

        // get list sweepstake has entered by player
        $this->db->select("Tickets.*");
        $this->db->where( array( 'SweepstakeTickets.sweepstakeId' => $sweepstakeId ) );
        $this->db->join( 'SweepstakeTickets', 'SweepstakeTickets.ticketId = Tickets.id', 'left' ); 
        $query = $this->db->get('Tickets');
        $list = $query->result();

        // check query return
        if ( empty( $list ) ) {

            $result = array( 'code' => 1, 'message'=> 'List Not Found', 'statusCode' => 404 );
        } 
        else {

            // get count list return from list object sweepstake 
            $count = sizeof( $list );

            // return result
            $result = array( 'tickets' => $list, 'count' => $count, 'statusCode' => 200 );
        }
        
        return $result; 
    }

    /**
     * raffleTickets get ticket happen _after_ the end date of the sweepstake
     * @param  int $sweepstakeId 
     * @return array
     */
    public function raffleTickets( $sweepstakeId ) {

        // validate id 
        if ( ! is_numeric($sweepstakeId) || $sweepstakeId <= 0 ) {

            // return array errors when id is valid
            $logErrors = array( 'code' => 1, 'message' => 'Sweepstakes Id must is a numeric and greater than zero', 'statusCode' => 400 );     
            
            return $logErrors;
        } 

        $isValidSweepstake = $this->sweepstake->getById($sweepstakeId);

        if ( is_object( $isValidSweepstake ) ) {

            $timeNow = strtotime( date( 'm/d/Y H:i:s' ));
            $endDate = strtotime( str_replace('-', '/', $isValidSweepstake->endDate ));
            $theEnd = $isValidSweepstake->endDate;

            if ( $endDate <= $timeNow ) {

                $tickets = $this->getAllTicketEnteredBySweepstakesId( $sweepstakeId );

                if ( isset( $tickets['tickets'] ) )  {

                    $id = rand( 0, $tickets['count'] - 1);

                    foreach ($tickets['tickets'] as $key => $ticket) {
                        
                        if ( (int)$key == $id ) {

                            $result = array($ticket, 'statusCode' => 200);

                            return $result;
                        }

                    }
                }
                else {

                    $errors = array('code' => 2, 'message' => "Tickets not found", 'statusCode' => 400);
                    return $errors;
                }
            }
            else {

                $errors = array('code' => 3, 'message' => "the raffle can only happen _after_ the end date {$theEnd} of the Sweepstakes", 'statusCode' => 400);

                return $errors;
            }
        } 
        else {

            $errors = array('code' => 4, 'message' => "Sweepstake not found or had been deleted", 'statusCode' => 400);
            return $errors;

        }
    }
}