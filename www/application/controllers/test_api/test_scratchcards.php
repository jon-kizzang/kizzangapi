<?php
/**
 * Testing scratchcards
 *
 */
class test_scratchcards extends CI_Controller {

	function __construct() {
		parent::__construct();

		// loading model scratchcards
		$this->load->model('scratchcard');

		//loading library unit test
		$this->load->library('unit_test');

		// loading database test
		$this->load->database('scratchcardsdb', TRUE);

		//To enable strict mode
		$this->unit->use_strict(TRUE);

		// Disable database debugging so we can test all units without stopping
		// at the first SQL error
		$this->db->db_debug = FALSE;

	}

	function testGetAll() {

		$limit = 10;
		$offset = 0;

		// To verify get all scratchcard is invalid

		// To verify get all scratchcard is valid
		$testResultFirst = $this->scratchcard->getAll( $limit, $offset);
		if (is_array($testResultFirst) && isset($testResultFirst['game_list'])) {

			// To verify offset return must be equal offset input
			$this->unit->run($testResultFirst['offset'], $offset, 'To verify get all scratchcard is valid', 'To verify offset return must be equal offset input');

			// To verify limit return must be equal limit input
			$this->unit->run($testResultFirst['limit'], $limit, 'To verify get all scratchcard is valid', 'To verify limit return must be equal limit input');

			if (!empty($testResultFirst['game_list'])) {

				$games = $testResultFirst['game_list'];

				foreach ($games as $key => $game) {

					// To verify all game return DeployMobile must be equal 1
					$this->unit->run((int) $game->DeployMobile, 1, 'To verify get all scratchcard is valid', ' To verify all game return DeployMobile must be equal 1');
				}

			}
		}

		echo $this->unit->report();
		echo $this->returnResult($this->unit->result());

	}

	function testGetByIdScratchCard() {
		$count = $this->scratchcard->count_by('DeployMobile', 1);

		// To verify get game by id is invalid
		// ===================================

		if ($count > 0) {

			// To verify id input is invalid
			$idInvalid = array('abc', null, -1, 0);

			foreach ($idInvalid as $key => $value) {
				$testResultFirst = $this->scratchcard->getById($value);

				if (is_array($testResultFirst) && isset($testResultFirst['message'])) {

					$this->unit->run($testResultFirst['message'], 'Id must is a numeric and greater than zero', 'To verify get game by id is invalid', 'To verify id input is invalid');
				}
			}

			// To verify get game by id is valid
			$scratchcardExpected = $this->scratchcard->get_by('DeployMobile', 1);
			$id = $scratchcardExpected->ID;

			$testResultSecond = $this->scratchcard->getById($id);

			if (is_object($testResultSecond)) {

				// To verify id return must be equal id input
				$this->unit->run($testResultSecond->ID, $id, 'To verify get game by id is valid', 'To verify id return must be equal id input');

				// To verify all info return must be equal info expected
                $this->unit->run($testResultSecond->TotalWinningCards, $scratchcardExpected->TotalWinningCards, 'To verify get game by id is valid', 'To verify TotalWinningCards return must be equal TotalWinningCards input');

                $this->unit->run($testResultSecond->PlayInterval, $scratchcardExpected->PlayInterval, 'To verify get game by id is valid', 'To verify PlayInterval return must be equal PlayInterval input');

                $this->unit->run($testResultSecond->SpotsOnCard, $scratchcardExpected->SpotsOnCard, 'To verify get game by id is valid', 'To verify SpotsOnCard return must be equal SpotsOnCard input');

                $this->unit->run($testResultSecond->EndDate, $scratchcardExpected->EndDate, 'To verify get game by id is valid', 'To verify EndDate return must be equal EndDate input');

                $this->unit->run($testResultSecond->WinningSpots, $scratchcardExpected->WinningSpots, 'To verify get game by id is valid', 'To verify WinningSpots return must be equal WinningSpots input');

                $this->unit->run($testResultSecond->SerialNumber, $scratchcardExpected->SerialNumber, 'To verify get game by id is valid', 'To verify SerialNumber return must be equal SerialNumber input');

                $this->unit->run($testResultSecond->CardIncrement, $scratchcardExpected->CardIncrement, 'To verify get game by id is valid', 'To verify CardIncrement return must be equal CardIncrement input');

                $this->unit->run($testResultSecond->WinningCardIncrement, $scratchcardExpected->WinningCardIncrement, 'To verify get game by id is valid', 'To verify WinningCardIncrement return must be equal WinningCardIncrement input');

                $this->unit->run($testResultSecond->WinAmount, $scratchcardExpected->WinAmount, 'To verify get game by id is valid', 'To verify WinAmount return must be equal WinAmount input');

                $this->unit->run($testResultSecond->Card_Count, $scratchcardExpected->Card_Count, 'To verify get game by id is valid', 'To verify Card_Count return must be equal Card_Count input');

                $this->unit->run($testResultSecond->Win_Count, $scratchcardExpected->Win_Count, 'To verify get game by id is valid', 'To verify Win_Count return must be equal Win_Count input');

                $this->unit->run($testResultSecond->Name, $scratchcardExpected->Name, 'To verify get game by id is valid', 'To verify Name return must be equal Name input');

                $this->unit->run($testResultSecond->DeployWeb, $scratchcardExpected->DeployWeb, 'To verify get game by id is valid', 'To verify DeployWeb return must be equal DeployWeb input');

                $this->unit->run($testResultSecond->DeployMobile, $scratchcardExpected->DeployMobile, 'To verify get game by id is valid', 'To verify DeployMobile return must be equal DeployMobile input');
                $this->unit->run($testResultSecond->PayoutID, $scratchcardExpected->PayoutID, 'To verify get game by id is valid', 'To verify PayoutID return must be equal PayoutID input');

			}

		} else {

			echo "<h4 style='color:red;' > Can't verify get game by Id. Database is empty or had been delete.</h4>";
		}

		echo $this->unit->report();
		echo $this->returnResult($this->unit->result());
	}

