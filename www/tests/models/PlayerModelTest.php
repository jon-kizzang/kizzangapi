<?php

/**
 * @group Model
 */
class PlayerModelTest extends CIUnit_TestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->CI->load->model(array('player', 'player', 'masteremail', 'masteraccount', 'position', 'facebookinvite', 'gamecount'));
        $this->player = $this->CI->player;
        $this->masteremail = $this->CI->masteremail;
        $this->masteraccount = $this->CI->masteraccount;
        $this->facebookinvite = $this->CI->facebookinvite;
        // disable send SQS when run unit test
        $this->player->executeTesting = TRUE;
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function createWinConfirmation() {

        for ($i=0; $i < 20; $i++) {

            $data = array(
            'winConfirmed'         => 1,
            'playerId'             => rand( 1, 3),
            // 'accountData',
            'serialNumber'         => '341315135135351',
            'entry'                => 1213123,
            'prizeAmount'          => rand(3000, 4000)/100,
            'prizeName'            => 'prizeName' . rand(1,40),
            'taxableAmount'        => rand( 1, 20)/10,
            'payPalCorrelationId'  => 'payPalCorrelationId' . rand(1,40),
            'payPalPayDate'        => date('Y-m-' . rand(1,28) . ' H:i:s'),
            'playerActionChoice'   => 0,
            'winDate'              => date('Y-m-' . rand(1,28) . ' H:i:s'),
            // 'payPalEmail',
            'payPalPaymentStatus'  => 'P',
            'payPalTransactionId'  => rand(10000000000, 40000000000),
            'status'               => 'P',
            'cumulativeAnnualPaid' => rand(3000, 4000)/100,
            'organizationId'       => $i,
            );

            $id = $this->CI->winconfirmation->insert( $data, TRUE );
        }
    }
    /**
     * unit test add new player
     */
    public function testAddNewPlayer()
    {
        $emailDb    = $this->player->as_array()->get_by( 'isDeleted', 0 );
        $playerPost = array(
            'email'     => substr(str_shuffle(md5(time())), 0, 100)."test@gmail.com",
            'password'  => "123456",
            'gender'    => 1,
            'firstName' => "User",
            'lastName'  => "Test",
            'dob'       => array('bday' => 02, 'bmonth' => 12, 'byear' => 1989),
            'option3'   => TRUE,
          );

        // In case is invalid
        //=====================
        // In case data need update a record not exist field of table players on database
        $dataRecordNotExist                 = $playerPost;
        $dataRecordNotExist['email']        = md5(date('Y-m-d H:i:s').rand(1,100))."testexit@gmail.com";
        $dataRecordNotExist['DataNotExist'] = "Not exit";

        $playerTest = $this->player->add($dataRecordNotExist);

        if ( is_object($playerTest) && isset($playerTest->id) ) {

            $playersExpected = $this->player->getById( $playerTest->id, TRUE );

            // DataNotExist will be not exist on info player return
            $this->assertEquals(in_array('DataNotExist', (array)$playerTest), FALSE);

            // player must be create
            $this->assertEquals( is_object($playerTest), TRUE);

            // all info of player create must be equal player in database
            foreach ( (array)$playerTest as $key => $value ) {

                if ( array_key_exists($key, (array)$playersExpected) ) {

                    if( $key != 'statusCode' ) {

                        $this->assertEquals($playerTest->$key, $playersExpected->$key);
                    }
                }
           }
        }

        //verify email is invalid
        //In case email empty
        $playerInvalid          = $playerPost;
        $playerInvalid['email'] = NULL;
        $insertId               = $this->player->add($playerInvalid);

        //InsertId return must be is zero
        $this->assertEquals($insertId['message'][0], "The Email or phone number field is required."   );

        //In case email invalid format
        $playerInvalid['email'] = "abc";

        //Email must be is correct format
        $this->assertTrue(valid_email($playerInvalid['email']) == FALSE);
        $insertId = $this->player->add($playerInvalid);

        if ( isset($emailDb[0]) ) {

            $emailExistDb           = $emailDb[0]['AccountEmail'];

            //In case email already using
            $playerInvalid['email'] = $emailExistDb;
            $emailExit              = $this->player->add($playerInvalid);

            if ( isset($emailExit['message'][0]) ) {

                //This email address is already in use
                $this->assertEquals($emailExit['message'][0], "The email field must contain a unique value.");
            }
        }

        //In case first name empty
        $playerFirstNameInvalid              = $playerPost;
        $playerFirstNameInvalid['firstName'] = '';
        $insertId                            = $this->player->add($playerFirstNameInvalid);

        if ( isset($insertId['message'][0]) ) {
            //firstName is required
            $this->assertEquals($insertId['message'][0], "The First Name field is required.");
        }

        //In case last name empty
        $playerLastNameInvalid             = $playerPost;
        $playerLastNameInvalid['lastName'] = '';
        $insertId                          = $this->player->add($playerLastNameInvalid);

        if ( isset($insertId['message'][0]) ) {

            //LastName is required
            $this->assertEquals($insertId['message'][0], "The Last Name field is required.");

        }

        // Verify gender is invalid
        $genderInvalid           = $playerPost;
        $genderInvalid['gender'] = 'adaa';
        $genderTestInvalid       = $this->player->add($genderInvalid);
        if ( isset($genderTestInvalid['message'][0]) ) {

            //The Gender field is not in the correct format
            $this->assertEquals($genderTestInvalid['message'][0],'The Gender field is not in the correct format.');
        }

        // Verify Gender field is invalid when empty
        $genderInvalid['gender'] = '';
        $genderTestEmpty         = $this->player->add($genderInvalid);

        if ( isset($genderTestEmpty['message'][0]) ) {

            //The Gender field is required.
            $this->assertEquals($genderTestEmpty['message'][0], "The Gender field is required.");
        }

        //In case password empty
        $playerPwInvalid             = $playerPost;
        $playerPwInvalid['password'] = '';
        $insertId                    = $this->player->add($playerPwInvalid);

        if ( isset($insertId['message'][0]) ) {

            //password is required
            $this->assertEquals($insertId['message'][0], "The Password field is required.");
        }

        // To verify birth of day is invalid
        $dataDob        = $playerPost;
        $dataDob['dob'] = array('bday' => 02, 'bmonth' => 12, 'byear' => 1999);
        $testDobInvalid = $this->player->add($dataDob);

        if( is_array($testDobInvalid) && isset($testDobInvalid['message']) ) {

            // verify date of birth is'nt correct format
            $this->assertEquals($testDobInvalid['message'][0], "Birthdate is not in the correct format");
        }


        // To verify birth of day is invalid
        $dataDob        = $playerPost;
        $dataDob['dob'] = array('bday' => 02, 'bmonth' => 12, 'byear' => 1900);

        $testDobInvalid = $this->player->add($dataDob);
        if( is_array($testDobInvalid) && isset($testDobInvalid['message']) ) {

            // verify date of birth is'nt correct format
            $this->assertEquals($testDobInvalid['message'][0], "Birthdate is not in the correct format");
        }

        // To verify state is invalid
        $stateInvalid = array('BCD', 12, -12, 0);

        foreach ( $stateInvalid as $value ) {

            $playerStateInvalid          = $playerPost;
            $playerStateInvalid['state'] = $value;
            $testStateInvalid            = $this->player->add($playerStateInvalid);

            if( is_array($testStateInvalid) && isset($testStateInvalid['message']) ) {

                if ($value == 'BCD' || $value == 0) {

                    // verify state  exceed 2 characters in length.
                    $this->assertEquals( $testStateInvalid['message'][0], "The State field can not exceed 2 characters in length." );
                } else {

                    // verify State field may only contain alphabetical characters.
                    $this->assertEquals( $testStateInvalid['message'][0], "The State field may only contain alphabetical characters." );
                }
            }
        }

        // To verify zip is invalid
        $playerPost['email']     = substr(str_shuffle(md5(time())), 0, 100)."testZipString@gmail.com";
        $playerZipInvalid        = $playerPost;
        $playerZipInvalid['zip'] = 'abc';
        $testZipInvalid          = $this->player->add($playerZipInvalid);

        if( is_array($testZipInvalid) && isset($testZipInvalid['message']) ) {

                // verify state  exceed 2 characters in length.
                $this->assertEquals($testZipInvalid['message'][0], "The Zip field must contain only numbers.");
        }

        // To verify The Zip field can not exceed 8 characters in length.
        $playerPost['email']     = substr(str_shuffle(md5(time())), 0, 100)."testZipInvalid@gmail.com";
        $playerZipInvalid        = $playerPost;
        $playerZipInvalid['zip'] = 1111111111111;
        $testZipInvalid          = $this->player->add($playerZipInvalid);

        if( is_array($testZipInvalid) && isset($testZipInvalid['message']) ) {

            // verify zip field may only contain alphabetical characters.
            $this->assertEquals($testZipInvalid['message'][0], "The Zip field can not exceed 8 characters in length.");
        }

        // To verify the City field is not in the correct format.
        $playerPost['email']       = substr(str_shuffle(md5(time())), 0, 100)."testCityInvalid@gmail.com";
        $playerCityInvalid         = $playerPost;
        $playerCityInvalid['city'] = 1111111111111;
        $testCityInvalid           = $this->player->add($playerCityInvalid);

        if( is_array($testCityInvalid) && isset($testCityInvalid['message']) ) {

            // verify City field may only contain alphabetical characters.
            $this->assertEquals($testCityInvalid['message'][0], "The City field is not in the correct format.");
        }

        // To verify The City field can not exceed 40 characters in length.
        $playerPost['email']          = substr(str_shuffle(md5(time())), 0, 100)."testCityInvalid@gmail.com";
        $playerCityMaxInvalid         = $playerPost;
        $playerCityMaxInvalid['city'] = str_repeat("ab", 41);
        $testCityMaxInvalid           = $this->player->add($playerCityMaxInvalid);
        if( is_array($testCityMaxInvalid) && isset($testCityMaxInvalid['message']) ) {

            // verify The City field can not exceed 40 characters in length.
            $this->assertEquals($testCityMaxInvalid['message'][0], "The City field can not exceed 40 characters in length.");
        }

        // To verify the Home Phone is not in the correct format ((555)-555-5555 or (555)555-5555 or 5555555555) )
        $playerPost['email']                 = substr(str_shuffle(md5(time())), 0, 100)."testhomePhoneInvalid@gmail.com";
        $playerhomePhoneInvalid              = $playerPost;
        $playerhomePhoneInvalid['homePhone'] = 'bbc';
        $testhomePhoneInvalid                = $this->player->add($playerhomePhoneInvalid);

        if( is_array($testhomePhoneInvalid) && isset($testhomePhoneInvalid['message']) ) {

            // verify the Home Phone is not in the correct format ((555)-555-5555 or (555)555-5555 or 5555555555) )
            $this->assertEquals($testhomePhoneInvalid['message'][0], "The Home Phone is not in the correct format ((555)-555-5555 or (555)555-5555 or 5555555555)");
        }

        // To verify The Address field can not exceed 40 characters in length.
        $playerPost['email']                = substr(str_shuffle(md5(time())), 0, 100)."testAddressInvalid@gmail.com";
        $playerAddressMaxInvalid            = $playerPost;
        $playerAddressMaxInvalid['address'] =  str_repeat("AB", 202);
        $testAddressMaxInvalid              = $this->player->add($playerAddressMaxInvalid);

        if( is_array($testAddressMaxInvalid) && isset($testAddressMaxInvalid['message']) ) {

            // verify The Address field can not exceed 40 characters in length.
            $this->assertEquals($testAddressMaxInvalid['message'][0], "Address must be less than 200 characters");
        }

        // In case is valid
        // ====================
         $playerPostSecond = array(
            'email'     => md5(date('Y-m-d H:i:s').rand(1,100))."test2@gmail.com",
            'password'  => "123456",
            'gender'    => 1,
            'firstName' => "User",
            'lastName'  => "Test",
            'dob'       => array('bday' => 02, 'bmonth' => 12, 'byear' => 1989)
        );

        $idInvalid = $this->player->add($playerPostSecond);

        if ( is_object($idInvalid) && isset($idInvalid->id) ) {

            $player = $this->player->get($idInvalid->id);

            //ID insert of player when create must be equal ID player when get on database
            $this->assertEquals($idInvalid->id, $player->id);

            //email when get on database must be equal email when creating
            $this->assertEquals($playerPostSecond['email'], $player->accountEmail);

            //email hash when get on database must be equal email hash when creating
            $this->assertEquals(md5($playerPostSecond['email']), $player->emailHash);

            //Password hash when get on database must be equal password hash when creating
            $this->assertEquals(md5($playerPostSecond['password']), $player->passwordHash);

            //ScreenName when get on database must be equal ScreenName when creating
            $this->assertEquals(($playerPostSecond['firstName'].' '.strtoupper( substr($playerPostSecond['lastName'] , 0, 1 ) )), $player->screenName);


            // Verify firstName return must be equal firstname input
            $this->assertEquals($player->accountData['firstName'], $playerPostSecond['firstName']);

            // Verify lastname return must be equal lastName input
            $this->assertEquals($player->accountData['lastName'], $playerPostSecond['lastName']);

            // Verify dob return must be equal dob input
            $dayOfBirth = $playerPostSecond['dob']['bmonth'] . '/' . $playerPostSecond['dob']['bday'] . '/' . $playerPostSecond['dob']['byear'];

            $this->assertEquals($player->accountData['dob'], $dayOfBirth);

            // Verify that the passwordHash is ‘Not NULL’
            $this->assertEquals(is_string($player->passwordHash), TRUE);

            // Verify that the registeredWithFB is a TINYINT(1)
            $this->assertEquals((int)$player->registeredWithFB, 0);

            // The profileComplete flag should be false (0) when a player account is created
            $this->assertEquals((int)$player->profileComplete, 0);
        }

        // To verify add player return is valid
        $data = array(
          'email'       => md5(date('Y-m-d H:i:s').rand(1,100))."test3@gmail.com",
          'password'    => "123456new",
          'gender'      => '1',
          'firstName'   => "User Update",
          'lastName'    => "Test Update",
          'address'     => "604 NUI THANH, DA NANG",
          'state'       => 'AB',
          'zip'         => 123,
          'honorific'   => "miss",
          'city'        => "DaNang",
          'homePhone'   => "1200360000",
          'mobilePhone' =>"9999999999",
          'address2'    => "193 NGUYEN LUON BANG",
          'countryCode' => "AF",
          'dob'         => array('bday' => 12, 'bmonth' => 12, 'byear' => 1989)
        );

        // The profileComplete flag is set when the user updates the player account data with all information populated
        $testResultFifth = $this->player->add($data);

        if ( is_object($testResultFifth) && isset($testResultFifth->id) ) {

            $this->assertEquals((int)$testResultFifth->profileComplete, 1);
        }

    }

    function testEditPlayer() {

        // To verify update player return is valid
        $dataLogin = array(
            'email'    => "admin@kizzang.com",
            'password' => '123456789',
            'deviceId' => 1);

        $login = $this->player->login($dataLogin);

        $this->player->setToken( $login['token'] ) ;

        $count = $this->player->count_by('isDeleted', 0);

        if ($count > 0) {
            $playerExpectedNotExit = $this->player->limit(1)->order_by('id', 'DESC')->get_all();
            $playerExpected = $this->player->as_array()->get_by( 'isDeleted', 0 );
            $playerExpectedOne = $this->player->as_array()->limit(2)->get_many_by('isDeleted', 0);
            $dataPlayerExpected = array(
                'email'     => md5(date('Y-m-d H:i:s').rand(1,100))."test2@gmail.com",
                'password'  => 123456,
                'gender'    => 1,
                'firstName' => "User",
                'lastName'  => "Test",
                'dob'       => array('bday' => 02, 'bmonth' => 12, 'byear' => 1989),
                'option3'   => TRUE
                );

            $idExpected = $playerExpected['id'];
            $idNotExist = isset($playerExpectedNotExit->id) ? ((int)$playerExpectedNotExit->id + 1) : 1;
            $emailExit  = isset($playerExpectedOne['accountEmail']) ? ($playerExpectedOne['accountEmail']) : '';

            // To verify udpate player return is invalid
            $idInvalid  = array(NULL, '', 0, 'abc', -123);

            // To verify id is invalid
            foreach ($idInvalid as $key => $value) {

                $testResult = $this->player->edit($value, $dataPlayerExpected);

                if ( is_array($testResult) && isset($testResult['message']) ) {
                    if ($key == 5) {

                        // Verify Player Not Found
                        $this->assertContains($testResult['message'], "Player Not Found");
                    } else {

                        // Verify Id must is a numeric and greater than zero
                        $this->assertContains($testResult['message'], "Id must be a numeric and greater than zero");
                    }
                }
            }

            /*TODO YOU CAN DELETE EMAIL VALID*/

            // To verify email is invalid
            $emailInvalid = array(NULL, '', 'abc', $emailExit);

            foreach ($emailInvalid as $key => $value) {

                $dataEmail['email'] = $value;
                $testResultSeconds  = $this->player->edit( $idExpected, $dataEmail );

                if ( is_array($testResultSeconds) && isset($testResultSeconds['message']) ) {
                    if ($value == 'abc') {

                         // The email field must contain a valid email address.
                        $this->assertTrue(isset($testResultSeconds['message']));
                    } else {

                         // Verify email invalid
                        $this->assertTrue(isset($testResultSeconds['message']));
                    }
                }
            }

            // To verify update player return is valid
            $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);

            $login = $this->player->login($dataLogin);

            $this->player->setToken( $login['token'] ) ;
            // To verify data player return is empty

            foreach ($dataPlayerExpected as $key => $value) {

                // $dataTest = $dataPlayerExpected;
                $dataTest         = array();
                $dataTest[$key]   = '';
                $testResultThirds = $this->player->edit($idExpected, $dataTest);

                if (is_array($testResultThirds) && isset($testResultThirds['message'])) {

                    if ( $key == 'password' ) {

                        $this->assertContains($testResultThirds['message'][0], "The Password field is required.");
                    }

                    if ( $key == 'gender' ) {
                       $this->assertContains($testResultThirds['message'][0], "The Gender field is required.");
                    }

                    if ( $key == 'firstName' ) {
                        $this->assertContains($testResultThirds['message'][0], "The First Name field is required.");
                    }

                    if ( $key == 'lastName' ) {
                        $this->assertContains($testResultThirds['message'][0], "The Last Name field is required.");
                    }

                    if ( $key == 'dob' ) {
                        $this->assertContains($testResultThirds['message'][0], "The Date of birth field is required.");
                    }
                }
            }

            if(is_array($login) && isset($login['token'])) {

                $playerProfile = $this->player->get_by(array('isDeleted'=> 0, 'profileComplete' => 0));
                if (is_object($playerProfile) && isset($playerProfile->id)) {

                    $data = array(
                      'homePhone' =>'5555555555',
                      'password'  => "123456new",
                      'gender'    => '1',
                      'firstName' => "User",
                      'lastName'  => "Test Update",
                      'dob'       => array('bday' => 12, 'bmonth' => 12, 'byear' => 1988),
                      'option3'   => TRUE
                    );

                    foreach ( $data as $key => $value ) {

                        $dataTestOne       = array();
                        $dataTestOne[$key] = $value;

                        $testResultFourth  = $this->player->edit($playerProfile->id, $dataTestOne);
                        if( is_object($testResultFourth) && isset($testResultFourth->id) ) {

                            if( $key == 'homePhone' ) {
                                // verify homephone update must be equal home phone input previous
                                $this->assertEquals($testResultFourth->accountData['homePhone'], $value );
                            }
                            elseif ( $key == 'gender' ) {

                                // Verify gender return must be equal gender input
                                $this->assertEquals($testResultFourth->gender, "Male");
                            }
                            elseif ( $key == 'dob' ) {

                                $dayOfBirth = $data['dob']['bmonth'] . '/' . $data['dob']['bday'] . '/' . $data['dob']['byear'];
                                $testDob = $testResultFourth->accountData;

                                // Verify dob return must be equal dob input
                                $this->assertEquals( $testDob['dob'], $dayOfBirth );
                            }
                            elseif( $key == 'firstName') {

                                $testfirstName = $testResultFourth->accountData;

                                // Verify firstname return must be equal firstname input
                                $this->assertEquals($testfirstName['firstName'], $value );
                            }
                            elseif( $key == 'lastName') {

                                // Verify screenName return must be equal creatName input
                                $this->assertContains($testResultFourth->screenName, ($data['firstName'].' '.strtoupper( substr( $data['lastName'], 0, 1 ) )));
                                $testlastName = $testResultFourth->accountData;

                                // Verify lastname return must be equal lastname input
                                $this->assertEquals( $testlastName['lastName'], $value );
                            }
                        }
                    }
                    // To verify update player return is valid
                    $data = array(
                      'password'    => "123456new",
                      'gender'      => '1',
                      'firstName'   => "User Update",
                      'lastName'    => "Test Update",
                      'address'     => "604 NUI THANH, DA NANG",
                      'state'       => 'AB',
                      'zip'         => 123,
                      'honorific'   => "miss",
                      'city'        => "DaNang",
                      'homePhone'   => "1200360000",
                      'mobilePhone' =>"9999999999",
                      'address2'    => "193 NGUYEN LUON BANG",
                      'countryCode' => "AO",
                      'dob'         => array('bday' => 12, 'bmonth' => 12, 'byear' => 1988),
                      'option3'     => TRUE,
                    );

                    $testResultFifth = $this->player->edit($idExpected, $data);
                    if(is_object($testResultFifth) && isset($testResultFifth)) {

                        // The profileComplete flag is set when the user updates the player account data with all information populated
                        $this->assertEquals((int)$testResultFifth->profileComplete, 1);
                    }
                }
            }

        } else {

            $this->assertTrue(FALSE);
        }

    }

    function testDeletePlayer() {

        // To verify update player return is valid
        //=========================================
        $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);

        $login = $this->player->login($dataLogin);

        if ( is_array($login) && isset($login['token'] ) ) {

            $this->player->setToken( $login['token'] );

            $player = $this->player->as_array()->order_by('id', 'DESC')->get_by( 'isDeleted', 0 );

            // In case value return invalid
            //===============================
            // In case id return is empty/ null/ undefine/ string
            $idEmpty         = '';
            $test_testResult = $this->player->destroy($idEmpty);

            // Id must be is request
            $this->assertContains($test_testResult['message'], "Id must be a numeric and greater than zero");

            // In case id string
            $id_invalid        = "abc";
            $testResultSeconds = $this->player->destroy($id_invalid);

            //Couldn't update player
            $this->assertContains($testResultSeconds['message'] ,"Id must be a numeric and greater than zero");

            // In case value return valid
            if( is_array($player) && isset($player['id']) ) {

                $testResultThirds = $this->player->destroy($player['id']);
                $playersExpected = (array)$this->player->with_deleted()->get($player['id']);

                //All info of player update must be equal all info on database
                $this->assertEmpty($testResultThirds[0]);

                // Is delete return must be is value = 1
                $this->assertEquals((int)$playersExpected['isDeleted'], 1);
            } else {

                $this->assertTrue(FALSE);
            }
        } else {

            $this->assertTrue(FALSE);
        }
    }

    function testVerifiedMail() {

        $dataExpected = $this->player->order_by('id', 'DESC')->get_by('isDeleted', 0);

        if (is_object($dataExpected) && isset($dataExpected->emailCode)) {

            $testResult = $this->player->emailVerified($dataExpected->emailCode);
            $test = $this->player->getById($dataExpected->id, TRUE);

            // To verify return Player is Verified successfully
            $this->assertContains($testResult['message'], "Verified successfully");

            $this->assertEquals((int)$test->emailVerified, 1);

            // To verify email verified is invalid
            $emailCodeInvalid = array(null,'', 'abc');

            foreach ($emailCodeInvalid as $key => $value) {

                $testResultInvalid = $this->player->emailVerified($value);

                $this->assertContains($testResultInvalid['message'], "Player Not Found");
            }

        } else {

            $this->assertTrue(FALSE);
        }

    }

    protected function getPlayer($emailConfirmed, $FBID) {

        if ($emailConfirmed === 0) {
            $data = array(
                'email'     => substr( md5( uniqid( date( 'Y-m-d H:i:s' ), true ) ), 0, 30 )."login0@gmail.com",
                'password'  => "123456",
                'gender'    => '1',
                'firstName' => "User",
                'lastName'  => "Test",
                'dob'       => array('bday' => 02, 'bmonth' => 12, 'byear' => 1989),
                'phoneHome' => '0123456789',
                'option3'   => TRUE,
                );
            $player = $this->player->add($data);

            if (is_object($player) && isset($player->id)) {
                return $player;
            } else {

                return FALSE;
            }
        } else {

            $data = array(
                'email'     => substr( md5( uniqid( date( 'Y-m-d H:i:s' ), true ) ), 0, 30 )."login1@gmail.com",
                'password'  => "123456",
                'gender'    => 1,
                'firstName' => "User",
                'lastName'  => "Test",
                'dob'       => array('bday' => 02, 'bmonth' => 12, 'byear' => 1989),
                'phoneHome' => '0123456789',
                'option3'   => TRUE,
                'fbId'      => is_null($FBID) ? $FBID : md5( $FBID )
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

    function testGetAllPlayer() {

        $count = $this->player->count_by('isDeleted', 0);
        if( $count > 0 ) {

            $getPlayer     = $this->player->limit(3)->get_many_by( 'isDeleted', 0 );
            $offsetInvalid = array(null, '', 0, 'abc');
            $limitInvalid  = array(null, '', 0, 'abc');

            // verify offset and limit is invalid
            foreach ($limitInvalid as $key => $value) {

                if ( array_key_exists($key, $offsetInvalid) ){

                    $testResultFirst = $this->player->getAll($value, $value);

                    if (is_array($testResultFirst) && isset($testResultFirst['message']))
                        $this->assertContains($testResultFirst['message'], "Player Not Found");
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
            $this->assertTrue(is_array($testResultSeconds['players']));

            //ID player return must be equal ID when offset is zero
            if (isset($getPlayer[0]->id)) {

                $this->assertEquals($testResultSeconds['players'][0]->id, $getPlayer[0]->id);
            }

            if ($count >= $limit) {

                //Players returnt must be equal limit
                $this->assertEquals(sizeof($testResultSeconds['players']), $limit);

                //Count players return must assert Greater Than Or Equal players returnt
                $this->assertTrue(sizeof($testResultSeconds['players']) <= $count);
            }


            // value limit return must equal limit before
            $this->assertEquals(($testResultSeconds['limit']), $limit);

            // value offset return must equal offset before
            $this->assertEquals($testResultSeconds['offset'], $offset);

            // count players return must be equal count player get before
            $this->assertEquals($testResultSeconds['count'], $count);

            // Testing ID return follow value offset
            $offset = 2;
            $testResultThird = $this->player->getAll($offset, $limit);

            //ID player return must be equal ID when offset is 2
            if (isset($getPlayer[$offset]->id)) {

                $this->assertEquals($testResultThird['players'][0]->id, $getPlayer[$offset]->id);
            }

        } else {

            $this->assertTrue(FALSE);
        }
    }

    function testGetPlayerById() {

        $count = $this->player->count_by('isDeleted', 0);

        if( $count > 0 ) {

            $dataExpected = $this->player->with('gender')->order_by('id', 'DESC')->get_by('isDeleted', 0);
            $idExit       = $dataExpected->id;
            $idNotExist   = isset($dataExpected->id) ? ((int)$dataExpected->id + 1) : 1;

            //In case id player return is invalid
            $idInvalid    = array(NULL,'', 0, -123, $idNotExist);

            foreach ($idInvalid as $key => $value) {

                $testResult = $this->player->getById($value, FALSE);

                if(is_array($testResult) && isset($testResult['message'])) {

                    if( $key == 4) {

                        // Verify id isn't exist
                        $this->assertContains($testResult['message'], "Not authorized");
                    } else {

                        // Verify id return is invalid
                        $this->assertContains($testResult['message'], "Id must be a numeric and greater than zero");
                    }
                }
            }

            $testResultValid = $this->player->getById($idExit, TRUE);

            foreach ((array)$testResultValid as $key => $value) {

                if ( array_key_exists($key, (array)$dataExpected) ) {

                    if( $key != 'statusCode') {

                        // To verify get Playper return is valid
                        $this->assertEquals($value, $dataExpected->$key);
                    }
                }
            }

        } else {

            $this->assertTrue(FALSE);
        }
    }

    function testCreateTokenLogin() {

        // To verify login of player invalid
        // ==================================
        $emailInvalid = array('', null, 'abc',  "notexit@gmail.com");
        $password     = array('', null, 'abc' ,'invalid');
        foreach ($emailInvalid as $key => $value) {

            $dataInvalid = array('email' => $emailInvalid[$key], 'password' => $password[$key], 'deviceId' => 1 );
            $testResultOne = $this->player->login($dataInvalid);

            if ( is_array($testResultOne) && isset($testResultOne['message']) ) {
                if( empty($value)) {

                    // To verify email or password invalid
                    $this->assertContains($testResultOne['message'][0], "the email or phone number field is required.", "To verify login of player is invalid", "Verify password or email invalid");
                } elseif($value == 'abc') {

                    // The email field must contain a valid email address.
                    $this->assertContains($testResultOne['message'][0], "The email field must contain a valid email address.");
                }
            } else {

                    // To verify email or password invalid
                    $this->assertContains($testResultOne['message'], "Invalid email or password");
            }
        }

        $player = $this->getPlayer(0, null);

        // To verify emailConfirmed return is invalid
        if ( $player ) {

            $emailConfirmed   = $player->accountEmail;
            $password         = 123456;
            $data             = array( 'email' => $emailConfirmed, 'password' => $password, 'deviceId'=> 1);
            $testResultSecond = $this->player->login($data);

            if ( is_array($testResultSecond) && isset($testResultSecond['message']) ) {

                // verify Registration Incomplete
                $this->assertContains($testResultSecond['message'], "Email has not been confirmed");
            }

        } else {

            $this->assertTrue(TRUE);
        }

        // To verify login of player is valid
        $playerValid = $this->getPlayer(1,NULL);
        if ( $playerValid ) {

            $email         = $playerValid->accountEmail;
            $password      = 123456;

            // To verify device Id must be is numberic and greater than 0
            $deviceInvalid = array('', null, 'abc', -123, 0);
            $data          = array('email' => $email, 'password' => $password, 'deviceId' => 1);

            foreach ($deviceInvalid as $key => $value) {

                $dataDevicedInvalid             = $data;
                $dataDevicedInvalid['deviceId'] = $value;
                $testResultDevice               = $this->player->login($dataDevicedInvalid);

                if ( is_array($testResultDevice) && isset($testResultDevice['message'])) {

                    if (empty($value)) {

                        $this->assertContains($testResultDevice['message'][0], "The Device Id field is required.");

                    } elseif ($value == 'abc') {

                        // To verify device is invalid
                        $this->assertContains( $testResultDevice['message'][0],"The Device Id field must contain only numbers.");
                    } else {

                        // To verify device is invalid
                        $this->assertContains( $testResultDevice['message'][0], "The Device Id field must contain a number greater than 0." );

                    }
                }
            }

            $optionContinueInvalid = array('abc', -123, 2);
            foreach ($optionContinueInvalid as $key => $value) {
                $dataContinueInvalid               = $data;
                $dataContinueInvalid['isContinue'] = $value;
                $testContinue                      = $this->player->login($dataContinueInvalid);

                if ( is_array($testContinue) && isset($testContinue['message'])) {

                    // To verify isContinue is invalid
                    $this->assertContains($testContinue['message'][0], "The isContinue field is not in the correct format.");
                }
            }

            $data['deviceId'] = 1;
            $datatokenHash    = array(md5($email), md5(123456), md5($data['deviceId']));
            $tokenHash        = $this->player->base64Encode($datatokenHash);
            $testResultThirds = $this->player->login($data);

            if( is_array($testResultThirds) && isset($testResultThirds['token']) ) {

                // Token hash return must be equal token hash from email nad password input
                $this->assertEquals($testResultThirds['token'], $tokenHash);
            }

            $testResultFourth = $this->player->login($data);

            if( is_array($testResultFourth) && isset($testResultFourth['token']) ) {

                // Token hash return must be equal token hash from email nad password input
                $this->assertEquals($testResultThirds['token'], $testResultFourth['token']);
            }

            $data['deviceId'] = 2;
            $dataHash         = array(md5($email), md5($password), md5($data['deviceId']));
            $tokenHash        = $this->player->base64Encode($dataHash);
            $testResultFifth  = $this->player->login($data);

            if ( is_array($testResultFifth) && isset($testResultFifth['message']) ) {

                // To verify player login in other device
                $this->assertContains($testResultFifth['message'], "You are logging in on other device, do you want to continues this login?");
            }

            $data['isContinue'] = TRUE;
            $testResultSixth    = $this->player->login($data);

            if( is_array($testResultSixth) && isset($testResultSixth['token']) ) {

                // verify player login success when accept continue login
                $this->assertEquals( $testResultSixth['token'], $tokenHash);

                $testResultInvalidLogout = $this->player->logout();

                if( is_array($testResultInvalidLogout) && isset($testResultInvalidLogout['message']) && isset($testResultInvalidLogout['statusCode']) && $testResultInvalidLogout['statusCode'] == 400) {

                    //To verify logout un_successful
                    $this->assertContains($testResultInvalidLogout['message'], "Can't logout");

                }

                $token            = $this->player->setToken($testResultSixth['token']);
                $testResultLogout = $this->player->logout();



                if( is_array($testResultLogout) && isset($testResultLogout['message']) && isset($testResultLogout['statusCode']) && $testResultLogout['statusCode'] == 200 ) {

                    //To verify logout successful
                    $this->assertEquals($testResultLogout['message'], "Logout successfully");

                }

                $data['deviceId'] = 3;
                $data['isContinue'] = FALSE;

                $testLoginAgain = $this->player->login($data);

                if ( is_array($testLoginAgain) && isset($testLoginAgain) ) {

                    // verify player login success when accept continue login
                    $this->assertEquals( $testResultSixth['token'], $tokenHash );
                } else {

                    $this->assertTrue(FALSE);
                }

            }

        } else {

            $this->assertTrue(FALSE);
        }

    }

    function testAddFacebookInvite() {

        $data = array(
            'friendFacebookId' => 'abc000001'.substr(str_shuffle(md5(time())), 0, 100),
            'responseId' => 'eeee00001'
            );

        // To verify add facebook invite is invalid
        // =========================================

        // To verify id player is invalid
        $player          = $this->player->limit(1)->order_by( 'id', 'DESC' )->get_all();
        $playerIdExit    = !empty( $player ) ? $player[0]->id : 0 ;
        $playerIdNotExit = ($playerIdExit + 1);
        $playIdInvalid   = array(null, 'abc', 0, -1 );

        // To verify player is invalid
        foreach ( $playIdInvalid as $key => $value ) {

            $testResultFirst = $this->facebookinvite->add( $value , $data);
            if ( isset( $testResultFirst['statusCode'] ) && $testResultFirst['statusCode'] == 400 ) {

                // To verify player Id input is invalid
                $this->assertContains( $testResultFirst['message'], 'Id must be a numeric and greater than zero', 'To verify player Id input is invalid' );

            } elseif ( $testResultFirst['statusCode'] == 403 ) {

                //To verify player is not exist
                $this->assertContains( $testResultFirst['message'] , 'Not authorized', "To verify add facebookinvite return is invalid", "To verify player is invalid" );
            }
        }

        // To verify update player return is valid
        $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);
        $login     = $this->player->login($dataLogin);

        if ( isset($login['token'])) {

            $this->player->setToken( $login['token'] );
            $playerId = $login['playerId'];

            // To verify friendFacebookId is invalid
            $invalid = array(null, str_repeat('a1', 65));

            foreach ($invalid as $key => $value) {

                $dataFacebookInvalid                     = $data;
                $dataFacebookInvalid['friendFacebookId'] = $value;

                // To verify friendFacebookId is invalid
                $testResultSecond = $this->facebookinvite->add( $playerId, $dataFacebookInvalid );

                if  ( is_array( $testResultSecond ) && isset( $testResultSecond['message'] ) ) {

                    if ( is_null( $value ) ) {

                        // To verify friendFacebookId is null
                        $this->assertContains( $testResultSecond['message'][0], 'The friendFacebookId field is required.', 'To verify friendFacebookId is null'  );

                    } else {

                        // To verify friendFacebookId is invalid
                        $this->assertContains( $testResultSecond['message'][0], 'The friendFacebookId field can not exceed 45 characters in length.', 'To verify friendFacebookId is invalid'  );
                    }
                }
            }

            // To verify responseId is invalid
            foreach ($invalid as $key => $value) {

                $dataFacebookInvalid               = $data;
                $dataFacebookInvalid['responseId'] = $value;

                // To verify responseId is invalid
                $testResultThird = $this->facebookinvite->add( $playerId, $dataFacebookInvalid );

                if  ( is_array( $testResultThird ) && isset( $testResultThird['message'] ) ) {

                    if ( is_null( $value ) ) {

                        // To verify responseId is null
                        $this->assertContains( $testResultThird['message'][0], 'The responseId field is required.', 'To verify responseId is null'  );

                    } else {

                        // To verify responseId is invalid
                        $this->assertContains( $testResultThird['message'][0], 'The responseId field can not exceed 128 characters in length.', 'To verify responseId is invalid'  );
                    }
                }
            }

            // To verify add facebook invite id valid
            // =========================================
            $testResultFourth = $this->facebookinvite->add( $playerId, $data);

            if ( is_object( $testResultFourth ) ) {

                // To verify playerId return must be equal playerId input
                $this->assertContains( $testResultFourth->playerId, $playerId, 'To verify playerId return must be equal playerId input');

                // To verify friendFacebookId return must be equal friendFacebookId input
                $this->assertContains( $testResultFourth->friendFacebookId, $data['friendFacebookId'] , 'To verify friendFacebookId return must be equal friendFacebookId input');

                // To verify responseId return must be equal responseId input
                $this->assertContains( $testResultFourth->responseId, $data['responseId'] , 'To verify responseId return must be equal responseId input');

            } else {

                $this->assertTrue( FALSE,  "Can't verify add facebookinvite is valid. Pls try test again.");
            }

        } else {

            $this->assertTrue( FALSE,  "Can't verify add facebookinvite is valid. Pls try test again.");
        }

    }

    function testGetProfile() {

        // To verify get Profile return is invalid
        //=========================================

        // To verify id player is invalid

        $player          = $this->player->limit(1)->order_by( 'id', 'DESC' )->get_all();
        $playerIdExit    = !empty( $player ) ? $player[0]->id : 0 ;
        $playerIdNotExit = ($playerIdExit + 1);
        $playIdInvalid   = array(null, 'abc', 0, $playerIdNotExit, -1 );
        $offset          = 0;
        $limit           = 10;

        // To verify player is invalid
        foreach ( $playIdInvalid as $key => $value ) {

            $testResultFirst = $this->player->profile( $value, $limit, $offset );

            if ( isset( $testResultFirst['statusCode'] ) && $testResultFirst['statusCode'] == 400 ) {

                // To verify player Id input is invalid
                $this->assertContains( $testResultFirst['message'], 'Id must be a numeric and greater than zero', 'To verify player Id input is invalid' );

            } elseif ( isset( $testResultFirst['statusCode'] ) && $testResultFirst['statusCode'] == 403 ) {

                //To verify player is not exist
                $this->assertContains( $testResultFirst['message'] , 'Not authorized', "To verify player is invalid" );
            }
        }

        // Login with admin
        $dataLogin = array('email' => "admin@kizzang.com", 'password' => '123456789', 'deviceId' => 1);

        $login     = $this->player->login($dataLogin);
        $this->player->setToken( $login['token'] );
        $playerId = 2;

        // To verify get Profile return is valid

        // get position from db
        $position                 = $this->CI->position->get_by( array( 'playerId' => $playerId, 'calendarDate' => date( 'Y-m-d' ) ) );
        $currentPositionExpected  =  ($position) ? $position->endPosition : 0;

        // get highest position from database with playerId
        $highestExpected          =  $this->CI->position->order_by( 'endPosition', 'DESC' )->limit( 1 )->get_by( 'playerId', $playerId );
        $hightestPositionExpected = ($highestExpected) ? $highestExpected->endPosition : 0;

         // select max(prizeAmount) AS highest, sum(prizeAmount) AS total from WinConfirmations where playerId = $1
        $win = $this->CI->db->select( 'max(prizeAmount) AS highest, sum(prizeAmount) AS total', FALSE )
                ->where( 'playerId', $playerId )
                ->get( 'WinConfirmations' )
                ->row();

        // get expected highest Win
        $highestWinExpected = empty($win->highest) ? 0.0 : (float)$win->highest;

        // get total AggregateWin expected
        $totalAddgrgateWinExpected = empty($win->total) ? 0.0 : (float)$win->total;

        // get favoriteGame
        $gameCount = $this->CI->db->select( 'sum(count) AS count, gameType')
                    ->where( 'playerId', $playerId )
                    ->group_by( 'gameType' )
                    ->order_by( 'count', 'DESC' )
                    ->limit( 1 )
                    ->get( 'GameCount' )
                    ->row();

        $favoriteGameExpected = empty($gameCount) ? '' : $gameCount->gameType;

        // get list friend facebook
        $friendList = $this->CI->facebookinvite->get_many_by( 'playerId', $playerId );

        $friendListExpected = array();

        if ( !empty( $friendList ) ) {

            foreach ($friendList as $key => $value) {

                array_push( $friendListExpected, $value->friendFacebookId );
            }
        }

        $testResultSecond = $this->player->profile( $playerId, $limit, $offset );

        if ( is_array( $testResultSecond ) && isset( $testResultSecond['profile'] ) ) {

            // To verify highest return must be equal max of prizaAmount of get from database player
            $this->assertEquals( $testResultSecond['profile']['highestWin'], $highestWinExpected, 'To verify highest return must be equal max of prizaAmount of get from database player' );

            // To verify totalAggregateWin must be sum prizaAmount from database by player
            $this->assertEquals( $testResultSecond['profile']['totalAggregateWin'], $totalAddgrgateWinExpected,'To verify totalAggregateWin must be sum prizaAmount from database by player' );

            // To verify currentPostion return must be equal endPosition get from database by player
            $this->assertEquals( $testResultSecond['profile']['currentPosition'], $currentPositionExpected,'To verify currentPostion return must be equal endPosition get from database by player' );

            // To verify hightestPosition return is valid
            $this->assertEquals( $testResultSecond['profile']['highestPosition'], $hightestPositionExpected,'To verify hightestPosition return is valid' );

            // To verify favoriteGame return is valid
            $this->assertEquals( $testResultSecond['profile']['favoriteGame'], $favoriteGameExpected,'To verify favoriteGame return is valid' );

            // To verify friends list return is valid
            $testFrients = $testResultSecond['profile']['friendList'];
            if ( !empty($testFrients ) ) {

                foreach ($testFrients as $key => $value) {

                    if ( array_key_exists($key, $friendListExpected) ){

                        $this->assertEquals( $testFrients["$key"], $friendListExpected["$key"] ,'To verify friends list return is valid' );
                    }

                }
            }
            else {

                $this->assertEmpty( $testFrients, 'To verify friends list return is valid' );
            }

        }

    }

    // Encrypt string
    function encryptString( $data )
    {
        return $data;
    }
    
    function testLoginByFaceBook() {

        $result = $this->player->memcacheInstance->flush();

        $query = $this->CI->db->query('SET FOREIGN_KEY_CHECKS=0;');
        // $query = $this->CI->db->query('TRUNCATE Players;');
        // $query = $this->CI->db->query('TRUNCATE Positions;');
        $query = $this->CI->db->query('TRUNCATE MasterEmail;');
        $query = $this->CI->db->query('TRUNCATE MasterAccount;');
        $query = $this->CI->db->query('SET FOREIGN_KEY_CHECKS=1;');

        // To verify login by Facebook is invalid
        // ========================================
        // To verify email input is invalid
        $FBIDExist = 123456789;
        $rand      = rand(1,10);
        $FBID      = '12333333'. "$rand";
        $FBIDMatch = '123456789'. "$rand";
        $data      = array(
            'email'      => substr( md5( uniqid( date( 'Y-m-d H:i:s' ), true ) ), 0, 30 )."testingfacebook@gmail.com",
            'FBID'       => (int)$FBID ,
            'deviceId'   => 1,
            'gender'     => 1,
            'birthDay'   => 02,
            'birthMonth' => 12,
            'birthYear'  => 1989,
            'screenName' => 'Nhan Doan',
            'firstName'  => 'Nhan',
            'lastName'   => 'Nhan' );

        $emailInvalid = array(NULL, '', -123, 'abc', 0);

        foreach ($emailInvalid as $key => $value) {

            $dataEmailInvalid          = $data;
            $dataEmailInvalid['email'] = $value;
            $testEmailResultInvalid    = $this->player->loginFacebook($dataEmailInvalid);

            if(is_array($testEmailResultInvalid) && isset($testEmailResultInvalid['message'])) {

                // To verify email is invalid must be required
               if ( empty($value) ) {

                    $this->assertContains($testEmailResultInvalid['message'][0], "The email field is required.");
               } else {

                // To verify the email field must contain a valid email address.
                $this->assertContains($testEmailResultInvalid['message'][0], "The email field must contain a valid email address.");
               }
            }
        }

        // To verify FBID input is invalid
        $FBIDInvalid = array('', null, 0, -123, 'abc');

        foreach ($FBIDInvalid as $key => $value) {

            $dataFBIDInvalid         = $data;
            $dataFBIDInvalid['FBID'] = $value;
            $testFBIDInvalid         = $this->player->loginFacebook($dataFBIDInvalid);

            if ( is_array($testFBIDInvalid) && isset($testFBIDInvalid['message']) ) {

                if (empty($value) || $value === 0) {

                    // To verify The FBID field is required.
                    $this->assertContains($testFBIDInvalid['message'][0], "The FBID field is required.");
                } else {

                    // To verify The FBID field must contain a number greater than 0.
                    $this->assertContains($testFBIDInvalid['message'][0], "The FBID field must contain a number greater than 0.");
                }
            }
        }

        // To verify Device Id input invalid
        $deviceId = array('', null, 'abc', -123, 0);
        foreach ($deviceId as $key => $value) {

            $dataDeviceIdInvalid             = $data;
            $dataDeviceIdInvalid['deviceId'] = $value;
            $testDeviceIdInvalid             = $this->player->loginFacebook($dataDeviceIdInvalid);

            if( is_array($testDeviceIdInvalid) && isset($testDeviceIdInvalid['message']) ) {
                if ( empty($value) ) {

                    // To verify The DeviceId field is required.
                    $this->assertContains($testDeviceIdInvalid['message'][0], "The Device Id field is required.");

                } elseif($value == 'abc') {

                    $this->assertContains($testDeviceIdInvalid['message'][0], "The Device Id field must contain a number greater than 0.");
                } else {

                    // To verify The DeviceId field must contain a number greater than 0.
                    $this->assertContains($testDeviceIdInvalid['message'][0], "The Device Id field must contain a number greater than 0.");

                }
            }
        }

        // To verify login by Facebook is valid
        // ======================================

        // To verify register return is valid with email match any account
        $emailMatch = $this->getPlayer(1, null);
        if( is_object($emailMatch) && isset($emailMatch->accountEmail) ) {

            $dataEmailMatch          = $data;
            $dataEmailMatch['email'] = $emailMatch->accountEmail;
            $testResultSecond        = $this->player->loginFacebook($dataEmailMatch);
            $dataExpected            = $this->player->get_by('id', $emailMatch->id);

            if( is_array($testResultSecond) && isset($testResultSecond['message']) ) {

                // verify FBID had add to account with email address
                //$this->assertContains($testResultSecond['message'], "Added {$dataEmailMatch['FBID']} to account with {$emailMatch->accountEmail} address");

                if (is_object($dataExpected) && isset($dataExpected->id) ) {

                    // verify FBID return must be equal FBID from input previous
                    //$this->assertEquals($this->encryptString($data['FBID']), $dataExpected->fbId);

                    // verify email return must be qual email input previous
                    $this->assertEquals($emailMatch->accountEmail, $dataExpected->accountEmail);
                }
            }
        }

        // To verify register return is valid with FBID , email match any FBID, email account
        $dataGetMatchEmailFBID = $this->getPlayer(1, (int)$FBIDMatch);

        if ( is_object($dataGetMatchEmailFBID) && isset($dataGetMatchEmailFBID->id) ) {
            $dataMatchEmailFBID             = $data;
            $dataMatchEmailFBID['email']    = $dataGetMatchEmailFBID->accountEmail;
            $dataMatchEmailFBID['FBID']     = (int)$FBIDMatch;
            $dataMatchEmailFBID['deviceId'] = 1;
            $email                          = $dataGetMatchEmailFBID->accountEmail;

            $deviceId = $data['deviceId'];
            $tokenFrist = array(md5($email), md5(NULL) , md5($deviceId));
            $tokenHash = $this->player->base64Encode($tokenFrist);
            $testResultFifth = $this->player->loginFacebook($dataMatchEmailFBID);

            if( is_array($testResultFifth) && isset($testResultFifth['token']) ) {

                //Token return when match FBID and email account from kizzang
                // $this->assertEquals($testResultFifth['token'], $tokenHash);
            }

            $dataMatchEmailFBID['deviceId'] = 2;
            $testResultOtherDeviceId        = $this->player->loginFacebook($dataMatchEmailFBID);

            if ( is_array($testResultOtherDeviceId) && isset($testResultOtherDeviceId['message']) ) {

                // To verify login fb is valid when player just login with other device
                //$this->assertContains($testResultOtherDeviceId['message'], "You are logging in on other device, do you want to continues this login?");
            }

        }

        // To verify register return is valid with, FBID match any FBID account, email doesn't match account, password exist for this account
        $onlyMacthFBID   = 10000000;
        $playersMachFBID = $this->getPlayer(1, $onlyMacthFBID);

        if ( is_object($playersMachFBID) && isset($playersMachFBID->id) ) {

            $emailHashOld                  = md5($playersMachFBID->accountEmail);
            $passwordHash                  = md5(NULL);
            $dataTestMatchFBID             = $data;
            $dataTestMatchFBID['email']    = "testingfacebook@gmail.com";
            $dataTestMatchFBID['FBID']     = $onlyMacthFBID;
            $dataTestMatchFBID['deviceId'] = 1;
            $emaiHashNew                   = md5($dataTestMatchFBID['email']);

            $testResultFourth              = $this->player->loginFacebook($dataTestMatchFBID);

            if ( is_array($testResultFourth) && isset($testResultFourth['token'])) {

                $datatokenHash   = array(md5($playersMachFBID->accountEmail), $passwordHash, md5($dataTestMatchFBID['deviceId']));
                $tokenHashOnlyFB = $this->player->base64Encode($datatokenHash);

                // verify token return must be equal tokehash
                // $this->assertEquals($testResultFourth['token'], $tokenHashOnlyFB);

                $dataExpectedFBID = $this->masteremail->get_many_by('playerId', $playersMachFBID->id);

                if ( is_array($dataExpectedFBID) && (sizeof($dataExpectedFBID) > 0 )){

                    // Verify email hash save in MasterEmail tables
                    $this->assertEquals($dataExpectedFBID[0]->emailHash, $emaiHashNew);

                    $this->assertEquals($dataExpectedFBID[1]->emailHash, $emailHashOld);
                }

                $dataTestMatchFBID['deviceId'] = 2;
                $testResultOtherDeviceId       = $this->player->loginFacebook($dataTestMatchFBID);

                if (is_array($testResultOtherDeviceId) && isset($testResultOtherDeviceId['message'])) {

                    // To verify login fb is valid when player just login with other device
                    $this->assertContains($testResultOtherDeviceId['message'], "You are logging in on other device, do you want to continues this login?");
                }

                // To verify login with other deviceId
                $dataTestMatchFBID['isContinue'] = TRUE;

                $testResultLoginContinue         = $this->player->loginFacebook($dataTestMatchFBID);

                if(is_array($testResultLoginContinue) && isset($testResultLoginContinue['token'])) {

                    $tokenHashArray  = array(md5($playersMachFBID->accountEmail), $passwordHash, md5($dataTestMatchFBID['deviceId']));
                    $tokenHashOnlyFB = $this->player->base64Encode($tokenHashArray);

                    // verify token return must be equal tokehash
                    // $this->assertEquals($testResultLoginContinue['token'], $tokenHashOnlyFB);

                }
            }

            // "To verify register return is valid with, FBID match any FBID account, email match any account, password exist for this account
            $onlyMacthFBIDSecond  = 11111111111;
            $playerOnlyMatchFBID  = $this->getPlayer(1, $onlyMacthFBIDSecond);
            $playerOnlyMatchEmail = $this->getPlayer(1, null);

            if ( is_object($playerOnlyMatchFBID) && is_object($playerOnlyMatchEmail) ) {

                $onlyEmail                   = $playerOnlyMatchEmail->accountEmail;
                $dataTestInCase              = $data;
                $dataTestInCase['email']     = $onlyEmail;
                $dataTestInCase['FBID']      = $onlyMacthFBIDSecond;
                $dataTestInCase['deviceId']  = 1;

                $testResultSixth = $this->player->loginFacebook($dataTestInCase);
                $emailHash       = md5($onlyEmail);
                $emailHashFb     = md5($playerOnlyMatchFBID->accountEmail);

                if ( is_array($testResultSixth) && isset($testResultSixth['token']) ) {

                    $tokenArrayHashOnlyFB = array(md5($playerOnlyMatchFBID->accountEmail), md5(null), md5($dataTestInCase['deviceId']));
                    $tokenHashOnlyFB      = $this->player->base64Encode($tokenArrayHashOnlyFB);

                    // verify token return must be equal tokehash
                    // $this->assertEquals($testResultSixth['token'], $tokenHashOnlyFB);

                    $dataExpectedInCase = $this->masteraccount->get_by('playerIdFb', $playerOnlyMatchFBID->id);

                    if ( is_object($dataExpectedInCase) ){

                        // Verify email hash save in MasterEmail tables
                        $this->assertEquals($dataExpectedInCase->emailHashFb, $emailHashFb);

                        $this->assertEquals($dataExpectedInCase->emailHash, $emailHash);
                    }
                }

            }
        }
    }
}
