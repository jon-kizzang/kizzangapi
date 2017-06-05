<?php

/**
 * @group Model
 */
class EventModelTest extends CIUnit_TestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->CI->load->model(array('eventnotification'));
        $this->eventnotification = $this->CI->eventnotification;

        $this->eventnotification->executeTesting = TRUE;


    }

    public function tearDown()
    {
        parent::tearDown();
    }

    function testAddEvent() {

        $data = array(
        	'playerId' => 1,
            'type'     => 'sponsor wheel',
            'data'     => '{sponsorId: 1}',
            'pending'  => 1,
            'expireDate' =>  date('m-d-Y H:i:s', strtotime('+1 day'))
        );

        // To verfiy add event return is invalid
        // ======================================

        // To verify data must be not empty
        $dataInvalid     = '';
        $testResultFirst = $this->eventnotification->add( $dataInvalid, $data['playerId'] );

        if (is_array($testResultFirst) && isset($testResultFirst['message']) ) {

            $this->assertContains( $testResultFirst['message'], 'Please enter the required data', 'To verify data is empty');
        }
        
        // To vetify type must be required
        $typeInvalid         = $data;
        $typeInvalid['type'] = '';
        $testResultSecond     = $this->eventnotification->add( $typeInvalid, $data['playerId'] );

        if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

            $this->assertContains( $testResultSecond['message'][0], 'The type field is required.', 'To verify type is invalid' );
        }

        // To vetify data must be required
        $dataInvalid         = $data;
        $dataInvalid['data'] = '';
        $testResultThird     = $this->eventnotification->add( $dataInvalid, $data['playerId'] );

        if( is_array($testResultThird) && isset($testResultThird['message'])) {

            $this->assertContains( $testResultThird['message'][0], 'The data field is required.', 'To verify data is invalid' );
        }

        // To verify add eventnotification return is valid
        // ======================================
        
        $testResultFifth = $this->eventnotification->add( $data, $data['playerId'] );
        if( is_object($testResultFifth) ) {

            // To verify playerId return must be equal playerId input
            $this->assertEquals((int)$testResultFifth->playerId, $data['playerId'], "To verify playerId return must be equal playerId input");

            // To verify type return must be equal type input
            $this->assertEquals($testResultFifth->type, $data['type'], "To verify type return must be equal type input");
            
            // To verify data return must be equal data input
            $this->assertEquals($testResultFifth->data, $data['data'], "To verify data return must be equal data input");

            // To verify pending must be equal 1
            $this->assertEquals((int)$testResultFifth->pending, 1 , "To verify pending must be equal 1"); 

            // To verify added must be equal 1
            $this->assertNotNull($testResultFifth->added, "To verify added must be not null");

            // To verify updated must be equal 1
            $this->assertNull($testResultFifth->updated, "To verify updated must be equal null");

            // To verify playerActionTaken return must be equal playerActionTaken input
            $this->assertEquals($testResultFifth->playerActionTaken, 0, "To verify playerActionTaken return must be equal playerActionTaken input"); 
        } 

    }

    function testUpdateEvent() {

        $event = $this->eventnotification->order_by( 'id', 'DESC' )->get_by( array('playerId !=' => 0, 'pending !=' => 0) );

        if (is_object($event)) {

            $id = $event->id;
            $playerId = $event->playerId;
            $dataUpdate = array(

                'playerId' => $playerId,
                'type'     => 'update sponsor wheel',
                'data'     => '{sponsorId: 1}',
                'pending'  => 0,
            );

            // To verfiy update event return is invalid
            // ======================================
            
            // To verify data must be not empty
            $dataInvalid     = '';
            $testResultFirst = $this->eventnotification->edit( $id, $playerId, 'Player' ,$dataInvalid );

            if (is_array($testResultFirst) && isset($testResultFirst['message']) ) {

                $this->assertContains( $testResultFirst['message'], 'Please enter the required data', 'To verify data is empty');
            }

            // To verify id is invalid 
            $idInvalid = array('', null, 0, -1);

            foreach ($idInvalid as $value) {

                $testResultSecond = $this->eventnotification->edit( $value, $playerId, 'Player' , $dataUpdate );

                if( is_array($testResultSecond) && isset($testResultSecond['message'])) {

                    $this->assertContains( $testResultSecond['message'][0], 'Id must be a numeric and greater than zero', 'To verify id is invalid' );
                }
            }

            // To verify edit return msg is null
            $dataUpdateInvalid = array(
                'abc' => 'abc',
            );

            $testMsg = $this->eventnotification->edit($id, $playerId, 'Player' , $dataUpdateInvalid);

            if ( is_array($testMsg) && isset($testMsg['message']) ) {
                
                // To verify edit return msg is null
                $this->assertEquals(($testMsg['message']), "Please enter the required data", "To verify edit return msg is null");
            }
            
            // To vetify type must be required
            $typeInvalid         = $dataUpdate;
            $typeInvalid['type'] = '';
            $testResultThird     = $this->eventnotification->edit( $id,  $playerId, 'Player' ,$typeInvalid );
            
            if( is_array($testResultThird) && isset($testResultThird['message'])) {

                $this->assertContains( $testResultThird['message'][0], 'The type field is required.', 'To verify type is invalid' );
            }

            // To vetify data must be required
            $dataInvalid          = $dataUpdate;
            $dataInvalid['data']  = '';
            $testResultFourth     = $this->eventnotification->edit( $id,  $playerId, 'Player' ,$dataInvalid );

            if( is_array($testResultFourth) && isset($testResultFourth['message'])) {

                $this->assertContains( $testResultFourth['message'][0], 'The data field is required.', 'To verify data is invalid' );
            }
            
            // To verify only edit event notification where pending = 1
            $eventInvalid = $this->eventnotification->get_by(array('pending' => 0));

            if( is_object($eventInvalid) ) {

                $pendingInvalid       = $dataUpdate;
                $resultPendingInvalid = $this->eventnotification->edit($eventInvalid->id, $playerId, 'Player', $pendingInvalid);
                
                if ($resultPendingInvalid['code'] === 7) {

                    $this->assertContains( $resultPendingInvalid['message'], "The pending action cannot be edited", "To verify only edit event notification where pending = 1");
                }

            }

            // To verify Player is Not authorized
            $playerInvalid = array('', NULL , 0, -1, 'abc');

            foreach ($playerInvalid as $value) {
                
                $testPlayerInvalid = $this->eventnotification->edit( $id, $value, 'Player', $dataUpdate);

                if( $testPlayerInvalid['code'] === 6) {

                    $this->assertContains( $testPlayerInvalid['message'], "Not authorized", "To verify Player is Not authorized");
                }
            }

            // To verify update event return is valid
            // ======================================
            $testResultFifth = $this->eventnotification->edit( $id,  $playerId, 'Player' ,$dataUpdate );
            if( is_object($testResultFifth) ) {

                // To verify id return must be equal id input
                $this->assertEquals($id, $testResultFifth->id, "To verify id return must be equal id input");

                // To verify playerId return must be equal playerId input
                $this->assertEquals($dataUpdate['playerId'], $testResultFifth->playerId, "To verify playerId return must be equal playerId input");

                // To verify type return must be equal type input
                $this->assertEquals($dataUpdate['type'], $testResultFifth->type, "To verify type return must be equal type input");
                
                // To verify data return must be equal data input
                $this->assertEquals($dataUpdate['data'], $testResultFifth->data, "To verify data return must be equal data input");

                // To verify playerActionTaken return must be equal playerActionTaken default 0
                $this->assertEquals((int)$testResultFifth->playerActionTaken, 0, "To verify playerActionTaken return must be equal value default");

                // To verify updated return must be equal updated input
                $this->assertNotNull( $testResultFifth->updated, "To verify updated return must be not null");


            } else {

                $this->assertTrue( FALSE, $testResultFifth['message']);
            }

        } else {

            $this->assertTrue( FALSE, "Can't verify update. Event is empty. ");
        }
    }

    function testGetEventById() {

        $event = $this->eventnotification->order_by( 'id', 'DESC' )->get_by( array('id !=' => 0) );
        
        if ( is_object( $event ) ) {

            $id = $event->id;

            // To verfiy get event by id return is invalid
            // ======================================
            // To verify id is invalid
            $idInvalid = array('', null, 0, -1);

            foreach ($idInvalid as $value) {

                $testResultFirst = $this->eventnotification->getById( $value );

                if( is_array($testResultFirst) && isset($testResultFirst['message'])) {

                    $this->assertContains( $testResultFirst['message'][0], 'Id must be a numeric and greater than zero', 'To verify id is invalid' );
                }
            }

            // To verify get event by id return is valid
            // ======================================
            $testResultSecond = $this->eventnotification->getById( $id );

            if( is_object($testResultSecond) ) {

                // To verify id return must be equal id input
                $this->assertEquals($id, $testResultSecond->id, "To verify id return must be equal id input");
                
                // To verify type return must be equal type input
                $this->assertEquals($event->type, $testResultSecond->type, "To verify type return must be equal type input");
                
                // To verify data return must be equal data input
                $this->assertEquals($event->data, $testResultSecond->data, "To verify data return must be equal data input");

            } else {

                $this->assertTrue( FALSE, $testResultSecond['message']);
            }

        } else {
            
            $this->assertTrue( FALSE, "Can't verify update. Event is empty. ");

        }
    }

    function testGetAllEvent() {

        // To verify get all events return is valid
        // ======================================
        $testResultFirst = $this->eventnotification->getAll();

        if( $testResultFirst['code'] == 0 ) {

            // To verify all event return pending must be equal 0
            
            foreach ($testResultFirst['eventNotifications'] as $value) {
                
                $this->assertEquals( (int)$value->pending, 0, "To verify all event return pending must be equal 0");
            }
        } elseif( $testResultFirst['code'] == 1 )   {

            $this->assertTrue( FALSE, $testResultFirst['message']);
        }
    }

    function testDeleteEvent() {

        $getenvet = $this->eventnotification->order_by( 'id','DESC' )->get_by(array('id !=' => 0));

        if( !empty($getevent) ) {
            
            // To verify delete event is invalid
            //===============================
            $id = $getevent->id;

            // To verify id is invalid
            $idInvalid = array('', null, 0, -1);

            foreach ($idInvalid as $value) {

                $testResultFirst = $this->eventnotification->destroy( $value );

                if( is_array($testResultFirst) && isset($testResultFirst['message'])) {

                    $this->assertContains( $testResultFirst['message'][0], 'Id must be a numeric and greater than zero', 'To verify id is invalid' );
                }
            }

            // To verify delete event is valid
            //===============================
            $testResultSecond = $this->eventnotification->destroy( $id );

            if( $testResultSecond['statusCode'] == 204) {

                $this->assertEmpty( $testResultSecond[0], 'To verify return no content when delete event' );
            }
        }
    }
}
