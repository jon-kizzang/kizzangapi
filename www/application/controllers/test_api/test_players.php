<?php
/**
* Building testing unit Players
*
* - Testing Get all players
* - Test get players by Id
* - Test create new player
* - Test update a player
* - Test delete a player
*/
class test_players extends CI_Controller {
  function __construct() {
    parent::__construct();

    // init the memcache
    $this->config->load( 'memcache' );

    $memcacheServer = $this->config->item( 'memcache_server' );
    $memcachePort = $this->config->item( 'memcache_port' );

    $memcacheInstance = new Memcache();
    $memcacheInstance->pconnect( $memcacheServer, $memcachePort );
    $memcacheInstance->flush();

    $this->load->library('form_validation');
    
    $this->load->helper(array('form', 'url'));
    // $this->load->library('form_validation'); 

    // loading library unit test
    $this->load->library('unit_test');

    // loading database test
    $this->load->database('test', TRUE);

    //To enable strict mode 
    $this->unit->use_strict(TRUE);

    // Disable database debugging so we can test all units without stopping
    // at the first SQL error
    $this->db->db_debug = FALSE;

    // Load syntax highlighting helper
    $this->load->helper('text');
  }

    /**
    * TestGetAll_get
    *
    * function testing get all list with limit and offset
    */
    public function testGetAll() {

        $count = $this->player->count_by('isDeleted', 0);
        if( $count > 0 ) {

            $getPlayer = $this->player->limit(3)->get_many_by( 'isDeleted', 0 );
            $offsetInvalid = array(null, '', 0, 'abc');
            $limitInvalid = array(null, '', 0, 'abc');

            // verify offset and limit is invalid
            foreach ($limitInvalid as $key => $value) {
                if ( array_key_exists($key, $offsetInvalid) ){
                    $testResultFirst = $this->player->getAll($value, $value);
                    if (is_array($testResultFirst) && isset($testResultFirst['errors']))
                        $this->unit->run($testResultFirst['errors'], "Player Not Found", "To verify get all list PlayPeriod is invalid", "To verify offset and limit is invalid");
                }
            }

            // In case invalid
            // =========================
            // In case limit is string

            // Testing limit and offset in case is numberic
            $limit = 2;
            $offset = 0;
            $testResultSeconds = $this->player->getAll($offset, $limit);

            //Players reponse return must be array
            $this->unit->run($testResultSeconds['players'], 'is_array',"In case is valid" ,"Players reponse return must be array");

            //ID player return must be equal ID when offset is zero
            if (isset($getPlayer[0]->id))
              $this->unit->run($testResultSeconds['players'][0]->id, $getPlayer[0]->id,"In case is valid" ,"ID player return must be equal ID when offset is zero");

            if($count >= $limit)

              //Players returnt must be equal limit
              $this->unit->run(sizeof($testResultSeconds['players']), $limit,"In case is valid" ,"Players returnt must be equal limit");

              //Count players return must assert Greater Than Or Equal players returnt
              $this->unit->run(sizeof($testResultSeconds['players']) <= $count,'is_true',"In case is valid" ,"Count players return must assert Greater Than Or Equal players returnt");

            // value limit return must equal limit before
            $this->unit->run(($testResultSeconds['limit']), $limit,"In case is valid" ,"value limit return must equal limit before");

            // value offset return must equal offset before
            $this->unit->run($testResultSeconds['offset'], $offset,"In case is valid" ,"value offset return must equal offset offset");

            // count players return must be equal count player get before
            $this->unit->run($testResultSeconds['count'], $count,"In case is valid" ,"count players return must be equal count player get before");  
            
            // Testing ID return follow value offset
            $offset = 2;
            $testResultThird = $this->player->getAll($offset, $limit);

            //ID player return must be equal ID when offset is 2
            if (isset($getPlayer[$offset]->id))
              $this->unit->run($testResultThird['players'][0]->id, $getPlayer[$offset]->id,"Testing ID return follow value offset" ,"ID player return must be equal ID when offset is 2"); 
            
        } else {

            echo "<h4 style='color: red;'>Can't verify Playper. Please make sure Player doesn't empty. Try run testing add new Player.<h4>";
        }

        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());

    }

  /**
   * TestGetOne
   *
   * function testing in case get a player by ID
   */
    public function testGetById() {
        $count = $this->player->count_by('isDeleted', 0);

        if( $count > 0) {
            $dataExpected = $this->player->with('gender')->order_by('id', 'DESC')->get_by('isDeleted', 0);
            $idExit = $dataExpected->id;
            $idNotExist = isset($dataExpected->id) ? ((int)$dataExpected->id + 1) : 1;
            //In case id player return is invalid
            $idInvalid = array(NULL,'', 0, -123, $idNotExist);
            foreach ($idInvalid as $key => $value) {
                
                $testResult = $this->player->getById($value, FALSE);
                if(is_array($testResult) && isset($testResult['errors'])) {
                    if( $key == 4) {
                        // Verify id isn't exist
                        $this->unit->run($testResult['errors'], "Not authorized", "To verify return Player is invalid", "To verify id isn't exist");
                    } else {

                        // Verify id return is invalid
                        $this->unit->run($testResult['errors'], "Id must be a numeric and greater than zero", "To verify return Player is invalid", "To verify id return is invalid");
                    }
                }
            }
            
            $testResultValid = $this->player->getById($idExit, TRUE);
            foreach ((array)$testResultValid as $key => $value) {
                if ( array_key_exists($key, (array)$dataExpected) ) {

                    if( $key != 'statusCode') {

                        // To verify get Playper return is valid
                        $this->unit->run($value, $dataExpected->$key, "To verify get Playper return is valid", "To verify $key return must be equal $key from databases" );
                    }
                }
            }
            
           
        } else {
            echo "<h4 style='color: red;'>Can't verify Playper by Id. Please make sure Player doesn't empty. Try run testing add new Player.<h4>";
        }

        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());
    }

    /**
    * TestPostCreatePlayer
    *
    * Function testing creating a player
    */
    public function testAddNewPlayer() {
        $emailDb = $this->player->as_array()->get_by( 'isDeleted', 0 );
        $playerPost = array(
            'email' => substr(str_shuffle(md5(time())), 0, 100)."test@gmail.com",
            'password' => "123456",
            'gender' => 1,
            'firstName' => "User",
            'lastName' => "Test",
            'dob' => array('bday' => 02, 'bmonth' => 12, 'byear' => 1989),
            'option3' => TRUE,
          );

        // In case is invalid
        //=====================
        // In case data need update a record not exist field of table players on database
        $dataRecordNotExist = $playerPost;
        $dataRecordNotExist['email'] = md5(date('Y-m-d H:i:s').rand(1,100))."testexit@gmail.com";
        $dataRecordNotExist['DataNotExist'] = "Not exit";

        $playerTest = $this->player->add($dataRecordNotExist);
        if ( is_object($playerTest) && isset($playerTest->id)) {

            $playersExpected = $this->player->getById($playerTest->id, TRUE);
            // DataNotExist will be not exist on info player return
            $this->unit->run(in_array('DataNotExist', (array)$playerTest), 'is_false', "In case invalid", "DataNotExist will be not exist on info player return");

            // player must be create
            $this->unit->run($playerTest, 'is_object', "Insert colunm not exist on table Player", "Player must be create");

            // all info of player create must be equal player in database
            foreach ((array)$playerTest as $key => $value) {

                if ( array_key_exists($key, (array)$playersExpected) ) {
                    if( $key != 'statusCode') {
                        $this->unit->run($playerTest->$key, $playersExpected->$key, "To verify add new players is valid", "all info of player create must be equal player in database");
                    }
                }
           }
        }
       
        //verify email is invalid
        //In case email empty
        
        $playerInvalid = $playerPost;
        $playerInvalid['email'] = NULL;
        $insertId = $this->player->add($playerInvalid);

        //InsertId return must be is zero
        $this->unit->run($insertId['errors'][0], "The email field is required.", "In case email empty", "InsertId return must be is zero!");

        //In case email invalid format
        $playerInvalid['email'] = "abc";

        //Email must be is correct format
        $this->unit->run(valid_email($playerInvalid['email']), FALSE, "In case email invalid format" ,"Email must be is correct format");
        $insertId = $this->player->add($playerInvalid);

        if (isset($emailDb[0])) {
          $emailExistDb = $emailDb[0]['AccountEmail'];

          //In case email already using
          $playerInvalid['email'] = $emailExistDb;
          $emailExit = $this->player->add($playerInvalid);
          if ( isset($emailExit['errors'][0]) )
            //This email address is already in use
            $this->unit->run($emailExit['errors'][0], "The email field must contain a unique value." ,"In case email already using" , "This email address is already in use");
        }

        //In case first name empty
        $playerFirstNameInvalid = $playerPost;
        $playerFirstNameInvalid['firstName'] = '';
        $insertId = $this->player->add($playerFirstNameInvalid);

        if ( isset($insertId['errors'][0]) )
          //firstName is required
          $this->unit->run($insertId['errors'][0], "The First Name field is required.","In case firstName empty" ,"FirstName is required");

        
        //In case last name empty
        $playerLastNameInvalid = $playerPost;
        $playerLastNameInvalid['lastName'] = '';
        $insertId = $this->player->add($playerLastNameInvalid);

        if ( isset($insertId['errors'][0])) {

          //LastName is required
          $this->unit->run($insertId['errors'][0], "The Last Name field is required.","In case LastName empty" ,"LastName is required");

        }

        // Verify gender is invalid
        $genderInvalid = $playerPost;
        $genderInvalid['gender'] = 'adaa';
        $genderTestInvalid = $this->player->add($genderInvalid);
        if ( isset($genderTestInvalid['errors'][0]) )
          //The Gender field is not in the correct format
          $this->unit->run($genderTestInvalid['errors'][0],'The Gender field is not in the correct format.' , "The Gender is invalid" ,"The Gender field is not in the correct format");

        // Verify Gender field is invalid when empty    
        $genderInvalid['gender'] = '';
        $genderTestEmpty = $this->player->add($genderInvalid);

        if ( isset($genderTestEmpty['errors'][0]) )

          //The Gender field is required.
          $this->unit->run($genderTestEmpty['errors'][0], "The Gender field is required.","The Gender is invalid" ,"The Gender field is required.");  

        //In case password empty
        $playerPwInvalid = $playerPost;
        $playerPwInvalid['password'] = '';
        $insertId = $this->player->add($playerPwInvalid);

        if ( isset($insertId['errors'][0]) )
          //password is required
          $this->unit->run($insertId['errors'][0], "The Password field is required.","In case password empty" ,"password is required");


        // To verify birth of day is invalid 
        $dataDob = $playerPost;
        $dataDob['dob'] = array('bday' => 02, 'bmonth' => 12, 'byear' => 1999);

        $testDobInvalid = $this->player->add($dataDob);
        if(is_array($testDobInvalid) && isset($testDobInvalid['errors'])) {

            // verify date of birth is'nt correct format
            $this->unit->run($testDobInvalid['errors'][0], "Birthdate is not in the correct format", "To verify create new player is invalid", "verify date of birth is'nt correct format");
        }

        // To verify birth of day is invalid 
        $dataDob = $playerPost;
        $dataDob['dob'] = array('bday' => 02, 'bmonth' => 12, 'byear' => 1900);

        $testDobInvalid = $this->player->add($dataDob);
        if(is_array($testDobInvalid) && isset($testDobInvalid['errors'])) {

            // verify date of birth is'nt correct format
            $this->unit->run($testDobInvalid['errors'][0], "Birthdate is not in the correct format", "To verify create new player is invalid", "verify date of birth is'nt correct format");
        }
        // To verify state is invalid 
        $stateInvalid = array('BCD', 12, -12, 0);

        foreach ($stateInvalid as $value) {
            
            $playerStateInvalid = $playerPost;
            $playerStateInvalid['state'] = $value;
            $testStateInvalid = $this->player->add($playerStateInvalid);

            if(is_array($testStateInvalid) && isset($testStateInvalid['errors'])) {

                if ($value == 'BCD' || $value == 0) {

                    // verify state  exceed 2 characters in length.
                    $this->unit->run($testStateInvalid['errors'][0], "The State field can not exceed 2 characters in length.", "To verify create player is invalid", "verify state  exceed 2 characters in length.");                

                } else {
                    
                    // verify State field may only contain alphabetical characters.
                    $this->unit->run($testStateInvalid['errors'][0], "The State field may only contain alphabetical characters.", "To verify create player is invalid", "verify State field may only contain alphabetical characters.");                
                }
            }
        }
        // To verify zip is invalid
        $playerPost['email'] = substr(str_shuffle(md5(time())), 0, 100)."testZipString@gmail.com";
        $playerZipInvalid = $playerPost;    
        $playerZipInvalid['zip'] = 'abc';
        $testZipInvalid = $this->player->add($playerZipInvalid);
        if(is_array($testZipInvalid) && isset($testZipInvalid['errors'])) {

                // verify state  exceed 2 characters in length.
                $this->unit->run($testZipInvalid['errors'][0], "The Zip field must contain only numbers.", "To verify create player is invalid", "verify state  exceed 2 characters in length.");                
        }

        $playerPost['email'] = substr(str_shuffle(md5(time())), 0, 100)."testZipInvalid@gmail.com";
        $playerZipInvalid = $playerPost;    
        $playerZipInvalid['zip'] = 1111111111111;
        $testZipInvalid = $this->player->add($playerZipInvalid);
        if(is_array($testZipInvalid) && isset($testZipInvalid['errors'])) {

            // verify zip field may only contain alphabetical characters.
            $this->unit->run($testZipInvalid['errors'][0], "The Zip field can not exceed 8 characters in length.", "To verify create player is invalid", "verify State field may only contain alphabetical characters.");                
        }

        $playerPost['email'] = substr(str_shuffle(md5(time())), 0, 100)."testCityInvalid@gmail.com";
        $playerCityInvalid = $playerPost;    
        $playerCityInvalid['city'] = 1111111111111;
        $testCityInvalid = $this->player->add($playerCityInvalid);
        if(is_array($testCityInvalid) && isset($testCityInvalid['errors'])) {

            // verify City field may only contain alphabetical characters.
            $this->unit->run($testCityInvalid['errors'][0], "The City field is not in the correct format.", "To verify create player is invalid", "verify City field may only contain alphabetical characters.");                
        }

        $playerPost['email'] = substr(str_shuffle(md5(time())), 0, 100)."testCityInvalid@gmail.com";
        $playerCityMaxInvalid = $playerPost;    
        $playerCityMaxInvalid['city'] = str_repeat("ab", 41);
        $testCityMaxInvalid = $this->player->add($playerCityMaxInvalid);
        if(is_array($testCityMaxInvalid) && isset($testCityMaxInvalid['errors'])) {

            // verify The City field can not exceed 40 characters in length.
            $this->unit->run($testCityMaxInvalid['errors'][0], "The City field can not exceed 40 characters in length.", "To verify create player is invalid", "verify CityMax field may only contain alphabetical characters.");                
        }

        $playerPost['email'] = substr(str_shuffle(md5(time())), 0, 100)."testhomePhoneInvalid@gmail.com";
        $playerhomePhoneInvalid = $playerPost;    
        $playerhomePhoneInvalid['homePhone'] = 'bbc';
        $testhomePhoneInvalid = $this->player->add($playerhomePhoneInvalid);

        if(is_array($testhomePhoneInvalid) && isset($testhomePhoneInvalid['errors'])) {

            // verify the Home Phone is not in the correct format ((555)-555-5555 or (555)555-5555 or 5555555555) )
            $this->unit->run($testhomePhoneInvalid['errors'][0], "The Home Phone is not in the correct format ((555)-555-5555 or (555)555-5555 or 5555555555)", "To verify create player is invalid", "verify homePhone The Home Phone is not in the correct format ((555)-555-5555 or (555)555-5555 or 5555555555) )");                
        }

         $playerPost['email'] = substr(str_shuffle(md5(time())), 0, 100)."testAddressInvalid@gmail.com";
        $playerAddressMaxInvalid = $playerPost;    
        $playerAddressMaxInvalid['address'] =  str_repeat("AB", 202);
        $testAddressMaxInvalid = $this->player->add($playerAddressMaxInvalid);

        if(is_array($testAddressMaxInvalid) && isset($testAddressMaxInvalid['errors'])) {

            // verify The Address field can not exceed 40 characters in length.
            $this->unit->run($testAddressMaxInvalid['errors'][0], "Address must be less than 200 characters", "To verify create player is invalid", "verify The Address field can not exceed 200 characters in length");                
        }

        // In case is valid
        // ====================

         $playerPostSecond = array(
            'email' => md5(date('Y-m-d H:i:s').rand(1,100))."test2@gmail.com",
            'password' => "123456",
            'gender' => 1,
            'firstName' => "User",
            'lastName' => "Test",
            'dob' => array('bday' => 02, 'bmonth' => 12, 'byear' => 1989)
          );

        $idInvalid = $this->player->add($playerPostSecond);
        if ( is_object($idInvalid) && isset($idInvalid->id)) {

            $player = $this->player->get($idInvalid->id);

            //ID insert of player when create must be equal ID player when get on database
            $this->unit->run($idInvalid->id, $player->id," In case is valid" ,"ID insert of player when create must be equal ID player when get on database");

            //email when get on database must be equal email when creating
            $this->unit->run($playerPostSecond['email'], $player->accountEmail," In case is valid" , "email when get on database must be equal email when creating");

            //email hash when get on database must be equal email hash when creating
            $this->unit->run(md5($playerPostSecond['email']), $player->emailHash," In case is valid" ,"email hash when get on database must be equal email hash when creating");

            //Password hash when get on database must be equal password hash when creating
            $this->unit->run(md5($playerPostSecond['password']), $player->passwordHash," In case is valid" ,"Password hash when get on database must be equal password hash when creating");

            //ScreenName when get on database must be equal ScreenName when creating
            $this->unit->run(($playerPostSecond['firstName'].' '.strtoupper( substr($playerPostSecond['lastName'] , 0, 1 ) )), $player->screenName," In case is valid" , "ScreenName when get on database must be equal ScreenName when creating");


            // Verify firstName return must be equal firstname input
            $this->unit->run($player->accountData['firstName'], $playerPostSecond['firstName'], "In case is valid", "Verify firstName return must be equal firstname input");
            
            // Verify lastname return must be equal lastName input
            $this->unit->run($player->accountData['lastName'], $playerPostSecond['lastName'], "In case is valid", "Verify lastname return must be equal firstname input");
            
            // Verify dob return must be equal dob input
            $dayOfBirth = $playerPostSecond['dob']['bmonth'] . '/' . $playerPostSecond['dob']['bday'] . '/' . $playerPostSecond['dob']['byear'];

            $this->unit->run($player->accountData['dob'], $dayOfBirth , "In case is valid", "Verify dob return must be equal dob input");

            // Verify that the passwordHash is ‘Not NULL’
            $this->unit->run($player->passwordHash, 'is_string', " In case is valid", "Verify that the passwordHash is 'Not NULL'" );

            // Verify that the registeredWithFB is a TINYINT(1)
            $this->unit->run((int)$player->registeredWithFB, 0, "In case is valid", "To verify hat the registeredWithFB is a TINYINT(1)");

            // The profileComplete flag should be false (0) when a player account is created
            $this->unit->run((int)$player->profileComplete, 0 , "In casee is valid", "Verify that the profileComplete is 0");
        }

         // To verify add player return is valid
        $data = array(
          'email' => md5(date('Y-m-d H:i:s').rand(1,100))."test3@gmail.com",  
          'password' => "123456new",
          'gender' => '1',
          'firstName' => "User Update",
          'lastName' => "Test Update",
          'address' => "604 NUI THANH, DA NANG",
          'state' => 'AB',
          'zip'=> 123,
          'honorific' => "miss",
          'city' => "DaNang",
          'homePhone' => "1200360000",
          'mobilePhone' =>"9999999999",
          'address2' => "193 NGUYEN LUON BANG",
          'dob' => array('bday' => 12, 'bmonth' => 12, 'byear' => 1989)
        );

        // The profileComplete flag is set when the user updates the player account data with all information populated
        $testResultFifth = $this->player->add($data);
        if (is_object($testResultFifth) && isset($testResultFifth->id)) {

            $this->unit->run((int)$testResultFifth->profileComplete, 1, "Verify is case is valid", "The profileComplete flag is set when the user updates the player account data with all information populated");
        }
        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());
    }

    /**
    * TestPutPlayer
    *
    * Function testing update a player
    */
    public function testEditPlayer() {

        // To verify update player return is valid
        $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);

        $login = $this->player->login($dataLogin);
        $this->player->setToken( $login['token'] );

        $count = $this->player->count_by('isDeleted', 0);
        $token = '';

        if ($count > 0) {
            $playerExpectedNotExit = $this->player->limit(1)->order_by('id', 'DESC')->get_all();
            $playerExpected = $this->player->as_array()->get_by( 'isDeleted', 0 );
            $playerExpectedOne = $this->player->as_array()->limit(2)->get_many_by('isDeleted', 0);
            $dataPlayerExpected = array(
                'email' => md5(date('Y-m-d H:i:s').rand(1,100))."test2@gmail.com",
                'password' => 123456,
                'gender' => 1,
                'firstName' => "User",
                'lastName' => "Test",
                'dob' => array('bday' => 02, 'bmonth' => 12, 'byear' => 1989)
                );
            $idExpected = $playerExpected['id'];
            $idNotExist = isset($playerExpectedNotExit->id) ? ((int)$playerExpectedNotExit->id + 1) : 1;
            $emailExit = isset($playerExpectedOne['accountEmail']) ? ($playerExpectedOne['accountEmail']) : '';
            // To verify udpate player return is invalid
            $idInvalid = array(NULL, '', 0, 'abc', -123);

            // To verify id is invalid
            foreach ($idInvalid as $key => $value) {

                $testResult = $this->player->edit($value, $dataPlayerExpected);

                if ( is_array($testResult) && isset($testResult['errors']) ) {
                    if ($key == 5) {
                        // Verify Player Not Found
                        $this->unit->run($testResult['errors'], "Player Not Found", "To verify udpate player return is invalid", "Verify Player Not Found");
                    } else {

                        // Verify Id must is a numeric and greater than zero
                        $this->unit->run($testResult['errors'], "Id must be a numeric and greater than zero", "To verify udpate player return is invalid", "Verify Id must is a numeric and greater than zero");
                    }
                }
            }

            /*TODO YOU CAN DELETE EMAIL VALID*/
            
            // To verify email is invalid
            $emailInvalid = array(NULL, '', 'abc', $emailExit);
            
            foreach ($emailInvalid as $key => $value) {
                $this->form_validation->reset_validation();

                $dataEmail['email'] = $value; 
                $testResultSeconds = $this->player->edit($idExpected, $dataEmail);

                if (is_array($testResultSeconds) && isset($testResultSeconds['errors'])) {
                    if ($value == 'abc') {

                         // The email field must contain a valid email address.
                        $this->unit->run(isset($testResultSeconds['errors']), true, "To verify udpate player return is invalid", "Verify The email field must contain a valid email address.");
                    } else {

                         // Verify email invalid
                        $this->unit->run(isset($testResultSeconds['errors']), true, "To verify udpate player return is invalid", "Verify email invalid");
                    }
                }
            }

            // To verify data player return is empty 
            foreach ($dataPlayerExpected as $key => $value) {
                // $dataTest = $dataPlayerExpected;
                $dataTest = array();
                $dataTest[$key] = '';
                $testResultThirds = $this->player->edit($idExpected, $dataTest);
                if (is_array($testResultThirds) && isset($testResultThirds['errors'])) {

                    if ( $key == 'password' ) {
                        $this->unit->run($testResultThirds['errors'][0], "The Password field is required.", "To verify update player is invalid", "To verify data player return is empty ");
                    }

                    if ( $key == 'gender' ) {
                       $this->unit->run($testResultThirds['errors'][0], "The Gender field is required.", "To verify update player is invalid", "To verify data player return is empty "); 
                    }

                    if ( $key == 'firstName' ) {
                        $this->unit->run($testResultThirds['errors'][0], "The First Name field is required.", "To verify update player is invalid", "To verify data player return is empty ");
                    }

                    if ( $key == 'lastName' ) {
                        $this->unit->run($testResultThirds['errors'][0], "The Last Name field is required.", "To verify update player is invalid", "To verify data player return is empty ");
                    }

                    if ( $key == 'dob' ) {
                        $this->unit->run($testResultThirds['errors'][0], "The Date of birth field is required.", "To verify update player is invalid", "To verify data player return is empty ");
                    }
                }               
            }
             
            if(is_array($login) && isset($login['token'])) {

                $playerProfile = $this->player->get_by(array('isDeleted'=> 0, 'profileComplete' => 0));
                if (is_object($playerProfile) && isset($playerProfile->id)) {

                    $data = array(
                      'homePhone' =>'5555555555',
                      'password' => "123456new",
                      'gender' => '1',
                      'firstName' => "User",
                      'lastName' => "Test Update",
                      'dob' => array('bday' => 12, 'bmonth' => 12, 'byear' => 1988),
                    );

                    foreach ($data as $key => $value) {

                        $dataTestOne = array();
                        $dataTestOne[$key] = $value;
                        
                        $testResultFourth = $this->player->edit($playerProfile->id, $dataTestOne);
                        if(is_object($testResultFourth) && isset($testResultFourth->id)) {

                            if( $key == 'homePhone') {
                                // verify homephone update must be equal home phone input previous
                                $this->unit->run($testResultFourth->accountData['homePhone'], $value, "To verify update player is valid", "verify homephone update must be equal home phone input previous");
                            }
                            elseif ( $key == 'gender') {

                                // Verify gender return must be equal gender input
                                $this->unit->run($testResultFourth->gender, "Male", "To verify update player is valid", "Verify $key return must be equal $key input");
                            }
                            elseif ( $key == 'dob') {

                                $dayOfBirth = $data['dob']['bmonth'] . '/' . $data['dob']['bday'] . '/' . $data['dob']['byear'];
                                $testDob = $testResultFourth->accountData;
                                
                                // Verify dob return must be equal dob input
                                $this->unit->run($testDob['dob'], $dayOfBirth , "To verify update player is valid", "Verify $key return must be equal $key input");
                            }
                            elseif( $key == 'firstName') {
                                
                                $testfirstName = $testResultFourth->accountData;

                                // Verify firstname return must be equal firstname input
                                $this->unit->run($testfirstName['firstName'], $value , "To verify update player is valid", "Verify $key return must be equal $key input");
                            }
                            elseif( $key == 'lastName') {
                                // Verify screenName return must be equal creatName input
                                $this->unit->run($testResultFourth->screenName, ($data['firstName'].' '.strtoupper( substr( $data['lastName'], 0, 1 ) )), "To verify update player is valid", "Verify $key return must be equal $key input");
                                $testlastName = $testResultFourth->accountData;

                                // Verify lastname return must be equal lastname input
                                $this->unit->run($testlastName['lastName'], $value , "To verify update player is valid", "Verify $key return must be equal $key input");
                            }
                        }
                    }
                    // To verify update player return is valid
                    $data = array(
                      'password' => "123456new",
                      'gender' => '1',
                      'firstName' => "User Update",
                      'lastName' => "Test Update",
                      'address' => "604 NUI THANH, DA NANG",
                      'state' => 'AB',
                      'zip'=> 123,
                      'honorific' => "miss",
                      'city' => "DaNang",
                      'homePhone' => "1200360000",
                      'mobilePhone' =>"9999999999",
                      'address2' => "193 NGUYEN LUON BANG",
                      'dob' => array('bday' => 12, 'bmonth' => 12, 'byear' => 1988)
                    );

                    $testResultFifth = $this->player->edit($idExpected, $data);
                    if(is_object($testResultFifth) && isset($testResultFifth)) {

                        // The profileComplete flag is set when the user updates the player account data with all information populated
                        $this->unit->run((int)$testResultFifth->profileComplete, 1, "Verify update player is valid", "The profileComplete flag is set when the user updates the player account data with all information populated");
                    }
                }
            }
            
            echo $this->unit->report();
            echo $this->returnResult($this->unit->result());
        } else {

            echo "<h4 style='color:red;'>Please run test create new Player. Because player empty on database</h4>";
        }


    }

    public function testDeletePlayer() {

        // To verify update player return is valid
        $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);

        $login = $this->player->login($dataLogin);

        if ( is_array($login) && isset($login['token'] ) ) {

            $this->player->setToken( $login['token'] );

            $player = $this->player->as_array()->get_by( 'isDeleted', 0 );
            // In case value return invalid
            //===============================
            // In case id return is empty/ null/ undefine/ string
            $idEmpty = '';
            $test_testResult = $this->player->destroy($idEmpty, $login['token']);

            // Id must be is request
            $this->unit->run($test_testResult['errors'], "Id must be a numeric and greater than zero", "In case id is empty" ,"Id must be is request");

            // In case id string
            $id_invalid = "abc";
            $testResultSeconds = $this->player->destroy($id_invalid, $login['token']);

            //Couldn't update player
            $this->unit->run($testResultSeconds['errors'] ,"Id must is a numeric and greater than zero", "Id must is a numeric and greater than zero" ,"Id return is null");

            // In case value return valid
            if(is_array($player) && isset($player['id'])) {
              $testResultThirds = $this->player->destroy($player['id'], $login['token']);
              $playersExpected = (array)$this->player->with_deleted()->get($player['id']);

              //All info of player update must be equal all info on database
              
              $this->unit->run($testResultThirds[0], 'is_null' , "In case is valid", "All info of player update must be equal all info on database");

              // Is delete return must be is value = 1
              $this->unit->run((int)$playersExpected['isDeleted'], 1, "In case is valid" , "Is delete return must be is value = 1");
            } else {
                echo "<h4 style='color:red;'>Please run test create new Player. Because player empty on database or player has been deleted.</h4>";
            }
        } else {
                echo "<h4 style='color:red;'>Please run test create new Player. Because player empty on database or player has been deleted.</h4>";
        }
        
        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());
    }

    /**
     * testIgnoreMail 
     * 
     * Functiom testing verify email ignored verify when create new user
     */
    public function testIgnoreMail() {
        
        // To verify update player return is valid
        $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);

        $login = $this->player->login($dataLogin);
        $this->player->setToken( $login['token'] );

        $data = $this->getPlayer(0, null);
        $dataExpected = $this->player->order_by('id', 'DESC')->get_by(array('isDeleted'=> 0, 'emailVerified'=> 0));

        if (is_object($dataExpected) && isset($dataExpected->emailCode)) {
            $testResult = $this->player->ignoreVerified($dataExpected->emailCode);
            $test = $this->player->with_deleted()->get($dataExpected->id);
            // To verify return Player is ignore Verified successfully
            $this->unit->run($testResult[0], 'is_null' , "To verify return Player when ignored verify email", "To verify return Player is ignore Verified successfully");

            $this->unit->run((int)$test->isDeleted, 1, "To verify return emailVerified is 1", "To verify return Player is Verified successfully");

            $emailCodeInvalid = array(null,'', 'abc');
            foreach ($emailCodeInvalid as $key => $value) {
                $testResultInvalid = $this->player->ignoreVerified($value);

                 // To verify email verified is invalid
                $this->unit->run($testResultInvalid['errors'], "Player Not Found", "To verify email verified is invalid", "Verify not success");
            }
               
        } else {
            echo "<h4 style='color:red;'>Please run test create new Player. Because player empty on database or player has been deleted.</h4>";
        }
        
        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());

    }


    /**
     * testVerifiedMail 
     * 
     * Functiom testing verify email when create new user
     */
    public function testVerifiedMail() {
        

        $dataExpected = $this->player->order_by('id', 'DESC')->get_by('isDeleted', 0);

        if (is_object($dataExpected) && isset($dataExpected->emailCode)) {

            $testResult = $this->player->emailVerified($dataExpected->emailCode);
            $test = $this->player->getById($dataExpected->id, TRUE);
            // To verify return Player is Verified successfully
            $this->unit->run($testResult[0], "Verified successfully", "To verify return Player is Verified successfully", "Verified successfully");

            $this->unit->run((int)$test->emailVerified, 1, "To verify return emailVerified is 1", "To verify return Player is Verified successfully");

            // To verify email verified is invalid
            $emailCodeInvalid = array(null,'', 'abc');
            foreach ($emailCodeInvalid as $key => $value) {
                $testResultInvalid = $this->player->emailVerified($value);

                $this->unit->run($testResultInvalid['errors'], "Player Not Found", "To verify email verified is invalid", "Verify not success");
            }

               
        } else {
            echo "<h4 style='color:red;'>Please run test create new Player. Because player empty on database or player has been deleted.</h4>";
        }
        
        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());

    }

    protected function getPlayer($emailConfirmed, $FBID) {

        if ($emailConfirmed === 0) {
            $data = array(
                'email' => substr( md5( uniqid( date( 'Y-m-d H:i:s' ), true ) ), 0, 30 )."login0@gmail.com",
                'password' => "123456",
                'gender' => '1',
                'firstName' => "User",
                'lastName' => "Test",
                'dob' => array('bday' => 02, 'bmonth' => 12, 'byear' => 1989),
                'phoneHome' => '0123456789',
                );
            $player = $this->player->add($data);
            // var_dump($player); die;
            if (is_object($player) && isset($player->id)) {
                return $player;
            } else {

                return FALSE;
            }
        } else {
            
            $data = array(
                'email' => substr( md5( uniqid( date( 'Y-m-d H:i:s' ), true ) ), 0, 30 )."login1@gmail.com",
                'password' => "123456",
                'gender' => 1,
                'firstName' => "User",
                'lastName' => "Test",
                'dob' => array('bday' => 02, 'bmonth' => 12, 'byear' => 1989),
                'phoneHome' => '0123456789',
                'fbId' => is_null($FBID) ? $FBID : md5( $FBID )
                );
            $player = $this->player->add($data);

            if ( is_object($player) && isset($player->id) ) {
                $confirmed = $this->player->emailVerified($player->emailCode);
                if (is_array($confirmed) && isset($confirmed['statusCode']) && $confirmed['statusCode'] === 200) {
                    $playerConfirmed = $this->player->getById($player->id, TRUE);
                    return $playerConfirmed;
                }
            
            } else {

                return FALSE;
            }

        }

    }

    /**
     * createTokenLogin 
     * 
     * Testing token return when user login
     */
    public function createTokenLogin() {

        // To verify login of player invalid
        // ==================================
        $emailInvalid = array('', null, 'abc',  "notexit@gmail.com");
        $password = array('', null, 'abc' ,'invalid');
        foreach ($emailInvalid as $key => $value) {
            $dataInvalid = array('email' => $emailInvalid[$key], 'password' => $password[$key], 'deviceId' => 1 );    
            $testResultOne = $this->player->login($dataInvalid);
            if (is_array($testResultOne) && isset($testResultOne['errors'])) {
                if( empty($value)) {

                    // To verify email or password invalid
                    $this->unit->run($testResultOne['errors'][0], "The email field is required.", "To verify login of player is invalid", "Verify password or email invalid");
                } elseif($value == 'abc') {

                    // The email field must contain a valid email address.
                    $this->unit->run($testResultOne['errors'][0], "The email field must contain a valid email address.", "To verify login of player is invalid", "Verify password or email invalid");
                }   
            } else {
                    // To verify email or password invalid
                    $this->unit->run($testResultOne['error'], "Invalid email or password", "To verify login of player is invalid", "Verify password or email invalid");
            } 
        }

        $player = $this->getPlayer(0, null);
        // To verify emailConfirmed return is invalid
        if ( $player ) {
            $emailConfirmed = $player->accountEmail;
            $password = 123456;
            $data = array( 'email' => $emailConfirmed, 'password' => $password, 'deviceId'=> 1);
            $testResultSecond = $this->player->login($data);
            if (is_array($testResultSecond) && isset($testResultSecond['error'])) {
                
                // verify Registration Incomplete
                $this->unit->run($testResultSecond['error'], "Email has not been confirmed", "To verify emailConfirmed return is invalid", "Verify Registration Incomplete");    
            }

        } else {

            echo "<h4 style='color:red;'>Please run test create new Player. Because player empty on database or player has been deleted.</h4>";
        }

        // To verify login of player is valid
        $playerValid = $this->getPlayer(1,NULL);
        if ($playerValid) {

            $email = $playerValid->accountEmail;
            $password = 123456;
            
            // To verify device Id must be is numberic and greater than 0
            $deviceInvalid = array('', null, 'abc', -123, 0);

            $data = array('email' => $email, 'password' => $password, 'deviceId' => 1);
            foreach ($deviceInvalid as $key => $value) {
                $dataDevicedInvalid = $data;
                $dataDevicedInvalid['deviceId'] = $value;
                
                $testResultDevice = $this->player->login($dataDevicedInvalid);
                if ( is_array($testResultDevice) && isset($testResultDevice['errors'])) {
                    if (empty($value)) {
                        $this->unit->run($testResultDevice['errors'][0], "The Device Id field is required.", "Verify players login is valid", "Verify deviceId invalid");
                    } elseif ($value == 'abc') {
                        // To verify device is invalid
                        $this->unit->run( $testResultDevice['errors'][0],"The Device Id field must contain only numbers.", "To verify login is invalid", "To verify device is invalid" ); 
                    } else {
                        
                        // To verify device is invalid
                        $this->unit->run( $testResultDevice['errors'][0], "The Device Id field must contain a number greater than 0.", "To verify login is invalid", "To verify device is invalid" ); 
                        
                    }
                }
            }

            $optionContinueInvalid = array('abc', -123, 2);
            foreach ($optionContinueInvalid as $key => $value) {
                $dataContinueInvalid = $data;
                $dataContinueInvalid['isContinue'] = $value;
                $testContinue = $this->player->login($dataContinueInvalid);

                if ( is_array($testContinue) && isset($testContinue['errors'])) {
                    
                    // To verify isContinue is invalid
                    $this->unit->run($testContinue['errors'][0], "The isContinue field is not in the correct format.", "To verify login is invalid", "To verify isContinue is invalid");
                }
            }

            $data['deviceId'] = 1;
            $datatokenHash = array(md5($email), md5(123456), md5($data['deviceId']));
            $tokenHash = $this->player->base64Encode($datatokenHash);
            $testResultThirds = $this->player->login($data);
            if(is_array($testResultThirds) && isset($testResultThirds['token'])) {

                // Token hash return must be equal token hash from email nad password input
                $this->unit->run($testResultThirds['token'], $tokenHash, "To verify login of player is valid", "Token hash return must be equal token hash from email nad password input");
            }

            $testResultFourth = $this->player->login($data);

            if(is_array($testResultFourth) && isset($testResultFourth['token'])) {
                // Token hash return must be equal token hash from email nad password input
                $this->unit->run($testResultThirds['token'], $testResultFourth['token'], "To verify login of player is valid", "Token hash return must be equal token hash from email nad password input");
            }

            $data['deviceId'] = 2;
            $dataHash = array(md5($email), md5($password), md5($data['deviceId']));

            $tokenHash = $this->player->base64Encode($dataHash);
            $testResultFifth = $this->player->login($data);

            if (is_array($testResultFifth) && isset($testResultFifth['message'])) {
                // To verify player login in other device
                $this->unit->run($testResultFifth['message'], "You are logging in on other device, do you want to continues this login?", "To verify login of player is valid", "Token hash return must be equal token hash from email nad password input");
            }

            $data['isContinue'] = TRUE;

            $testResultSixth = $this->player->login($data);

            if( is_array($testResultSixth) && isset($testResultSixth['token'])) {

                // verify player login success when accept continue login
                $this->unit->run( $testResultSixth['token'], $tokenHash, "verify player login success when accept continue login", "To verified login is valid when isContinue is TRUE");
                $testResultInvalidLogout = $this->player->logout();
                if(is_array($testResultInvalidLogout) && isset($testResultInvalidLogout['message']) && isset($testResultInvalidLogout['statusCode']) && $testResultInvalidLogout['statusCode'] == 400) {
                    
                    //To verify logout successful 
                    $this->unit->run($testResultInvalidLogout['message'], "Can't logout", "To verify logout successfully", "To verify logout successful");
                    
                } 

                $token = $this->player->setToken($testResultSixth['token']);
                $testResultLogout = $this->player->logout();



                if(is_array($testResultLogout) && isset($testResultLogout['message']) && isset($testResultLogout['statusCode']) && $testResultLogout['statusCode'] == 200) {
                    
                    //To verify logout successful 
                    $this->unit->run($testResultLogout['message'], "Logout successfully", "To verify logout successfully", "To verify logout successful");
                    
                }
                $data['deviceId'] = 3;
                $data['isContinue'] = FALSE;
                
                $testLoginAgain = $this->player->login($data);

                if ( is_array($testLoginAgain) && isset($testLoginAgain) ) {

                    // verify player login success when accept continue login
                    $this->unit->run( $testResultSixth['token'], $tokenHash, "verify player login success when accept continue login", "To verified login is valid when isContinue is TRUE");
                } else {

                    echo "Can't login again. Pls check here";
                }

            }

        } else {

            echo "<h4 style='color:red;'>Please run test create new Player. Because player empty on database or player has been deleted.</h4>";
        }

        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());
    }

    /**
     * testLoginFacebook 
     * 
     *  Testing login with facabook account 
     */
    
    public function testLoginFacebook () {
        $this->load->model( 'masteremail' );
        $this->load->model( 'masteraccount' );
        $query = $this->db->query('SET FOREIGN_KEY_CHECKS=0;');
        $query = $this->db->query('TRUNCATE Players;');
        $query = $this->db->query('TRUNCATE Positions;');
        $query = $this->db->query('TRUNCATE MasterEmail;');
        $query = $this->db->query('TRUNCATE MasterAccount;');
        $query = $this->db->query('SET FOREIGN_KEY_CHECKS=1;');

        // To verify login by Facebook is invalid
        // ========================================
        // To verify email input is invalid
        $FBIDExist = 123456789;
        $rand = rand(1,10);
        $FBID = '12333333'. "$rand";
        $FBIDMatch = '123456789'. "$rand";
        $data = array('email' => substr( md5( uniqid( date( 'Y-m-d H:i:s' ), true ) ), 0, 30 )."testingfacebook@gmail.com", 'FBID' => (int)$FBID , 'deviceId'=> 1, 'gender' => 1, 'birthDay' => 02, 'birthMonth' => 12, 'birthYear' => 1989, 'screenName' => 'Nhan Doan', 'firstName' => 'Nhan', 'lastName' => 'Nhan' );
        $emailInvalid = array(NULL, '', -123, 'abc', 0);

        foreach ($emailInvalid as $key => $value) {
            $dataEmailInvalid = $data;
            $dataEmailInvalid['email'] = $value;
            $testEmailResultInvalid = $this->player->loginFacebook($dataEmailInvalid);
            
            if(is_array($testEmailResultInvalid) && isset($testEmailResultInvalid['errors'])) {

                // To verify email is invalid must be required
               if ( empty($value) ) {
                    
                    $this->unit->run($testEmailResultInvalid['errors'][0], "The email field is required.", "To verify login Facebook is invalid", "To verify email is invalid must be required");
               } else {

                // To verify the email field must contain a valid email address.
                $this->unit->run($testEmailResultInvalid['errors'][0], "The email field must contain a valid email address.", "To verify login Facebook is invalid", "To verify the email field must contain a valid email address.");
               }
            }
        }

       // To verify FBID input is invalid 
       $FBIDInvalid = array('', null, 0, -123, 'abc');
       foreach ($FBIDInvalid as $key => $value) {
           
           $dataFBIDInvalid = $data;
           $dataFBIDInvalid['FBID'] = $value;
           $testFBIDInvalid = $this->player->loginFacebook($dataFBIDInvalid);

           if ( is_array($testFBIDInvalid) && isset($testFBIDInvalid['errors'])) {
                if (empty($value) || $value === 0) {

                    // To verify The FBID field is required.
                    $this->unit->run($testFBIDInvalid['errors'][0], "The FBID field is required.", "To verify login Facebook is invalid", "To verify The FBID field is required.");
                } else {

                    // To verify The FBID field must contain a number greater than 0.
                    $this->unit->run($testFBIDInvalid['errors'][0], "The FBID field must contain a number greater than 0.", "To verify login Facebook is invalid", "To verify The FBID field must contain a number greater than 0.");
                }
           }
       }

       // To verify Device Id input invalid
       $deviceId = array('', null, 'abc', -123, 0);
       foreach ($deviceId as $key => $value) {
            
           $dataDeviceIdInvalid = $data;
           $dataDeviceIdInvalid['deviceId'] = $value;
           $testDeviceIdInvalid = $this->player->loginFacebook($dataDeviceIdInvalid);
           
           if(is_array($testDeviceIdInvalid) && isset($testDeviceIdInvalid['errors'])) {
                if (empty($value)) {

                    // To verify The DeviceId field is required.
                    $this->unit->run($testDeviceIdInvalid['errors'][0], "The Device Id field is required.", "To verify login Facebook is invalid", "To verify The DeviceId field is required.");

                } elseif($value == 'abc') {
                    
                    $this->unit->run($testDeviceIdInvalid['errors'][0], "The Device Id field must contain a number greater than 0.", "To verify login Facebook is invalid", "To verify The Device Id field must contain only numbers.");
                } else {

                    // To verify The DeviceId field must contain a number greater than 0.
                    $this->unit->run($testDeviceIdInvalid['errors'][0], "The Device Id field must contain a number greater than 0.", "To verify login Facebook is invalid", "To verify The DeviceId field must contain a number greater than 0.");

                }
           }
       }

        // To verify login by Facebook is valid
        // ======================================
        // To verify register return is valid with email match any account
        $emailMatch = $this->getPlayer(1, null);
        if( is_object($emailMatch) && isset($emailMatch->accountEmail) ) {

            $dataEmailMatch = $data;
            $dataEmailMatch['email'] = $emailMatch->accountEmail;
            $testResultSecond = $this->player->loginFacebook($dataEmailMatch);
            $dataExpected = $this->player->get_by('id', $emailMatch->id);

            if(is_array($testResultSecond) && isset($testResultSecond['message'])) {

                // verify FBID had add to account with email address
                $this->unit->run($testResultSecond['message'], "Added {$dataEmailMatch['FBID']} to account with {$emailMatch->accountEmail} address", "To verify register by account Facebook successfully", "verify FBID had add to account with email address");
                if (is_object($dataExpected) && isset($dataExpected->id) ) {

                    // verify FBID return must be equal FBID from input previous
                    $this->unit->run(md5($data['FBID']), $dataExpected->fbId, "To verify register by account Facebook successfully", "verify FBID return must be equal FBID from input previous");

                    // verify email return must be qual email input previous                
                    $this->unit->run($emailMatch->accountEmail, $dataExpected->accountEmail, "To verify register by account Facebook successfully", "verify email return must be qual email input previous");
                }
            }
        }    

        // To verify register return is valid with FBID , email match any FBID, email account
        $dataGetMatchEmailFBID = $this->getPlayer(1, (int)$FBIDMatch);
        if ( is_object($dataGetMatchEmailFBID) && isset($dataGetMatchEmailFBID->id) ) {
            $dataMatchEmailFBID = $data;
            $dataMatchEmailFBID['email'] = $dataGetMatchEmailFBID->accountEmail;
            $dataMatchEmailFBID['FBID'] = (int)$FBIDMatch;
            $dataMatchEmailFBID['deviceId'] = 1;
            $email = $dataGetMatchEmailFBID->accountEmail;

            $deviceId = $data['deviceId'];
            $tokenFrist = array(md5($email), md5(NULL) , md5($deviceId));
            $tokenHash = $this->player->base64Encode($tokenFrist);
            $testResultFifth = $this->player->loginFacebook($dataMatchEmailFBID);
            if(is_array($testResultFifth) && isset($testResultFifth['token'])) {
                // print_r($testResultFifth['token']); 
                // Token return when match FBID and email account from kizzang
                $this->unit->run($testResultFifth['token'], $tokenHash, "To verify login by Facebook is valid", "To verify register return is valid with FBID , email match any FBID, email account");
            }

            $dataMatchEmailFBID['deviceId'] = 2;
            $testResultOtherDeviceId = $this->player->loginFacebook($dataMatchEmailFBID);

            if (is_array($testResultOtherDeviceId) && isset($testResultOtherDeviceId['message'])) {

                // To verify login fb is valid when player just login with other device
                $this->unit->run($testResultOtherDeviceId['message'], "You are logging in on other device, do you want to continues this login?", "To verify login is valid on FB", "To verify login fb is valid when player just login with other device");
            }

        }

        // To verify register return is valid with, FBID match any FBID account, email doesn't match account, password exist for this account
        $onlyMacthFBID = 10000000;
        $playersMachFBID = $this->getPlayer(1, $onlyMacthFBID);
        if ( is_object($playersMachFBID) && isset($playersMachFBID->id)) {
            $emailHashOld = md5($playersMachFBID->accountEmail);
            $passwordHash = md5(NULL);
            $dataTestMatchFBID = $data;
            $dataTestMatchFBID['email'] = "testingfacebook@gmail.com";
            $dataTestMatchFBID['FBID'] = $onlyMacthFBID;
            $dataTestMatchFBID['deviceId'] = 1;

            $emaiHashNew = md5($dataTestMatchFBID['email']);
            $testResultFourth = $this->player->loginFacebook($dataTestMatchFBID);

            if ( is_array($testResultFourth) && isset($testResultFourth['token'])) {
                $datatokenHash = array(md5($playersMachFBID->accountEmail), $passwordHash, md5($dataTestMatchFBID['deviceId']));
                $tokenHashOnlyFB = $this->player->base64Encode($datatokenHash);
                // verify token return must be equal tokehash
                $this->unit->run($testResultFourth['token'], $tokenHashOnlyFB, "To verify login facabook is valid" , "verify token return must be equal tokehash");

                $dataExpectedFBID = $this->masteremail->get_many_by('playerId', $playersMachFBID->id);
                if ( is_array($dataExpectedFBID) && (sizeof($dataFBIDInvalid) > 0 )){

                    // Verify email hash save in MasterEmail tables
                    $this->unit->run($dataExpectedFBID[0]->emailHash, $emaiHashNew);

                    $this->unit->run($dataExpectedFBID[1]->emailHash, $emailHashOld);    
                }

                $dataTestMatchFBID['deviceId'] = 2;
                $testResultOtherDeviceId = $this->player->loginFacebook($dataTestMatchFBID);

                if (is_array($testResultOtherDeviceId) && isset($testResultOtherDeviceId['message'])) {

                    // To verify login fb is valid when player just login with other device
                    $this->unit->run($testResultOtherDeviceId['message'], "You are logging in on other device, do you want to continues this login?", "To verify login is valid on FB", "To verify login fb is valid when player just login with other device");
                }

                // To verify login with other deviceId
                $dataTestMatchFBID['isContinue'] = TRUE;

                $testResultLoginContinue = $this->player->loginFacebook($dataTestMatchFBID);

                if(is_array($testResultLoginContinue) && isset($testResultLoginContinue['token'])) {

                    $tokenHashArray = array(md5($playersMachFBID->accountEmail), $passwordHash, md5($dataTestMatchFBID['deviceId']));
                    $tokenHashOnlyFB = $this->player->base64Encode($tokenHashArray);
                    // verify token return must be equal tokehash
                    $this->unit->run($testResultLoginContinue['token'], $tokenHashOnlyFB, "To verify login facabook is valid" , "verify token return must be equal tokehash");

                }
            }
        }

        // "To verify register return is valid with, FBID match any FBID account, email match any account, password exist for this account
        $onlyMacthFBIDSecond = 11111111111;
        $playerOnlyMatchFBID = $this->getPlayer(1, $onlyMacthFBIDSecond);
        $playerOnlyMatchEmail = $this->getPlayer(1, null);

        if (is_object($playerOnlyMatchFBID) && is_object($playerOnlyMatchEmail)) {

            $onlyEmail = $playerOnlyMatchEmail->accountEmail;
            $dataTestInCase = $data;
            $dataTestInCase['email'] = $onlyEmail;
            $dataTestInCase['FBID'] = $onlyMacthFBIDSecond;
            $dataTestInCase['deviceId'] = 1;
           
            $testResultSixth = $this->player->loginFacebook($dataTestInCase);
            $emailHash = md5($onlyEmail);
            $emailHashFb = md5($playerOnlyMatchFBID->accountEmail);
            if ( is_array($testResultSixth) && isset($testResultSixth['token'])) {
                $tokenArrayHashOnlyFB = array(md5($playerOnlyMatchFBID->accountEmail), md5(null), md5($dataTestInCase['deviceId']));
                $tokenHashOnlyFB = $this->player->base64Encode($tokenArrayHashOnlyFB);
                // verify token return must be equal tokehash
                $this->unit->run($testResultSixth['token'], $tokenHashOnlyFB, "To verify login facabook is valid" , "verify token return must be equal tokehash");

                $dataExpectedInCase = $this->masteraccount->get_by('playerIdFb', $playerOnlyMatchFBID->id);

                if ( is_object($dataExpectedInCase)){

                    // Verify email hash save in MasterEmail tables
                    $this->unit->run($dataExpectedInCase->emailHashFb, $emailHashFb);

                    $this->unit->run($dataExpectedInCase->emailHash, $emailHash);    
                }
            }

        }
        // "To verify register return is valid with BID match any FBID account, email doesn't match account, password not exist for this account"
        
        echo $this->unit->report();
        echo $this->returnResult($this->unit->result()); 
    
    }


    function returnResult($results) {
        $passed = [];
        $failed = [];
        foreach($this->unit->result() as $value) {
            if($value['Result'] === "Passed") {
                array_push($passed, $value['Result']);
            }

            if($value['Result'] === "Failed") {
                array_push($failed, $value['Result']);
            }
        }

        return  "<h1> Tests: ". sizeof($results). ", Passed: " .sizeof($passed). ", Failed:".sizeof($failed)."</h1>";
    }  
}