	function testUpdateScratchCard() {
        $dataUpdate = array(
            'TotalCards'   => 10,
            'SerialNumber' => 'KZ111111',
            'WinAmount'    => '12.00'
            );

		$count = $this->scratchcard->count_by('DeployMobile', 1);

		if ($count > 0) {

			// To verify update game is invalid
            // To verify id input is invalid
            $idInvalid = array('abc', null, -1, 0);

            foreach ($idInvalid as $key => $value) {

                $testResultFirst = $this->scratchcard->edit( $value, $dataUpdate );

                if (is_array($testResultFirst) && isset($testResultFirst['message'])) {

                    $this->unit->run($testResultFirst['message'], 'Id must is a numeric and greater than zero', 'To verify update game by id is invalid', 'To verify id input is invalid');
                }
            }

            $scratchcardExpected = $this->scratchcard->get_by('DeployMobile', 1);
            $id = $scratchcardExpected->ID;

            // To verify data is invalid 
            $dataInvalid = array();

            $testResultSecond = $this->scratchcard->edit( $id , $dataUpdate );
            if (is_array($testResultSecond) && isset($testResultSecond['error'])) {

                $this->unit->run($testResultSecond['error'], 'Please the required enter data', 'To verify update game by id is invalid', 'To verify id input is invalid');
            }

            // To verify TotalCards input invalid
            $totalCards = array( '', null, -1, 0, 'abc' );

            foreach ($totalCards as $key => $value) {
                
                $dataTotalCards['TotalCards'] = $value;
                $testResultThirst = $this->scratchcard->edit( $id, $dataTotalCards );
                    
                if (is_array($testResultThirst) && isset($testResultThirst['message'])) {

                    if ( empty( $value ) ) {

                        $this->unit->run( $testResultThirst['message'][0], 'The Total Cards field is required.', 'To verify update game by id is invalid', 'To verify id input is invalid');
                    } else {

                        $this->unit->run( $testResultThirst['message'][0], 'The Total Cards field must contain a number greater than 0.', 'The Total Cards field must contain a number greater than 0.', 'To verify id input is invalid');

                    }
                }
            }
            
            // To verify SerialNumber invalid
            $dataSerialNumber['SerialNumber'] = '';
            $testResultFourth = $this->scratchcard->edit( $id, $dataSerialNumber );
                
            if (is_array($testResultFourth) && isset($testResultFourth['message'])) {

                $this->unit->run( $testResultFourth['message'][0], 'The Serial Number field is required.', 'To verify update game by id is invalid', 'To verify id input is invalid');
            }

            // To verify WinAmount invalid
            $WinAmountInvalid = array( 'abc', 2 );

            foreach ( $WinAmountInvalid as $value ) {
                $WinAmount['WinAmount'] = $value;
                $testWinAmountInvalid = $this->scratchcard->edit( $id, $WinAmount );

                if ( is_array( $testWinAmountInvalid ) && isset( $testWinAmountInvalid['message'] )) {

                    // To verify WinAmount is invalid
                    $this->unit->run( $testWinAmountInvalid['message'][0], 'The Win Amount field is not in the correct format.', 'To verify WinAmount is invalid', 'To verify update scratchcard is invalid' );
                }     
            }

			// To verify update game is valid
            
            $testResultFifth = $this->scratchcard->edit( $id, $dataUpdate );

            if (is_object($testResultFifth)) {

                // To verify id return must be equal id input
                $this->unit->run($testResultFifth->ID, $id, 'To verify get game by id is valid', 'To verify id return must be equal id input');

                $this->unit->run($testResultFifth->SerialNumber, $dataUpdate['SerialNumber'], 'To verify get game by id is valid', 'To verify SerialNumber return must be equal SerialNumber input');

                $this->unit->run($testResultFifth->WinAmount, $dataUpdate['WinAmount'], 'To verify get game by id is valid', 'To verify WinAmount return must be equal WinAmount input');

                $this->unit->run((int)$testResultFifth->TotalCards, $dataUpdate['TotalCards'], 'To verify get game by id is valid', 'To verify WinAmount return must be equal WinAmount input');

            } else {

                echo "<h4 style='color:red;' > Can't verify  update Game in case valid.</h4>";
            }
		} else {

			echo "<h4 style='color:red;' > Can't verify Update game by Id. Database is empty or had been delete.</h4>";
		}

		echo $this->unit->report();
		echo $this->returnResult($this->unit->result());
	}

