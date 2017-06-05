<?php

/**
* 
*/
error_reporting(E_ALL);
ini_set('display_errors', 1);
/**
 * Testing mapstate 
 * 
 */
class test_mapstates extends CI_Controller {
  
    function __construct() {
        parent::__construct();

        // loading model map
        $this->load->model( 'map' );

        //loading library unit test
        $this->load->library( 'unit_test' );

        // loading database test
        $this->load->database( 'test', TRUE );

        //To enable strict mode 
        $this->unit->use_strict( TRUE );

        // Disable database debugging so we can test all units without stopping
        // at the first SQL error
        $this->db->db_debug = FALSE;
    }

    /**
     * Testing get maps state by start row and limit 
     * 
     */
    function testGetMapState() {

        // To verify get list map is invalid
        //======================================
        
        // To verify start input is invalid
        // To verify limit input is invalid
        $limitInvalid = array( 'abc', -1, NULL );
        $start = array( 0, 0 );
        foreach ($limitInvalid as $value) {
            
            $resultsInvalid = $this->map->mapPanelData( $start, $value );

            if ( is_array($resultsInvalid) && $resultsInvalid['statusCode'] === 400 ) {

                $this->unit->run($resultsInvalid['errors'], 'Not Found', 'To verify limit input is invalid');
            }

        }
        // To verify get list map state is valid
        //======================================
        $limit = 1;

        $testResultFirst = $this->map->mapPanelData( $start, $limit );
        if ( is_array( $testResultFirst ) && isset( $testResultFirst['segment'] ) ) {

            // limit return must be equal limit input 
            $this->unit->run( $testResultFirst['limit'], $limit , "To verify get list map state is valid", "limit return must be equal limit input");

            $this->unit->run( sizeof($testResultFirst['segment']), $limit , "To verify get list map state is valid", "count map result return must be equal limit input" );
        }

        $limit = 10;

        $testResultFirst = $this->map->mapPanelData( $start, $limit );

        if ( is_array( $testResultFirst ) && isset( $testResultFirst['segment'] ) ) {

            // limit return must be equal limit input 
            $this->unit->run( $testResultFirst['limit'], $limit , "To verify get list map state is valid", "limit return must be equal limit input");

            $this->unit->run( sizeof($testResultFirst['segment']), $limit , "To verify get list map state is valid", "count map result return must be equal limit input" );
        }

        echo $this->unit->report();
        echo $this->returnResult( $this->unit->result() );
    }

    /**
     * Testing retention day map 
     * 
     */
    function testRetentionDays() {

    }

    /**
     * returnResult 
     * @param  array $results 
     * @return string
     */
    function returnResult($results) {
        $passed = [];
        $failed = [];
        foreach($results as $value) {
            if($value['Result'] === "Passed") {
                array_push($passed, $value['Result']);
            }

            if($value['Result'] === "Failed") {
                array_push($failed, $value['Result']);
            }
        }
        return "<h1> Tests: ". sizeof($results). ", Passed: " .sizeof($passed). ", Failed:".sizeof($failed)."</h1>";
    }
}