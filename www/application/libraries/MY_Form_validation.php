<?php

class MY_Form_validation extends CI_Form_validation {

	protected $params = array();
	public $memcacheEnable;
	public $memcacheInstance;
	
	// --------------------------------------------------------------------

	/**
	 * Set Rules
	 *
	 * This function takes an array of field names and validation
	 * rules as input, validates the info, and stores it
	 *
	 * @access	public
	 * @param	mixed
	 * @param	string
	 * @return	void
	 */
	public function set_rules($field, $label = '', $rules = '')
	{
		// No reason to set rules if we have no POST data
		if (count($this->params) == 0)
		{
			return $this;
		}

		// If an array was passed via the first parameter instead of indidual string
		// values we cycle through it and recursively call this function.
		if (is_array($field))
		{
			foreach ($field as $row)
			{
				// Houston, we have a problem...
				if ( ! isset($row['field']) OR ! isset($row['rules']))
				{
					continue;
				}

				// If the field label wasn't passed we use the field name
				$label = ( ! isset($row['label'])) ? $row['field'] : $row['label'];

				// Here we go!
				$this->set_rules($row['field'], $label, $row['rules']);
			}
			return $this;
		}

		// No fields? Nothing to do...
		if ( ! is_string($field) OR  ! is_string($rules) OR $field == '')
		{
			return $this;
		}

		// If the field label wasn't passed we use the field name
		$label = ($label == '') ? $field : $label;

		// Is the field name an array?  We test for the existence of a bracket "[" in
		// the field name to determine this.  If it is an array, we break it apart
		// into its components so that we can fetch the corresponding POST data later
		if (strpos($field, '[') !== FALSE AND preg_match_all('/\[(.*?)\]/', $field, $matches))
		{
			// Note: Due to a bug in current() that affects some versions
			// of PHP we can not pass function call directly into it
			$x = explode('[', $field);
			$indexes[] = current($x);

			for ($i = 0; $i < count($matches['0']); $i++)
			{
				if ($matches['1'][$i] != '')
				{
					$indexes[] = $matches['1'][$i];
				}
			}

			$is_array = TRUE;
		}
		else
		{
			$indexes	= array();
			$is_array	= FALSE;
		}

		// Build our master array
		$this->_field_data[$field] = array(
			'field'				=> $field,
			'label'				=> $label,
			'rules'				=> $rules,
			'is_array'			=> $is_array,
			'keys'				=> $indexes,
			'postdata'			=> NULL,
			'error'				=> ''
		);

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Run the Validator
	 *
	 * This function does all the work.
	 *
	 * @access	public
	 * @return	bool
	 */
	public function run($group = '')
	{
		// Do we even have any data to process?  Mm?
		if (count($this->params) == 0)
		{
			return FALSE;
		}

		// Does the _field_data array containing the validation rules exist?
		// If not, we look to see if they were assigned via a config file
		if (count($this->_field_data) == 0)
		{
			// No validation rules?  We're done...
			if (count($this->_config_rules) == 0)
			{
				return FALSE;
			}

			// Is there a validation rule for the particular URI being accessed?
			$uri = ($group == '') ? trim($this->CI->uri->ruri_string(), '/') : $group;

			if ($uri != '' AND isset($this->_config_rules[$uri]))
			{
				$this->set_rules($this->_config_rules[$uri]);
			}
			else
			{
				$this->set_rules($this->_config_rules);
			}

			// We're we able to set the rules correctly?
			if (count($this->_field_data) == 0)
			{
				log_message('debug', "Unable to find validation rules");
				return FALSE;
			}
		}

		// Load the language file containing error messages
		$this->CI->lang->load('form_validation');

		// Cycle through the rules for each field, match the
		// corresponding http request item and test for errors
		foreach ($this->_field_data as $field => $row)
		{
			// Fetch the data from the corresponding http request array and cache it in the _field_data array.
			// Depending on whether the field name is an array or a string will determine where we get it from.

			if ($row['is_array'] == TRUE)
			{
				$this->_field_data[$field]['postdata'] = $this->_reduce_array($this->params, $row['keys']);
			}
			else
			{
				if (isset($this->params[$field]) AND $this->params[$field] != "")
				{
					$this->_field_data[$field]['postdata'] = $this->params[$field];
				}
			}

			$this->_execute($row, explode('|', $row['rules']), $this->_field_data[$field]['postdata']);
		}

		// Did we end up with any errors?
		$total_errors = count($this->_error_array);

		if ($total_errors > 0)
		{
			$this->_safe_form_data = TRUE;
		}

		// Now we need to re-set the POST data with the new, processed data
		$this->_reset_post_array();

		// No errors, validation passes!
		if ($total_errors == 0)
		{
			return TRUE;
		}

		// Validation fails
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Re-populate the http request array with our finalized and processed data
	 *
	 * @access	private
	 * @return	null
	 */
	protected function _reset_post_array()
	{
		
		foreach ($this->_field_data as $field => $row)
		{
			if ( ! is_null($row['postdata']))
			{
				if ($row['is_array'] == FALSE)
				{
					if (isset($this->params[$row['field']]))
					{
						$this->params[$row['field']] = $this->prep_for_form($row['postdata']);
					}
				}
				else
				{
					// start with a reference
					$post_ref =& $this->params;

					// before we assign values, make a reference to the right POST key
					if (count($row['keys']) == 1)
					{
						$post_ref =& $post_ref[current($row['keys'])];
					}
					else
					{
						foreach ($row['keys'] as $val)
						{
							$post_ref =& $post_ref[$val];
						}
					}

					if (is_array($row['postdata']))
					{
						$array = array();
						foreach ($row['postdata'] as $k => $v)
						{
							$array[$k] = $this->prep_for_form($v);
						}

						$post_ref = $array;
					}
					else
					{
						$post_ref = $this->prep_for_form($row['postdata']);
					}
				}
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Executes the Validation routines
	 *
	 * @access	private
	 * @param	array
	 * @param	array
	 * @param	mixed
	 * @param	integer
	 * @return	mixed
	 */
	protected function _execute($row, $rules, $postdata = NULL, $cycles = 0)
	{
		// If the http request data is an array we will run a recursive call
		if (is_array($postdata))
		{
			foreach ($postdata as $key => $val)
			{
				$this->_execute($row, $rules, $val, $cycles);
				$cycles++;
			}

			return;
		}

		// --------------------------------------------------------------------

		// If the field is blank, but NOT required, no further tests are necessary
		$callback = FALSE;
		if ( ! in_array('required', $rules) AND is_null($postdata))
		{
			// Before we bail out, does the rule contain a callback?
			if (preg_match("/(callback_\w+(\[.*?\])?)/", implode(' ', $rules), $match))
			{
				$callback = TRUE;
				$rules = (array('1' => $match[1]));
			}
			else
			{
				return;
			}
		}

		// --------------------------------------------------------------------

		// Isset Test. Typically this rule will only apply to checkboxes.
		if (is_null($postdata) AND $callback == FALSE)
		{
			if (in_array('isset', $rules, TRUE) OR in_array('required', $rules))
			{
				// Set the message type
				$type = (in_array('required', $rules)) ? 'required' : 'isset';

				if ( ! isset($this->_error_messages[$type]))
				{
					if (FALSE === ($line = $this->CI->lang->line($type)))
					{
						$line = 'The field was not set';
					}
				}
				else
				{
					$line = $this->_error_messages[$type];
				}

				// Build the error message
				$message = sprintf($line, $this->_translate_fieldname($row['label']));

				// Save the error message
				$this->_field_data[$row['field']]['error'] = $message;

				if ( ! isset($this->_error_array[$row['field']]))
				{
					$this->_error_array[$row['field']] = $message;
				}
			}

			return;
		}

		// --------------------------------------------------------------------

		// Cycle through each rule and run it
		foreach ($rules As $rule)
		{
			$_in_array = FALSE;

			// We set the $postdata variable with the current data in our master array so that
			// each cycle of the loop is dealing with the processed data from the last cycle
			if ($row['is_array'] == TRUE AND is_array($this->_field_data[$row['field']]['postdata']))
			{
				// We shouldn't need this safety, but just in case there isn't an array index
				// associated with this cycle we'll bail out
				if ( ! isset($this->_field_data[$row['field']]['postdata'][$cycles]))
				{
					continue;
				}

				$postdata = $this->_field_data[$row['field']]['postdata'][$cycles];
				$_in_array = TRUE;
			}
			else
			{
				$postdata = $this->_field_data[$row['field']]['postdata'];
			}

			// --------------------------------------------------------------------

			// Is the rule a callback?
			$callback = FALSE;
			if (substr($rule, 0, 9) == 'callback_')
			{
				$rule = substr($rule, 9);
				$callback = TRUE;
			}

			// Strip the parameter (if exists) from the rule
			// Rules can contain a parameter: max_length[5]
			$param = FALSE;
			if (preg_match("/(.*?)\[(.*)\]/", $rule, $match))
			{
				$rule	= $match[1];
				$param	= $match[2];
			}

			// Call the function that corresponds to the rule
			if ($callback === TRUE)
			{
				if ( ! method_exists($this->CI, $rule))
				{
					continue;
				}

				// Run the function and grab the result
				$result = $this->CI->$rule($postdata, $param);

				// Re-assign the result to the master data array
				if ($_in_array == TRUE)
				{
					$this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
				}
				else
				{
					$this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
				}

				// If the field isn't required and we just processed a callback we'll move on...
				if ( ! in_array('required', $rules, TRUE) AND $result !== FALSE)
				{
					continue;
				}
			}
			else
			{
				if ( ! method_exists($this, $rule))
				{
					// If our own wrapper function doesn't exist we see if a native PHP function does.
					// Users can use any native PHP function call that has one param.
					if (function_exists($rule))
					{
						$result = $rule($postdata);

						if ($_in_array == TRUE)
						{
							$this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
						}
						else
						{
							$this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
						}
					}
					else
					{
						log_message('debug', "Unable to find validation rule: ".$rule);
					}

					continue;
				}

				$result = $this->$rule($postdata, $param);

				if ($_in_array == TRUE)
				{
					$this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
				}
				else
				{
					$this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
				}
			}

			// Did the rule test negatively?  If so, grab the error.
			if ($result === FALSE)
			{
				if ( ! isset($this->_error_messages[$rule]))
				{
					if (FALSE === ($line = $this->CI->lang->line($rule)))
					{
						$line = 'Unable to access an error message corresponding to your field name.';
					}
				}
				else
				{
					$line = $this->_error_messages[$rule];
				}

				// Is the parameter we are inserting into the error message the name
				// of another field?  If so we need to grab its "field label"
				if (isset($this->_field_data[$param]) AND isset($this->_field_data[$param]['label']))
				{
					$param = $this->_translate_fieldname($this->_field_data[$param]['label']);
				}

				// Build the error message
				$message = sprintf($line, $this->_translate_fieldname($row['label']), $param);

				// Save the error message
				$this->_field_data[$row['field']]['error'] = $message;

				if ( ! isset($this->_error_array[$row['field']]))
				{
					$this->_error_array[$row['field']] = $message;
				}

				return;
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Match one field to another
	 *
	 * @access	public
	 * @param	string
	 * @param	field
	 * @return	bool
	 */
	public function matches($str, $field)
	{
		if ( ! isset($this->params[$field]))
		{
			return FALSE;
		}

		$field = $this->params[$field];

		return ($str !== $field) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	* Retrieve the validation errors.
	*
	* @return array
	*/
	public function validation_errors()
	{
		$string = strip_tags($this->error_string());

		return explode("\n", trim($string, "\n"));
	}

	/**
	* set form data for this params
	* @return null
	*/
	public function set_params( $data )
	{
		$this->params = $data;
	}

	/**
	* reset array error to null
	* @return null
	*/
	public function reset_validation()
	{
		$this->_field_data = array();
        $this->_config_rules = array();
        $this->_error_array = array();
        $this->_error_messages = array();
        $this->error_string = '';

        return $this;
	}

	/**
	 * Match one field to another
	 *
	 * @access	public
	 * @param	string
	 * @param	field
	 * @return	bool
	 */
	public function email_phone_unique($emailPhone, $field)
	{	
		$this->set_message('email_phone_unique', "This email address or phone number has already been used. Please enter a new one.");
		
		$emailPhone = md5( $emailPhone );

		if ( $this->memcacheEnable ) {

			// the idea that check the hash of email is existed in cache or not
			$key = "KEY" . $emailPhone;

			return empty( $this->memcacheInstance->get( $key ) );
		}
		else {

			list( $table, $email, $phone ) = explode('.', $field);
			
        	$query = $this->CI->db->where( $email, $emailPhone )->or_where( $phone, $emailPhone )->get( $table, 1 );

			return $query->num_rows() === 0;
		}
	}


	/**
	* validate date of birth
	* @param  array $dob array('bday' => 1, 'bmonth' => 12, 'byear' => 2014)
	* @return bool
	*/
	public function date_of_birth( $dob )
	{
		$isValid = FALSE;
		
		$birthDate = $dob['bmonth'] . '-' . $dob['bday'] . '-' . $dob['byear'] ;
		$birthTime = $birthDate . ' ' . date('H:i:s');

		$isValid = $this->valid_datetime( $birthTime );

		if ( $isValid ) {

			$age = DateTime::createFromFormat('m-d-Y', $birthDate)
			     ->diff(new DateTime('now'))
			     ->y;

			$isValid = ($age >= 21 && $dob['byear'] > 1900 );
		}

		$this->set_message('date_of_birth', 'Birthdate is not in the correct format');
		
		return $isValid;
	}

	/**
	 * Performs a Regular Expression match test.
	 *
	 * @param    string
	 * @param    regex
	 * @return    bool
	 */
	function regex_match($str, $regex)
	{
		if ( ! preg_match($regex, $str))
		{
			return FALSE;
		}

		$this->set_message('regex_match', 'The %s field is not in the correct format.');
		
		return  TRUE;
	}

	/** 
	 * validate datetime
	 * @param   string $str
	 * @return bool
	 */
	function valid_datetime( $str )
	{
		
		$dateformat = 'm-d-Y H:i:s';
		$date = DateTime::createFromFormat($dateformat, $str);

		$this->set_message('valid_datetime', 'The %s field must contain a valid date (m-d-Y H:i:s)');

		$errors = DateTime::getLastErrors();
		$warning_count = 0;
		if (!empty($errors['warning_count']))
			$warning_count = $errors['warning_count'];
		
		$error_count = 0;
		if (!empty($errors['error_count']))
			$error_count = $errors['error_count'];
		return $date && $warning_count == 0 && $error_count == 0;
	}

	/** 
	 * validate date
	 * @param   string $str
	 * @return bool
	 */
	function valid_date( $str )
	{
		
		$dateformat = 'm-d-Y';
		$date = DateTime::createFromFormat($dateformat, $str);

		$this->set_message('valid_date', 'The %s field must contain a valid date (m-d-Y)');

		$errors = DateTime::getLastErrors();
		$warning_count = 0;
		if (!empty($errors['warning_count']))
			$warning_count = $errors['warning_count'];
		
		$error_count = 0;
		if (!empty($errors['error_count']))
			$error_count = $errors['error_count'];
			
		return $date && $warning_count == 0 && $error_count == 0;
	}
    
            function valid_db_date($str)
            {
                return strtotime($str);
            }

	function valid_honorific( $str )
	{

		$array = array( 'mr.','dr.', 'miss', 'ms.','mrs.' );

		$isValid = (bool)count( array_intersect( array_map( 'strtolower', explode(' ', $str ) ), $array ) );

		$this->set_message( 'valid_honorific', 'The %s is not in the correct format' );

		return $isValid;
	}

	function valid_phone( $phone )
	{

		$isValid = FALSE;

		$this->set_message( 'valid_phone', 'The %s is not in the correct format ((555)-555-5555 or (555)555-5555 or 5555555555)' );

		$formats = array(
		    "/^\([0-9]{3}\)-[0-9]{3}-[0-9]{4}$/i", // (555)-555-5555
		    "/^\([0-9]{3}\) [0-9]{3}-[0-9]{4}$/i", // (555) 555-5555
		    "/^\([0-9]{3}\) [0-9]{3} [0-9]{4}$/i", // (555) 555 5555
		    "/^\([0-9]{3}\)[0-9]{3}-[0-9]{4}$/i", // (555)555-5555
		    "/^[0-9]{10}$/i", // 5555555555
		    "/^\([0-9]{3}\)[0-9]{3}.[0-9]{4}$/i", // (555)555.5555
		    "/^[0-9]{3}.[0-9]{3}.[0-9]{4}$/i", // 555.555.5555
		    "/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/i", // 555-555-5555
		);
		
		foreach( $formats as $format ) {
	        
	        // Loop through formats, if a match is found return true
	        if( preg_match( $format, $phone ) ) {
	        
	        	return TRUE;
	        }
	    }	
		
		return $isValid; 
	}

	function valid_email_phone( $emailMobile )
	{

		$isValid = FALSE;

		$this->set_message( 'valid_email_phone', 'Invalid email or phone number. Phone number needs to be 10 digits (1112223333).' );
                        
		$formats = array(
		    "'/^(\d[\s-]?)?[\(\[\s-]{0,2}?\d{3}[\)\]\s-]{0,2}?\d{3}[\s-]?\d{4}$/i'",
                        "/^[0-9]{10}$/i"
		);                           
                    
		foreach( $formats as $format ) {
	        
	        // Loop through formats, if a match is found return true
	        if( preg_match( $format, ltrim($emailMobile, "+1") ) ) {
	        
	        	$isValid = TRUE;
	        	break;
	        }
	    }

		if ( $isValid ) return TRUE;
		
		$isValid = $this->valid_email( $emailMobile );

		return $isValid;
	}
    
    public function valid_alpha_numeric_spaces($field)
    {
        if(preg_match("/^([0-9]|[a-z]|[A-Z]| )+$/", $field) !== false)
            return true;
        return false;
    }

	public function valid_address( $address ) {

		$isValid = FALSE; 
		
		if ( strlen($address) <= 200 )
		{
			$poBoxPattern = '/^[#.]{0,2}\s*((?:P(?:OST)?.?\s*(?:0(?:O(?:FF(?:ICE)?)?)?)?.?\s*(?:B(?:IN|OX)? )+)+|(?:B(?:IN|OX)+\s+)+)\s*\d+/i';
			
			preg_match($poBoxPattern, $address, $matches); 
			
			$result = count($matches);
			
			if ( $result === 0 )
			{
				$isValid = ! is_numeric( $address );
			}
		}

		$this->set_message( 'valid_address', 'Address must be less than 200 characters' );
		
		return $isValid;
	}

    function valid_type_sweepstake( $str ) {

        $array = array( 'open', 'closed' );

        $isValid = (bool)count( array_intersect( array_map( 'strtolower', explode(' ', $str ) ), $array ) );

        $this->set_message( 'valid_type_sweepstake', 'The %s is not in the correct format. It should be open/closed' );

        return $isValid;
    }

    function valid_country_code( $str ) {

    	$countryCode = array(
    		'AF','AL','DZ','AD','AO','AG','AR','AM','AU','AT','AZ','BS','BH','BD','BB','BY','BE','BZ','BJ','BT','BO','BA','BW',
    		'BR','BN','BG','BF','BI','KH','CM','CA','CV','CF','TD','CL','CN','CO','KM','CD','CG','CR','CI','HR','CU','CY','CZ',
    		'DK','DJ','DM','DO','EC','EG','SV','GQ','ER','EE','ET','FJ','FI','FR','GA','GM','GE','DE','GH','GR','GD','GT','GN',
    		'GW','GY','HT','HN','HU','IS','IN','ID','IR','IQ','IE','IL','IT','JM','JP','JO','KZ','KE','KI','KP','KR','KW','KG',
    		'LA','LV','LB','LS','LR','LY','LI','LT','LU','MK','MG','MW','MY','MV','ML','MT','MH','MR','MU','MX','FM','MD','MC',
    		'MN','ME','MA','MZ','MM','NA','NR','NP','NL','NZ','NI','NE','NG','NO','OM','PK','PW','PA','PG','PY','PE','PH','PL',
    		'PT','QA','RO','RU','RW','KN','LC','VC','WS','SM','ST','SA','SN','RS','SC','SL','SG','SK','SI','SB','SO','ZA','ES',
    		'LK','SD','SR','SZ','SE','CH','SY','TJ','TZ','TH','TL','TG','TO','TT','TN','TR','TM','TV','UG','UA','AE','GB','US',
    		'UY','UZ','VU','VA','VE','VN','YE','ZM','ZW','GE','TW','AZ','CY','MD','SO','GE','AU','CX','CC','AU','HM','NF','NC',
    		'PF','YT','GP','GP','PM','WF','TF','PF','BV','CK','NU','TK','GG','IM','JE','AI','BM','IO','VG','KY','FK','GI','MS',
    		'PN','SH','GS','TC','MP','PR','AS','UM','GU','UM','UM','UM','UM','UM','UM','UM','VI','UM','HK','MO','FO','GL','GF',
    		'GP','MQ','RE','AX','AW','AN','SJ','AC','TA','AQ','AQ','AQ','AQ','AQ' 
    	);
		
		$isValid = in_array( $str, $countryCode );

		$this->set_message( 'valid_country_code', 'The %s field is not in the correct format' );

		return $isValid;
    }

    /**
     * validate game type of winner
     * @param  string $str
     * @return book
     */
    function valid_game_type( $str ) {

    	$gameType = array( 'Slots','Scratchers','Sweepstakes','Parlay','BG','FT' );

    	$isValid = in_array( $str, $gameType );

		$this->set_message( 'valid_game_type', "The %s field only allow in ['Slots','Scratchers','Sweepstakes','Parlay','BG','FT']" );

		return $isValid;
    }
}