	function testDeleteScratchCard() {

		$count = $this->scratchcard->count_by('DeployMobile', 1);

		if ($count > 0) {

			// To verify delete game is invalid
            $idInvalid = array('abc', null, -1, 0);

            foreach ($idInvalid as $key => $value) {

                $testResultFirst = $this->scratchcard->destroy( $value );
                if (is_array($testResultFirst) && isset($testResultFirst['message'])) {

                    $this->unit->run($testResultFirst['message'], 'Id must is a numeric and greater than zero', 'To verify update game by id is invalid', 'To verify id input is invalid');
                }
            }

			// To verify delete game is valid
            $scratchcardExpected = $this->scratchcard->get_by('DeployMobile', 1);
            $id = $scratchcardExpected->ID;

            $testResultSecond = $this->scratchcard->destroy( $id );

            if ( is_array($testResultSecond) && $testResultSecond['statusCode'] == 204) {

                // To verify result return is null
                $this->unit->run($testResultSecond[0], 'is_null', 'To verify delete game is valid', 'To verify result return is null');

                
            } else {

                echo "<h4 style='color:red;' > Can't verify Delete game by Id in case valid.</h4>";                
            }

		} else {

			echo "<h4 style='color:red;' > Can't verify Delete game by Id. Database is empty or had been delete.</h4>";
		}

		echo $this->unit->report();
		echo $this->returnResult($this->unit->result());
	}

	/**
	 * returnResult
	 * @param  array $results
	 * @return string
	 */
	function returnResult($results) {
		$passed = [];
		$failed = [];
		foreach ($results as $value) {
			if ($value['Result'] === "Passed") {
				array_push($passed, $value['Result']);
			}

			if ($value['Result'] === "Failed") {
				array_push($failed, $value['Result']);
			}
		}
		return "<h1> Tests: " . sizeof($results) . ", Passed: " . sizeof($passed) . ", Failed:" . sizeof($failed) . "</h1>";
	}
}