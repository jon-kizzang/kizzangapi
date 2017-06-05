<?php 

  // Tracer.php
  // Author: Mike Berman, Kizzang
  // Jan-29-2015
  // tracer / logger for php

  //
  // Usage:
  // include_once "../path/to/helpers/Tracer.php";
 
  // $tracer = new Tracer("INFO");
  // $tracer->Info("Hola". time());
 

  // must set environment variable TRACE_PHP to enable 
  // in http conf:         SetEnv TRACE_PHP 1
  // the logging

  class Tracer {

   function __construct( $level ) {

       $this->logFile = "/tmp/tracer.log";
       $this->logLevel = $this->SetLogLevel($level);
   
   }

    public function SetLogLevel($level) {

 
    	$newLevel = "";


    	if ($level == "TRACE") {

              $newLevel  = "TRACE|INFO|DEBUG|ERROR";

    	}

    	if ($level == "DEBUG") {

              $newLevel  = "INFO|DEBUG|ERROR";

    	}

      	if ($level == "INFO") {

              $newLevel = "INFO|ERROR";

    	}
 
       return $newLevel;


    } //end SetLogLevel

    public function Log( $message, $level ) {

      
      try {

       // $debug = debug_print_backtrace();
        $debug = "";

      	$full_message = sprintf("%s $level: $message\n%s", 
            date('m/d/Y h:i:s a', time()),
      		$debug 

      		);

      	file_put_contents( 

      		$this->logFile , 
      		$full_message , 
      		FILE_APPEND 

      		);

      } catch (Exception $e) {

        echo "Could not write to trace file";
        return;

      }

    }//end Log


   public function LogCheck( $level ) {


   	  if (!getenv("TRACE_PHP")) { 

        return false;

   	  }

   	  if (stristr($this->logLevel, $level)) {
 
        return true;

   	  }

   	  return false;


   }


   public function Debug( $message ) {


   	  if ($this->LogCheck("DEBUG")) {

        $this->log( $message, "DEBUG" );

   	  }

  
   } // end Debug



   public function Info( $message ) {


   	  if ($this->LogCheck("INFO")) {

        $this->log( $message, "INFO" );

   	  }

  
   } // end Info


   public function Error( $message ) {


   	  if ($this->LogCheck("ERROR")) {

        $this->log( $message, "ERROR" );

   	  }

  
   } // end Error


   public function Trace( $message ) {


   	  if ($this->LogCheck("TRACE")) {

        $this->log( $message, "TRACE" );

   	  }

  
   } // end Trace



  }//end Tracer class