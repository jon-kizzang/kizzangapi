<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
/**
*  Function testing maps
*
*   - Testing get all maps 
*   - Testing create maps 
*/
class test_maps extends CI_Controller
{
    
    function __construct() {

        parent::__construct();

        $this->load->model('map');

        // loading library unit test
        $this->load->library('unit_test');

        // loading database test
        $this->load->database('test', TRUE);

        //To enable strict mode 
        $this->unit->use_strict(TRUE);

        // Disable database debugging so we can test all units without stopping
        // at the first SQL error
        $this->db->db_debug = FALSE;
    }

    /**
     * testCreatMap 
     *
     * Testing create new maps 
     */
    function testCreatMap() {

        // To verify create map is invalid
        $nameInvalid = array('', NULL);
        foreach ($nameInvalid as $key => $value) {
            $dataInvalid = array('name' => $value);
            $testResultInvalid = $this->map->add($dataInvalid);

            // The Name field is required. 
            if(is_array($testResultInvalid) && isset($testResultInvalid['errors'])) {

                $this->unit->run($testResultInvalid['errors'][0], "The Name field is required.", "To verify create maps is invalid", "The Name field is required.");
            } 
            
        }
        $testResult = $this->map->add(array('name' => "New Maps"));
        

        if (is_object($testResult) && isset($testResult)) {
            
            $resultExpected = $this->map->get_by('id', $testResult->id);

            // To verify create map is valid
            $this->unit->run($resultExpected->id, $testResult->id, "To verify create map is valid", "To verify id return must be equal id save on database");

            // To verify name return must be equal name saved on database
            $this->unit->run($resultExpected->name, $testResult->name, "To verify create map is valid", "To verify name return must be equal nameInvalid save on database");
        } else {

            echo "<h4 style='color: red;'>Can't check creat new map. Please try again.</h4>";
        }

        echo $this->unit->report();
        echo $this->returnResult($this->unit->result());
    }

    /**
     * testGetAllMap 
     *
     * Testing get maps by offset and limit value
     */
    function testGetById() {

    }


    /**
     * testGetAllMap 
     *
     * Testing get maps by offset and limit value
     */
    function testGetAllMaps() {

        $countExpected = $this->map->count_all();
        if ($countExpected) { 
            
            // To verify get maps is invalid
            $offsetInvalid = array('', null, 'abc', 0);
            $limitInvalid  = array('', null, 'abc', 0);

            foreach ($limitInvalid as $key => $value) {
                if(array_key_exists($key, $limitInvalid)) {
                    $testResultFirst = $this->map->getAll($offsetInvalid[$key], $limitInvalid[$key]);
                    if (is_array($testResultFirst) && isset($testResultFirst['errors'])) {
                        
                        // To verify map not found
                        $this->unit->run($testResultFirst['errors'], "Map Not Found", "To verify map return is invalid", "To verify map not found");
                    }
                    
                }
            }
            // To verify offset and limit is valid
            $offset = 0;
            $limit = 10;
            $testResultSecond = $this->map->getAll($offset, $limit);
            if (is_array($testResultSecond) && isset($testResultSecond['maps'])) {

                // verify limit return must be equal limit input before
                if ( isset($testResultSecond['limit']) ) {
                    $this->unit->run($testResultSecond['limit'], $limit, "To verify list return is valid", "verify limit return must be equal limit input before");
                }

                // verify offset return must be equal offset input before
                if ( isset($testResultSecond['offset']) ) {
                    $this->unit->run($testResultSecond['offset'], $offset, "To verify list return is valid", "verify offset return must be equal offset input before");
                }
                // verify count return must be equal count when get form data

                if( isset($testResultSecond['count']) ) {

                    $this->unit->run($testResultSecond['count'], $countExpected, "To verify list return is valid", "verify count return must be equal count get from data");
                }

                if( $limit < $countExpected)  {

                    // verify count list return must be equal value limit input
                    $this->unit->run(sizeof($testResultSecond['maps']), $limit, "To verify get list sweepstake is valid", "verify count return must be equal count when get form data"); 
                } else {
                    // verify count list return must be equal count of get on database
                    $this->unit->run(sizeof($testResultSecond['maps']), $countExpected, "To verify get list sweepstake is valid", "verify count list return must be equal count of get on database");
                }
            }
        } else {

            echo "<h4 style='color: red;'>Can't check get all maps. Make sure map is exist and not empty.</h4>";
        }

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