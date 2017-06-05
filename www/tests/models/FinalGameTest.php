<?php

class FinalGameTest extends CIUnit_TestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->CI->load->model(array('finalmatch', 'finalcategory','finalmatchconfig', 'finalpick', 'finalplayercard', 'finalresult', 'finalteam'));

        $this->finalmatch           = $this->CI->finalmatch;
        $this->finalteam            = $this->CI->finalteam;
        $this->finalcategory        = $this->CI->finalcategory;
        $this->finalmatchconfig     = $this->CI->finalmatchconfig;
        $this->finalpick            = $this->CI->finalpick;
        $this->finalresult          = $this->CI->finalresult;
        $this->finalplayercard      = $this->CI->finalplayercard;

        $this->finalplayercard->executeTesting = TRUE;
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    //==============================
    // Test final categories    //
    //============================
    function testCategoryAdd() {

        $data = array(
            'name' => 'Final Game' . md5(date('Y-m-d H:i:s').rand(1,100)),
        );

        // To verify add final Category is invalid
        //=========================================
        // To verify data is empty
        $dataInvalid     = '';
        $testResultFirst = $this->finalcategory->add( $dataInvalid );

        if (is_array($testResultFirst) && isset($testResultFirst['message']) ) {

            $this->assertContains( $testResultFirst['message'], 'Please enter the required data', 'To verify data is empty');
        }

        // To verify data is empty
        $dataInvalid['name'] = '';
        $testResultSecond    = $this->finalcategory->add( $dataInvalid );

        if (is_array($testResultSecond) && isset($testResultSecond['message']) ) {

            $this->assertContains( $testResultSecond['message'][0], 'The name field is required.', 'To verify data is empty');
        }

        // To verify add category is exist
        $categories = $this->finalcategory->get_by(array('id !=' => 0));

        if ( !empty( $categories ) ) {

            $dataInvalid['name'] = $categories->name;
            $testResultThird     = $this->finalcategory->add( $dataInvalid );

            if ( is_array($testResultThird) && isset($testResultThird['message']) ) {

                $this->assertContains( $testResultThird['message'], 'Cannot save a duplicate Final Category with name - ' . $dataInvalid['name'] , 'To verify add category is exist');
            }
        }

        // To verify add final Category is valid
        //=======================================
        $nameExpected     = $data['name'];
        $testResultFourth = $this->finalcategory->add( $data );

        if ( is_object($testResultFourth) ) {

            // To verify name returm must be equal name final Category input
            $this->assertEquals($testResultFourth->name, $nameExpected, 'To verify name returm must be equal name final Category input');

        } else {

            $this->assertTrue( FALSE , "Can't verify add category final is case valid");
        }

    }

    function testCategoryUpdate() {

        $category = $this->finalcategory->order_by('id', 'DESC')->get_by(array('id !='=> 0));

        if( !empty($category) ) {

            $id           = $category->id;
            $data['name'] = $category->name . "Update";

            // To verify update final Category is invalid
            //=========================================
            // To verify data is empty
            $dataEmpty       = '';
            $testResultFirst = $this->finalcategory->edit( $id , $dataEmpty );

            if ( is_array( $testResultFirst ) && isset( $testResultFirst['message'] ) ) {

                // To verify data is empty
                $this->assertContains( $testResultFirst['message'], 'Please enter the required data', 'To verify data is empty' );
            }

            // To verify id is invalid
            $idInvalid = array('', NULL, 'abc', 0, -1);

            foreach ($idInvalid as $key => $value) {

                $testResultSecond = $this->finalcategory->edit( $value, $data );

                if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

                   $this->assertContains( $testResultSecond['message'], 'Id must be a numeric and greater than zero', 'To verify id is invalid' );
                }
            }

            // To verify update final Category is valid
            //=========================================
            $testResultThird = $this->finalcategory->edit( $id, $data );

            if ( is_object($testResultThird) ) {

                // To verify name returm must be equal name final Category input
                $this->assertEquals( $testResultThird->name, substr( $data['name'], 0, 50 ), 'To verify name returm must be equal name final Category input');

            } else {

                $this->assertTrue( FALSE, "Can't verify update category final is case valid");
            }

        } else {

            $this->assertTrue( FASLE, "Can't verify update category final is case valid. Please testing add final Category before testing update.");
        }

    }

    //=======================
    // Test final teams   //
    //======================
    function testTeamAdd() {

        $data = array(
            'finalCategoryId' => 1,
            'name'           => "Add Team". md5(date('Y-m-d H:i:s').rand(1,100)) ,
        );

        // To verify final Team is invalid
        //=========================================
        // To verify data is empty
        $dataInvalid     = '';
        $testResultFirst = $this->finalteam->add( $dataInvalid );

        if (is_array($testResultFirst) && isset($testResultFirst['message']) ) {

            $this->assertContains( $testResultFirst['message'], 'Please enter the required data', 'To verify data is empty');
        }

        // To verify Team catagoryId is invalid
        $categoryIdInvalid = array('', null, 0, -1);
        foreach ($categoryIdInvalid as $value) {

            $category                    = $data;
            $category['finalCategoryId'] = $value;
            $testResultSecond            = $this->finalteam->add( $category );

            if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

                if( !empty($value) ) {

                    $this->assertContains( $testResultSecond['message'][0], 'The finalCategoryId field must contain a number greater than 0.', 'To verify catagoryId is invalid' );

                } else {

                    $this->assertContains( $testResultSecond['message'][0], 'The finalCategoryId field is required.', 'To verify catagoryId is invalid' );
                }

            }
        }

        // To verify Team team1 is invalid
        $nameInvalid           = $data;
        $nameInvalid['name']   = '';
        $testResultThird       = $this->finalteam->add( $nameInvalid );

        if( is_array($testResultThird) && isset($testResultThird['message'])) {

            $this->assertContains( $testResultThird['message'][0], 'The name field is required.', 'To verify name is invalid' );

        }

        // To verify final Team is valid
        //=========================================
        $testResultFourth = $this->finalteam->add( $data );

        if ( is_object($testResultFourth) ) {

            // To verify categoryId return must be categoryId from input
            $this->assertEquals( $data['finalCategoryId'], (int)$testResultFourth->finalCategoryId , 'To verify categoryId return must be categoryId from input');

            // To verify name return must be name from input
            $this->assertEquals( substr( $data['name'], 0, 50 ), $testResultFourth->name , 'To verify name return must be name from input');

        } else {

            $this->assertTrue( FALSE, "Can't verify add team final is case valid") ;
        }

    }

    function testTeamUpdate() {

        $team = $this->finalteam->get_by( array('id !=' => 0) );

        if ( !empty( $team ) ) {

            $id                            = $team->id;
            $categoryId                    = $team->finalCategoryId;
            $dataUpdate['name']            = substr($team->name . md5(date('Y-m-d H:i:s').rand(1,100)), 0, 50 );
            $dataUpdate['finalCategoryId'] = $categoryId;

            // To verify update final Team is invalid
            //===========================================================

            // To verify name is invlaid
            $nameInvalid['name']            = '';
            $nameInvalid['finalCategoryId'] = $categoryId;
            $testResultFirst                = $this->finalteam->edit($id, $nameInvalid );

            if( is_array($testResultFirst) && isset($testResultFirst['message'])) {

                $this->assertContains( $testResultFirst['message'][0], 'The name field is required.', 'To verify name is invalid' );

            }

            // To verify id is invalid
            $idInvalid = array('', null, 0, -1);
            foreach ($idInvalid as $value) {

                $testResultSecond = $this->finalteam->edit( $value, $dataUpdate );

                if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

                    $this->assertContains( $testResultSecond['message'], 'Id must be a numeric and greater than zero', 'To verify catagoryId is invalid' );

                }
            }

            // To verify category Id is invalid
            $categoryIdInvalid = array('', null, 0, -1);

            foreach ($categoryIdInvalid as $value) {

                $dataUpdate['finalCategoryId'] = $value;
                $testResultSecond              = $this->finalteam->edit( $id, $dataUpdate );

                if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

                    if( !empty($value) ) {

                        $this->assertContains( $testResultSecond['message'][0], 'The finalCategoryId field must contain a number greater than 0.', 'To verify catagoryId is invalid' );
                    } else {

                        $this->assertContains( $testResultSecond['message'][0], 'The finalCategoryId field is required.', 'To verify catagoryId is invalid' );

                    }
                }
            }

            // To verify data is empty
            $testResultFourth  = $this->finalteam->edit($id, '' );

            if( is_array($testResultFourth) && isset($testResultFourth['message'])) {

                $this->assertContains( $testResultFourth['message'], 'Please enter the required data', 'To verify name is invalid' );

            }

            // To verify update final Team is valid
            //=========================================================
            $testResultFifth = $this->finalteam->edit( $id, $categoryId, $dataUpdate);

            if ( is_object($testResultFifth) ) {

                // To verify id return must be equal id input
                $this->assertEquals($id, $testResultFifth->id, 'To verify id return must be equal id input');

                // To verify categoryId return must be equal categoryId input
                $this->assertEquals($categoryId, $testResultFifth->finalCategoryId, 'To verify categoryId return must be equal categoryId input');

                // To verify name return must be equal name input
                $this->assertEquals($dataUpdate['name'], $testResultFifth->name, 'To verify name return must be equal name input');

            } elseif( $testResultFifth['code'] == 5 ) {

                $this->assertContains( $testResultFifth['message'], "Cannot save a duplicate sport team with name - ". $dataUpdate['name']. " finalCategoryId - " . $categoryId );
            }

        } else {

            $this->assertTrue( FALSE, "Can't verify update team final is case valid. Please testing add final team before testing update.") ;
        }
    }

    //=======================
    // Test final config  //
    //=======================
    function testConfigAdd() {

        $cardDate         = date('m-d-Y');
        $config           = $this->finalmatchconfig->order_by('parlayCardId', 'DESC')->get_by(array('parlayCardId !=' => 0));
        $parlayCardId  = (! empty($config) ? ((int)$config->parlayCardId + 1) : 1 );

        $data             = array(

            'parlayCardId' => $parlayCardId,
            'serialNumber'    => 'TestSerial123',
            'cardWin' => 'ABC00001',
            'cardDate' => date('m-d-Y')
        );

        // To verify add Config is invalid
        //=========================================
        // To verify data is empty
        $dataInvalid     = '';
        $testResultFirst = $this->finalmatchconfig->add( $dataInvalid );

        if (is_array($testResultFirst) && isset($testResultFirst['message']) ) {

            $this->assertContains( $testResultFirst['message'], 'Please the required enter data', 'To verify data is empty');
        }

        // To verify parlayCardId is invalid
        $idInvalid = array('', null, 0, -1);

        foreach ($idInvalid as $value) {

            $parlayCardIdInvalid                 = $data;
            $parlayCardIdInvalid['parlayCardId'] = $value;
            $testResultSecond                    = $this->finalmatchconfig->add( $parlayCardIdInvalid );

            if( is_array($testResultSecond) && isset($testResultSecond['message']) ) {

                if( !empty($value) ) {

                    $this->assertContains( $testResultSecond['message'][0], 'The parlayCardId field must contain a number greater than 0.', 'To verify parlayCardId is invalid' );

                } else {

                    $this->assertContains( $testResultSecond['message'][0], 'The parlayCardId field is required.', 'To verify parlayCardId is invalid' );
                }

            }
        }

        // To verify add Config is valid
        //=========================================
        $testResultThird = $this->finalmatchconfig->add( $data );

        if ( is_object($testResultThird) ) {

            // To verify parlayCardId return must be equal parlayCardId input
            $this->assertEquals((int)$testResultThird->parlayCardId, $parlayCardId, 'To verify parlayCardId return must be equal parlayCardId input');

            // To verify serialNumber return must be equal serialNumber input
            $this->assertEquals($testResultThird->serialNumber, $data['serialNumber'], 'To verify serialNumber return must be equal serialNumber input');


        }
        else {

            $this->assertTrue( FALSE, "Can't verify add config final.") ;
        }
    }

    function testConfigUpdate() {

        $config = $this->finalmatchconfig->order_by('parlayCardId', 'DESC')->get_by(array('id !=' => 0));

        if ( !empty($config)) {

            // To verify Update Config is invalid
            //=========================================
            $id         = $config->id;
            $dataUpdate = array(
                'serialNumber' => $config->serialNumber . "Updated",
            );

            // To verify id is invalid
            $idInvalid = array('', null, 0, -1);

            foreach ($idInvalid as $value) {

                $testResultFirst = $this->finalmatchconfig->edit( $value, $dataUpdate );

                if( is_array($testResultFirst) && isset($testResultFirst['message']) ) {

                    $this->assertContains( $testResultFirst['message'], 'The id must be a numeric and greater than zero', 'To verify parlayCardId is invalid' );

                }
            }
            // To verify data is empty
            $dataInvalid = '';
            $testResultSecond = $this->finalmatchconfig->edit( $id, $dataInvalid );

            if (is_array($testResultSecond) && isset($testResultSecond['message']) ) {

                $this->assertContains( $testResultSecond['message'], 'Please enter the required data', 'To verify data is empty');
            }

            // To verify Update Config is valid
            //=========================================
            $testResultThird = $this->finalmatchconfig->edit( $id, $dataUpdate);

            if ( is_object($testResultThird) ) {

                // To verify id return must be equal id input
                $this->assertEquals( $testResultThird->id, $id, 'To verify id return must be equal id input');


                // To verify serialNumber return must be equal serialNumber input
                $this->assertEquals( $testResultThird->serialNumber, $dataUpdate['serialNumber'], 'To verify serialNumber return must be equal serialNumber input');

            } else {

                $this->assertTrue( FALSE, "Can't verify update config final in case valid.") ;
            }

        } else {

            $this->assertTrue( FALSE, "Can't verify update config final") ;
        }

    }
    function testConfigGetById() {

        $config = $this->finalmatchconfig->order_by('id', 'DESC')->get_by(array('id !=' => 0));

        if (! empty($config) ) {

            $id = $config->id;

            // To verify GetById Config is invalid
            //=========================================
            // To verify id input is invalid
            $idInvalid = array('', 'abc' ,null, 0, -1);

            foreach ($idInvalid as $value) {

                $testResultFirst = $this->finalmatchconfig->getById( $value );

                if( is_array($testResultFirst) && isset($testResultFirst['message']) ) {

                    $this->assertContains( $testResultFirst['message'], 'Id must be a numeric and greater than zero', 'To verify id is invalid' );

                }
            }

            // To verify GetById Config is valid
            //=========================================

            $testResultSecond = $this->finalmatchconfig->getById( $id );

            if ( is_object($testResultSecond) ) {

                // To verify id return must be equal id input
                $this->assertEquals( $testResultSecond->id, $id, 'To verify id return must be equal id input');

                // To verify parlayCardId return must be equal parlayCardId input
                $this->assertEquals( $testResultSecond->parlayCardId, $config->parlayCardId, 'To verify parlayCardId return must be equal parlayCardId database');

                // To verify serialNumber return must be equal serialNumber input
                $this->assertEquals( $testResultSecond->serialNumber, $config->serialNumber, 'To verify serialNumber return must be equal serialNumber database');

            } else {

                $this->assertTrue( FALSE, "Can't verify update config final in case valid.");
            }

        }
        else {

            $this->assertTrue( FALSE , "Can't verify get By id config final. Config empty");

        }

    }

    function testAddFinalMatch() {
        // truncate table
        $this->CI->db->query('SET FOREIGN_KEY_CHECKS=0;');
        $this->CI->db->query('TRUNCATE FinalMatches;');
        $this->CI->db->query('SET FOREIGN_KEY_CHECKS=1;');
        $this->CI->player->memcacheInstance->flush();
        $final = $this->CI->db->query('SELECT finalCategoryId, MAX(counted) as count FROM
            (
            SELECT finalCategoryId ,  COUNT(*) AS counted
            FROM FinalTeams
            WHERE finalCategoryId != 0
            GROUP BY finalCategoryId
            ) as counts;')->result();

        if( !empty($final) && $final[0]->count > 4 ) {

            $finalCategoryId = $final[0]->finalCategoryId;
            $teams           = $this->finalteam->limit(2, 0)->get_many_by( 'finalCategoryId', $finalCategoryId);
            $data            = array(
                'team1'           => $teams[0]->id,
                'team2'           => $teams[1]->id,
                'name'            => 'semi1',
                'finalCategoryId' => $finalCategoryId,
                'parlayCardId'    => 1,
                'dateTime'        => date('m-d-Y H:i:s'),
                'endDate'         => date('m-d-Y H:i:s', strtotime('+ 1 day')),
                'team1Name'       => "TEAM1",
                'team2Name'       => "TEAM2",
                );

            //To verify add final is invalid
            //==============================
            // To verify data is empty
            $dataInvalid     = '';
            $testResultFirst = $this->finalmatch->add( $dataInvalid );

            if (is_array($testResultFirst) && isset($testResultFirst['message']) ) {

                $this->assertContains( $testResultFirst['message'], 'Please the required enter data', 'To verify data is empty');
            }

            // To verify finale catagoryId is invalid
            $categoryIdInvalid = array('', null, 0, -1);
            foreach ($categoryIdInvalid as $value) {

                $category                    = $data;
                $category['finalCategoryId'] = $value;
                $testResultSecond            = $this->finalmatch->add( $category );

                if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

                    if ( !empty( $value ) ) {

                        $this->assertContains( $testResultSecond['message'][0], 'The finalCategoryId field must contain a number greater than 0.', 'To verify catagoryId is invalid' );
                    } else {

                        $this->assertContains( $testResultSecond['message'][0], 'The finalCategoryId field is required.', 'To verify catagoryId is invalid' );
                    }
                }
            }

            // To verify finale team1 is invalid
            $team1Invalid = array('', null, 0, -1);
            foreach ($team1Invalid as $value) {

                $team1            = $data;
                $team1['team1']   = $value;
                $testResultThird  = $this->finalmatch->add( $team1 );

                if( is_array($testResultThird) && isset($testResultThird['message'])) {

                    if ( !empty( $value ) ) {

                        $this->assertContains( $testResultThird['message'][0], 'The team1 field must contain a number greater than 0.', 'To verify team1 is invalid' );

                    } else {
                        $this->assertContains( $testResultThird['message'][0], 'The team1 field is required.', 'To verify team1 is invalid' );
                    }
                }
            }

            // To verify finale team2 is invalid
            $team1Invalid = array('', null, 0, -1);

            foreach ($team1Invalid as $value) {
                $team2           = $data;
                $team2['team2']  = $value;
                $testResultFourth= $this->finalmatch->add( $team2 );

                if( is_array($testResultFourth) && isset($testResultFourth['message'])) {

                    if ( !empty( $value ) ) {
                        $this->assertContains( $testResultFourth['message'][0], 'The team2 field must contain a number greater than 0.', 'To verify team2 is invalid' );
                    } else {
                        $this->assertContains( $testResultFourth['message'][0], 'The team2 field is required.', 'To verify team2 is invalid' );
                    }
                }
            }

            // To verify name is invalid
            $nameInvalid         = $data;
            $nameInvalid['name'] = '';
            $testResultFifth     = $this->finalmatch->add( $nameInvalid );

            if( is_array($testResultFifth) && isset($testResultFifth['message'])) {

                $this->assertContains( $testResultFifth['message'][0], 'The name field is required.', 'To verify name is invalid' );
            }

            // To verify add final is valid
            $testResultSixth = $this->finalmatch->add( $data );

            if ( is_object( $testResultSixth ) ) {

                // To verify categoryId return must equal category from input
                $this->assertEquals((int)$testResultSixth->finalCategoryId, $data['finalCategoryId'], 'To verify finalCategoryId return must equal category from input');

                // To verify name return must equal name from input
                $this->assertEquals($testResultSixth->name, $data['name'], 'To verify final return must equal name from input');

                // To verify team1 return must equal team1 from input
                $this->assertEquals((int)$testResultSixth->team1, $data['team1'], 'To verify team1 return must equal team1 from input');

                // To verify team2 return must equal team2 from input
                $this->assertEquals((int)$testResultSixth->team2, $data['team2'], 'To verify team2 return must equal team2 from input');

            } else {

                $this->assertTrue( FALSE, "Cant't verify add final in case valid." );
            }
        } else {

            $this->assertTrue( FALSE, "Can't add Final Match.");
        }
    }

    function testUpdateFinalMatch() {

        $final = $this->finalmatch->limit(1)->order_by('id', 'DESC')->get_all();

        if ( !empty( $final ) ) {

            $finalCategoryId = $final[0]->finalCategoryId;
            $teams           = $this->finalteam->limit(2, 0)->get_many_by(array('finalCategoryId' => $finalCategoryId, 'id !=' => $final[0]->team1, 'id !=' => $final[0]->team2 ));

            $data            = array(
                'team1'           => $teams[0]->id,
                'team2'           => $teams[1]->id,
                'name'            => 'semi1',
                'finalCategoryId' => $finalCategoryId,
                'parlayCardId'    => 1,
                'dateTime'        => date('m-d-Y H:i:s'),
                'endDate'         => date('m-d-Y H:i:s', strtotime('+ 1 day')),
                'team1Name'       => "TEAM1",
                'team2Name'       => "TEAM2",
                );
            $dataUpdate = array(
                'team1'           => 3,
                'team2'           => 4,
                'name'            => 'semi1',
                'finalCategoryId' => 4,
                'parlayCardId'    => 2,
                'team1Name'       => "TEAM3",
                'team2Name'       => "TEAM4",
                'dateTime'        => date('m-d-Y H:i:s'),
                'endDate'         => date('m-d-Y H:i:s', strtotime('+ 1 day')),
                );

            $id = $final[0]->id;

            //To verify udpate final is invalid
            //==============================
            // To verify data is empty
            $dataInvalid     = '';
            $testResultFirst = $this->finalmatch->edit( $id, $dataInvalid );

            if (is_array($testResultFirst) && isset($testResultFirst['message']) ) {

                $this->assertContains( $testResultFirst['message'][0], 'Please the required enter data', 'To verify data is empty');
            }
            // To verify finale id is invalid
            $idInvalid = array('', null, 0, -1);
            foreach ($idInvalid as $value) {

                $testResultSecond = $this->finalmatch->edit( $value, $dataUpdate );
                if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

                    $this->assertContains( $testResultSecond['message'][0], 'Id must to be a numeric and greater than zero', 'To verify id is invalid' );

                }
            }

            // To verify finale categoryId is invalid
            $categoryIdInvalid = array('', null, 0, -1);
            foreach ($categoryIdInvalid as $value) {

                $category               = $dataUpdate;
                $category['finalCategoryId'] = $value;
                $testResultSecond       = $this->finalmatch->edit( $id, $category );

                if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

                    if ( !empty( $value ) ) {
                        $this->assertContains( $testResultSecond['message'][0], 'The finalCategoryId field must contain a number greater than 0.', 'To verify categoryId is invalid' );
                    } else {

                        $this->assertContains( $testResultSecond['message'][0], 'The finalCategoryId field is required.', 'To verify categoryId is invalid' );
                    }
                }
            }

            // To verify finale team1 is invalid
            $team1Invalid = array('', null, 0, -1);
            foreach ($team1Invalid as $value) {

                $team1            = $dataUpdate;
                $team1['team1']   = $value;
                $testResultThird = $this->finalmatch->edit( $id, $team1 );

                if( is_array($testResultThird) && isset($testResultThird['message'])) {

                    if ( !empty( $value ) ) {
                        $this->assertContains( $testResultThird['message'][0], 'The team1 field must contain a number greater than 0.', 'To verify team1 is invalid' );
                    } else {
                        $this->assertContains( $testResultThird['message'][0], 'The team1 field is required.', 'To verify team1 is invalid' );
                    }
                }
            }

            // To verify finale team2 is invalid
            $team1Invalid = array('', null, 0, -1);
            foreach ($team1Invalid as $value) {
                $team2           = $dataUpdate;
                $team2['team2']  = $value;
                $testResultFourth= $this->finalmatch->edit( $id, $team2 );
                if( is_array($testResultFourth) && isset($testResultFourth['message'])) {

                    if ( !empty( $value ) ) {
                        $this->assertContains( $testResultFourth['message'][0], 'The team2 field must contain a number greater than 0.', 'To verify team2 is invalid' );
                    } else {
                        $this->assertContains( $testResultFourth['message'][0], 'The team2 field is required.', 'To verify team2 is invalid' );
                    }
                }
            }

            // To verify name is invalid
            $nameInvalid         = $dataUpdate;
            $nameInvalid['name'] = '';
            $testResultFifth     = $this->finalmatch->edit( $id, $nameInvalid );

            if( is_array($testResultFifth) && isset($testResultFifth['message'])) {

                $this->assertContains( $testResultFifth['message'][0], 'The name field is required.', 'To verify name is invalid' );
            }

            // To verify update final is valid
            $testResultSixth = $this->finalmatch->edit( $id, $dataUpdate );

            if ( is_object( $testResultSixth ) ) {

                // To verify id return must equal category from input
                $this->assertEquals($testResultSixth->id, $id , 'To verify id return must equal id from input');

                // To verify categoryId return must equal category from input
                $this->assertEquals((int)$testResultSixth->finalCategoryId, $dataUpdate['finalCategoryId'], 'To verify finalCategoryId return must equal category from input');

                // To verify name return must equal name from input
                $this->assertEquals($testResultSixth->name, $dataUpdate['name'], 'To verify final return must equal name from input');

                // To verify team1 return must equal team1 from input
                $this->assertEquals((int)$testResultSixth->team1, $dataUpdate['team1'], 'To verify team1 return must equal team1 from input');

                // To verify team2 return must equal team2 from input
                $this->assertEquals((int)$testResultSixth->team2, $dataUpdate['team2'], 'To verify team2 return must equal team2 from input');

            } else {

                $this->assertTrue( FALSE, "Cant't verify update final in case valid.");
            }
        } else {

            $this->assertTrue( FALSE, "Cant't verify update final in case valid." );
        }

    }

    function testDeleteFinal() {

        $final = $this->finalmatch->limit(1)->order_by('id', 'DESC')->get_all();

        if ( !empty($final) ) {

            $id = $final[0]->id;
            // To verify delete final is invalid
            // ===================================
            
            // To verify finale id is invalid
            $idInvalid = array('', null, 0, -1, 'abc' );
            foreach ($idInvalid as $value) {

                $testResultFirst = $this->finalmatch->destroy( $value );

                if( is_array($testResultFirst) && isset($testResultFirst['message'])) {

                    $this->assertContains( $testResultFirst['message'], 'Id must be a numeric and greater than zero', 'To verify delete final is invalid', 'To verify id is invalid' );

                }   
            }

            // To verify delete final is valid
            // ===================================
            $testResultSecond = $this->finalmatch->destroy( $id );

            if ( is_array($testResultSecond) && isset($testResultSecond['statusCode']) && $testResultSecond['statusCode'] == 204) {

                $resultExpect = ($this->finalmatch->getById($id));

                // To verify content return is null
                $this->assertEmpty( $testResultSecond[0], 'To verify content return is null' ); 
                
                // To verify statuscde return is 204
                $this->assertEquals( $testResultSecond['statusCode'], 204, 'To verify statuscde return is 204' );

                // To verify id not exist in database when delete
                $this->assertEquals( $resultExpect['message'], 'Final Match Not Found', 'To verify id not exist in database when delete' );
            } 
            else {
                
                $this->assertTrue( FALSE, "Cant't verify delete final in case valid.");
            }

        } else {

                $this->assertTrue( FALSE, "Cant't verify delete final in case valid.");
        }
    }

    public function addFinal( $bool ) {

        // truncate table
        $this->CI->db->query('SET FOREIGN_KEY_CHECKS=0;');
        $this->CI->db->query('TRUNCATE FinalMatches;');
        $this->CI->db->query('TRUNCATE FinalPlayerCards;');
        $this->CI->db->query('TRUNCATE FinalPlayerPicks;');
        $this->CI->db->query('TRUNCATE FinalResults;');
        $this->CI->db->query('SET FOREIGN_KEY_CHECKS=1;');
        $this->CI->player->memcacheInstance->flush();

        $final = $this->CI->db->query('SELECT finalCategoryId, MAX(counted) as count FROM
            (
            SELECT finalCategoryId ,  COUNT(*) AS counted
            FROM FinalTeams
            GROUP BY finalCategoryId
            ) as counts;')->result();

        if( !empty($final) && $final[0]->count > 4 ) {

            $finalCategoryId = $final[0]->finalCategoryId;
            $teams           = $this->finalteam->limit(4,0)->get_many_by( 'finalCategoryId', $finalCategoryId );

            $dataSemi1  = array(
                'team1'           => $teams[0]->id,
                'team2'           => $teams[1]->id,
                'name'            => 'semi1',
                'finalCategoryId' => $finalCategoryId,
                'parlayCardId'    => 1,
                'dateTime'        => date('m-d-Y H:i:s', strtotime('+1 day')),
                'team1Name'       => "TEAM1",
                'team2Name'       => "TEAM2",
            );

            $dataSemi2  = array(
                'team1'           => $teams[2]->id,
                'team2'           => $teams[3]->id,
                'name'            => 'semi2',
                'finalCategoryId' => $finalCategoryId,
                'parlayCardId'    => 1,
                'dateTime'        => date('m-d-Y H:i:s', strtotime('+1 day')),
                'team1Name'       => "TEAM3",
                'team2Name'       => "TEAM4",
            );

            $this->finalmatch->add( $dataSemi1 );
            $this->finalmatch->add( $dataSemi2 );

            $dataFinals = $this->finalmatch->getByParlay( $finalCategoryId );

            if ( !empty($dataFinals) && $dataFinals['code'] == 0 && $bool == FALSE ) {

                $listIds = array();

                foreach ($dataFinals['matches'] as $dataFinal ) {
                    $name             = $dataFinal->name;
                    $listIds["$name"] = $dataFinal->id;
                }

                return $listIds; 

            } elseif ( !empty($dataFinals) && $dataFinals['code'] == 0 && $bool == TRUE ) {

               $listIds = array();

                foreach ($dataFinals['matches'] as $dataFinal ) {
                    $name = $dataFinal->name;
                    $listIds["$name"] = $dataFinal->id;
                }

                $data = array(
                    'finalMatchId' => '',
                    'score1' => 40,
                    'score2' => 50
                    );

                $data['finalMatchId'] = $listIds['semi1'];
                $semi1 = $this->finalresult->add( $data );

                $data['finalMatchId'] = $listIds['semi2'];
                $semi2 = $this->finalresult->add( $data );
                
                $data['finalMatchId'] = $listIds['final'];
                $final = $this->finalresult->add( $data );

                if ( $semi1->code === 0 && $semi2->code === 0 && $final->code === 0 ) {

                    return $listIds;

                } else {

                    $this->assertTrue( FALSE, "Can't add result");
                }

            } else {

                $this->assertTrue( FALSE, "Cant't add Finale Game.");

            }
        }
        else {

            $this->assertTrue( FALSE, "Cant't add Finale Game.");
        }

    }

    // function testAddResult() {

    //     $ids = $this->addFinal( FALSE );

    //     if ( sizeof($ids) == 3 ) {


    //         $data = array(
    //             'finalMatchId' => 1,
    //             'score1'       => 44,
    //             'score2'       => 41,
    //             );

    //         // To verify add result Game Final is invalid
    //         // ================================
    //         // To verify data is empty
    //         $dataEmpty       = '';
    //         $testResultFirst = $this->finalresult->add($dataEmpty);

    //         if ( is_array( $testResultFirst ) && isset( $testResultFirst['message'] ) ) {

    //             // To verify data is empty
    //             $this->assertContains( $testResultFirst['message'], 'Please enter the required data', 'To verify data is empty' );
    //         }

    //         // To verify finalMatchId is invalid
    //         $idInvalid = array('', NULL, 'abc', 0, -1);
    //         foreach ($idInvalid as $key => $value) {

    //             $dataIdInvalid                 = $data;
    //             $dataIdInvalid['finalMatchId'] = $value;
    //             $testResultSecond              = $this->finalresult->add( $dataIdInvalid );

    //             if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

    //                 if ( !empty( $value ) ) {

    //                     $this->assertContains( $testResultSecond['message'][0], 'The final Match Id field must contain a number greater than 0.', 'To verify finalMatchId is invalid' );
    //                 } else {
    //                     $this->assertContains( $testResultSecond['message'][0], 'The final Match Id field is required.', 'To verify finalMatchId is invalid' );
    //                 }
    //             }
    //         }

    //         // To verify score1 is invalid
    //         $dataScore1 = $data;
    //         $score1     = array('', NULL, 'abc', 0, -1);
    //         foreach ($score1 as $key => $value) {

    //             $dataScore1['score1'] = $value;
    //             $testResultThird      = $this->finalresult->add( $dataScore1 );

    //             if( is_array($testResultThird) && isset($testResultThird['message'])) {

    //                 if ( !empty( $value ) ) {

    //                     $this->assertContains( $testResultThird['message'][0], 'The score1 field must contain a number greater than 0.', 'To verify score1 is invalid' );
    //                 } else {

    //                     $this->assertContains( $testResultThird['message'][0], 'The score1 field is required.', 'To verify score1 is invalid' );
    //                 }
    //             }
    //         }

    //         // To verify score2 is invalid
    //         $dataScore2 = $data;
    //         $score2     = array('', NULL, 'abc', 0, -1);
    //         foreach ($score2 as $key => $value) {

    //             $dataScore2['score2'] = $value;
    //             $testResultFourth     = $this->finalresult->add( $dataScore2 );

    //             if( is_array($testResultFourth) && isset($testResultFourth['message'])) {

    //                 if ( !empty( $value ) ) {

    //                     $this->assertContains( $testResultFourth['message'][0], 'The score2 field must contain a number greater than 0.', 'To verify score2 is invalid' );
    //                 } else {

    //                     $this->assertContains( $testResultFourth['message'][0], 'The score2 field is required.', 'To verify score2 is invalid' );
    //                 }
    //             }
    //         }

    //         // To verify finalMatchId is exits
    //         $finalExit = $this->finalresult->limit(1)->get_all();

    //         if ( !empty( $finalExit ) ) {

    //             $finalId                  = $finalExit[0]->finalMatchId;
    //             $dataExist                = $data;
    //             $dataExist['finalMatchId']= $finalId;
    //             $testResultFifth          = $this->finalresult->add( $dataExist );


    //             if ( is_array( $testResultFifth ) && isset( $testResultFifth['message'] ) ) {

    //                 $this->assertContains( $testResultFifth['message'], $this->CI->db->_error_message(), 'To verify finalMatchId is exits' );
    //             }
    //         }

    //         // To verify add result Game Final is valid
    //         // ================================
    //         $dataSemi1                = $data;
    //         $dataSemi1['finalMatchId']= $ids['semi1'];
    //         $testResultFifth          = $this->finalresult->add( $dataSemi1 );

    //         if( is_object( $testResultFifth ) ) {

    //             // To verify finalMatchId return must be equal id input
    //             $this->assertEquals($testResultFifth->finalMatchId, $ids['semi1'], 'To verify finalMatchId return must be equal id input' );

    //             // To verify score1 return must be equal score1 input
    //             $this->assertEquals($testResultFifth->score1, $dataSemi1['score1'], 'To verify score1 return must be equal score1 input' );

    //             // To verify score2 return must be equal score2 input
    //             $this->assertEquals($testResultFifth->score2, $dataSemi1['score2'], 'To verify score2 return must be equal score2 input' );

    //             // To verify winner must be is 2
    //             $this->assertEquals((int)$testResultFifth->winner, 1 , 'To verify winner must be is 2' );
    //         } else {

    //             $this->assertTrue( FALSE, "Can't verify Add results in case valid");
    //         }

    //     } else {

    //         $this->assertTrue( FALSE, "Can't verify Add results. Final Game is empty");
    //     }

    // }

    // function testUpdateResult() {

    //     $final = $this->finalresult->order_by('finalMatchId', 'DESC')->get_by( array('winner !=' => 0));
        
    //     if( !empty( $final ) ) {
    //         $id             = $final->finalMatchId;
    //         $winnerExpected = $final->winner;
    //         $scoreExpected1 = $final->score1;
    //         $scoreExpected2 = $final->score2;
    //         $dataUpdate     = array(
    //             'score1'    => $scoreExpected2,
    //             'score2'    => $scoreExpected1
    //         );
    //         // To verify update result Game Final is invalid
    //         // ================================
    //         // To verify final Id input return invalid
    //         $idInvalid = array('', null, 0, -1, 'abc');

    //         foreach ($idInvalid as $key => $value) {
                
    //             $testResultFirst = $this->finalresult->edit( $value ,$dataUpdate );
    //             if (is_array($testResultFirst) && isset( $testResultFirst['message'])) {


    //                 $this->assertContains( $testResultFirst['message'], 'Id must to be a numeric and greater than zero', 'To verify update  is invalid', 'To verify input data is invalid' );
    //             } 
    //         }

    //         // To verify data is empty     
    //         $testResultSecond = $this->finalresult->edit( $id , '' );
    //         if (is_array($testResultSecond) && isset( $testResultSecond['message'])) {

    //             $this->assertContains( $testResultSecond['message'], 'Please enter the required data', 'To verify update final result is invalid', 'To verify data is empty ' );
    //         }

    //         // To verify score1 is invalid
    //         $dataScore1 = $dataUpdate;
    //         $score1     = array('', NULL, 'abc', 0, -1);
    //         foreach ($score1 as $key => $value) {
                    
    //             $dataScore1['score1'] = $value; 
    //             $testResultThird = $this->finalresult->edit( $id, $dataScore1 );
         
    //             if( is_array($testResultThird) && isset($testResultThird['message'])) {

    //                 if ( !empty( $value ) ) {

    //                     $this->assertContains( $testResultThird['message'][0], 'The score1 field must contain a number greater than 0.', 'To verify score1 is invalid' );
    //                 } else {

    //                     $this->assertContains( $testResultThird['message'][0], 'The score1 field is required.', 'To verify score1 is invalid' );
    //                 }
    //             }  
    //         } 

    //         // To verify score2 is invalid
    //         $dataScore2 = $dataUpdate;
    //         $score2 = array('', NULL, 'abc', 0, -1);
    //         foreach ($score2 as $key => $value) {
                    
    //             $dataScore2['score2'] = $value; 
    //             $testResultFourth = $this->finalresult->edit( $id, $dataScore2 );
         
    //             if( is_array($testResultFourth) && isset($testResultFourth['message'])) {

    //                 if ( !empty( $value ) ) {

    //                     $this->assertContains( $testResultFourth['message'][0], 'The score2 field must contain a number greater than 0.', 'To verify score2 is invalid' );
    //                 } else {

    //                     $this->assertContains( $testResultFourth['message'][0], 'The score2 field is required.', 'To verify score2 is invalid' );
    //                 }
    //             }  
    //         } 

    //         // To verify update result Game Final is valid
    //         // ================================

    //         $testResultFifth = $this->finalresult->edit( $id, $dataUpdate );

    //         if ( is_object($testResultFifth) ) {

    //             // To verify finalMatchId return must be equal id input
    //             $this->assertEquals($testResultFifth->finalMatchId, $id, 'To verify finalMatchId return must be equal id input' );
                
    //             // To verify score1 return must be equal score1 input
    //             $this->assertEquals($testResultFifth->score1, $dataUpdate['score1'], 'To verify score1 return must be equal score1 input' );
                
    //             // To verify score2 return must be equal score2 input
    //             $this->assertEquals($testResultFifth->score2, $dataUpdate['score2'], 'To verify score2 return must be equal score2 input' );

    //             // To verify winner must be is 2
    //             $this->assertTrue( ($testResultFifth->winner != $winnerExpected), 'To verify winner return different winner first.' );

    //         } else {

    //             $this->assertTrue( FALSE, "Can't verify Update results. Final Game Result is empty.");  
    //         }
    //     } else {

    //         $this->assertTrue(FALSE , "Can't verify Update results. Final Game Result is empty.");   
    //     }
    // }

    // function testGetResultByCategory() {

    //     $final = $this->finalresult->order_by('finalMatchId', 'DESC')->get_by( array('winner !=' => 0));

    //     if ( !empty( $final ) ) {

    //         $finalId         = $final->finalMatchId;
    //         $finalMatch      = $this->finalmatch->getById( $finalId );
    //         $finalCategoryId = $finalMatch->finalCategoryId;
    //         $resultsExpected = $this->CI->db->select( 'FinalResults.*' )
    //             ->join( 'FinalMatches', 'FinalMatches.id = FinalResults.finalMatchId' )
    //             ->where( 'finalCategoryId', $finalCategoryId )
    //             ->get( 'FinalResults' )
    //             ->result();

    //         // To verify get byCategory result Game Final is invalid
    //         // ================================
    //         // To verify categoryId input is invalid
    //         $idInvalid = array('', null, 0, -1, 'abc');

    //         foreach ($idInvalid as $key => $value) {
                
    //             $testResultFirst = $this->finalresult->getByCategoryId( $value );
    //             if (is_array($testResultFirst) && isset( $testResultFirst['message'])) {

    //                 $this->assertContains( $testResultFirst['message'], 'Category Id must to be is numuric and greater than zero', 'To verify categoryId invalid' );
    //             } 
    //         }
            
    //         // To verify get byCategory result Game Final is valid
    //         // ================================
    //         $testResultSecond = $this->finalresult->getByCategoryId($finalCategoryId);
    //         if ( isset($testResultSecond['code']) && $testResultSecond['code'] == 0 ) {

    //             $results = !empty($testResultSecond['results']) ? $testResultSecond['results'] : array();
    //             foreach ($results as $key => $result) {

    //                 if ( array_key_exists($key , $resultsExpected ) ) {

    //                     // To verify finalMatchId return must be finalMatchId from database
    //                     $this->assertEquals($results["$key"]->finalMatchId, $resultsExpected["$key"]->finalMatchId, 'To verify finalMatchId return must be finalMatchId from database');  
                         
    //                     // To verify score1 return must be score1 from database
    //                     $this->assertEquals($results["$key"]->score1, $resultsExpected["$key"]->score1, 'To verify score1 return must be score1 from database'); 

    //                     // To verify score2 return must be score2 from database
    //                     $this->assertEquals($results["$key"]->score2, $resultsExpected["$key"]->score2, 'To verify score2 return must be score2 from database'); 

    //                     // To verify winner return must be winner from database
    //                     $this->assertEquals($results["$key"]->winner, $resultsExpected["$key"]->winner, 'To verify winner return must be winner from database'); 
    //                 }
    //             }
    //         } else {

    //             $this->assertTrue( FASLE, "Can't verify get results by categoryId in case invalid."); 
    //         }
    //     } else {

    //         $this->assertTrue( FALSE, "Can't verify get results by categoryId. Final Game Result is empty.");
            
    //     } 
    // }

    // function testSavePlayerCards() {

    //     $id = $this->addFinal( TRUE );

    //     $final = $this->finalmatch->order_by('finalCategoryId', 'DESC')->get_by( array('finalCategoryId !=' => 0));

    //     if ( !empty($final) ) {
    //         $finalCategoryId = $final->finalCategoryId;
    //         $dataExpected = array(
    //             'playerId' => 1,
    //             'finalCategoryId' => $finalCategoryId,
    //             'team1' => 1,
    //             'team2' => 2,
    //             'team3' => 3,
    //             'team4' => 4,
    //             'team5' => 1,
    //             'team6' => 4,
    //             'score1' => rand(21,40),
    //             'score2' => rand(11,20),
    //             'score3' => rand(25,35),
    //             'score4' => rand(36,46),
    //             'score5' => rand(30,40),
    //             'score6' => rand(10,23)
    //         );

    //         // To verify save player cards result Game Final is invalid
    //         // ================================
            
    //         // To verify data is empty     
    //         $testResultFirst = $this->finalplayercard->add( '' );
    //         if (is_array($testResultFirst) && isset( $testResultFirst['message'])) {

    //             $this->assertContains( $testResultFirst['message'], 'Please enter the required data', 'To verify save player cards result Game Final is invalid', 'To verify data is empty ' );
    //         } 

    //         // To verify playerId invalid
    //         $playerIdInvalid = array('', NULL, 0, -1, 'abc');
    //         foreach ($playerIdInvalid as $key => $value) {
    //             $dataIdInvalid = $dataExpected;
    //             $dataIdInvalid['playerId'] = $value;

    //             $testResultSecond = $this->finalplayercard->add( $dataIdInvalid );

    //             if (is_array($testResultSecond) && isset( $testResultSecond['message'])) {

    //                 if ( empty($value) ) {

    //                     $this->assertContains( $testResultSecond['message'][0], 'The playerId field is required.', 'To verify playerId is invalid' );
    //                 } else {

    //                     $this->assertContains( $testResultSecond['message'][0], 'The playerId field must contain a number greater than 0.', 'To verify playerId is invalid' );
    //                 }
    //             } 
    //         }

    //         // To verify finalCategoryId invalid
    //         $finalCategoryIdInvalid = array('', NULL, 0, -1, 'abc');

    //         foreach ($finalCategoryIdInvalid as $key => $value) {
    //             $dataIdInvalid = $dataExpected;
    //             $dataIdInvalid['finalCategoryId'] = $value;

    //             $testResultThird = $this->finalplayercard->add( $dataIdInvalid );

    //             if (is_array($testResultThird) && isset( $testResultThird['message'])) {

    //                 if ( empty($value) ) {

    //                     $this->assertContains( $testResultThird['message'][0], 'The finalCategoryId field is required.', 'To verify finalCategoryId is invalid' );
    //                 } else {

    //                     $this->assertContains( $testResultThird['message'][0], 'The finalCategoryId field must contain a number greater than 0.', 'To verify finalCategoryId is invalid' );
    //                 }
    //             } 
    //         }

    //     } else {
           
    //        $this->assertTrue( FALSE, "Can't verify save card results by categoryId. Final Game is empty.");  
    //     }
    // }

}   