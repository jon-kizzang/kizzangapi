<?php

// Reads config from json file dropped at Chef creation
// 
// Usage:
// 
// From within a PHP file:
//
// include_once "../kizzang_application/helpers/ChefConfig.php";
//
 
 

class ChefConfig
{

    // property declaration
    private $configuration_file_path  = "../chef-configs/remote-config.json" ;
    public $Config;

   function __construct() {


       //make sure it exists
       if (!file_exists($this->configuration_file_path)) {
       
         die("FATAL: Missing " . $this->configuration_file_path . `pwd` );
        
       }
       
       $raw_contents =  file_get_contents($this->configuration_file_path);


       //parse json object
       try {

         $this->Config = json_decode( $raw_contents );

       } catch (Exception $e) {

         die("FATAL: Bad configuration file? ");

       }
     

   }


}

$dynamicConfig = new ChefConfig();
 
## loop over all values in the config and define constants
foreach ($dynamicConfig->Config as $key => $value) {
    //print '{"name":"' . $key . '", "value":"' . $value . '"},' . "\n";
	# set all remote variables
    putenv("$key=$value");
 
}

//die();
?>
