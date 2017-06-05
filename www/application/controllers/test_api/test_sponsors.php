<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
/**
 * Testing sponsors 
 * 
 */
class test_sponsors extends CI_Controller {
  
    function __construct() {

        parent::__construct();

        // loading model map
        $this->load->model( 'sponsor' );

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
     * Function testing get map sponsor data
     * 
     */
    function testGetSponsorMapData() {
        $map = 1;
        $gender = 3;
        $ageMax = 99;
        $ageMin = 18;

        // Verify get sponsor map data is invalid
        //========================================
        // To verify map is invalid
        $mapInvalid = array( null, 'abc', -1, 0 );

        foreach ( $mapInvalid as $value ) {
            
            $testResultFirst = $this->sponsor->getSponsorMapData( $value, $gender, $ageMin, $ageMax);

            if ( is_array( $testResultFirst ) && isset( $testResultFirst['errors'] ) ) {


                $this->unit->run($testResultFirst['errors'], "Sponsor Map Not Found", "Verify get sponsor map data is invalid", "To verify map is invalid");
            }
        }
        
        // To verify gender is invalid
        $genderInvalid = array( null, 'abc', -1, 100 );
        
        foreach ( $genderInvalid as $value ) {
            
            $testResultSeconds = $this->sponsor->getSponsorMapData( $map, $value, $ageMin, $ageMax);

            if ( is_array( $testResultSeconds ) && isset( $testResultSeconds['errors'] ) ) {


                $this->unit->run($testResultSeconds['errors'], "Sponsor Map Not Found", "Verify get sponsor map data is invalid", "To verify map is invalid");
            }
        }

        // To verify egeMin is invalid
        $ageMinInvalid = 17;

        $testResultThirds = $this->sponsor->getSponsorMapData( $map, $gender, $ageMinInvalid, $ageMax);

        if ( is_array( $testResultThirds ) && isset( $testResultThirds['errors'] ) ) {


            $this->unit->run($testResultThirds['errors'], "Sponsor Map Not Found", "Verify get sponsor map data is invalid", "To verify map is invalid");
        }

        // To verify ageMax is invalid
        $ageMaxInvalid = 102;
            
        $testResultFourth = $this->sponsor->getSponsorMapData( $map, $gender, $ageMin, $ageMinInvalid);

        if ( is_array( $testResultFourth ) && isset( $testResultFourth['errors'] ) ) {


            $this->unit->run($testResultFourth['errors'], "Sponsor Map Not Found", "Verify get sponsor map data is invalid", "To verify map is invalid");
        }

        // Verify get sponsor map data is valid
        // =======================================
        $testResultFifth = $this->sponsor->getSponsorMapData( $map, $gender, $ageMin, $ageMax );
        if ( is_array( $testResultFifth ) && isset( $testResultFifth['map'] ) ) {

            foreach ( $testResultFifth['map'] as $key => $value) {
                $id = $value['egg']['id'];

                $result = $this->db->select('name, mapId, gender, ageMin, ageMax')
                    ->where('id', $id)
                    ->get('Sponsor_Campaigns')
                    ->result();

                // verify name return must be equal from database by Id
                $this->unit->run( $value['egg']['name'], $result[0]->name );

                // verify gender return must be equal gender input by id
                $this->unit->run( (int)$result[0]->gender , $gender );
                
                // verify mapId return must be equal mapId inputbu id
                $this->unit->run( (int)$result[0]->mapId , $map );

                // verify ageMin return must be equal ageMin inputbu id
                $this->unit->run( (int)$result[0]->ageMin , $ageMin );
                
                // verify ageMax return must be equal ageMax inputbu id
                $this->unit->run( (int)$result[0]->ageMax , $ageMax );
            }
        }
        echo $this->unit->report();
        echo $this->returnResult( $this->unit->result() );
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