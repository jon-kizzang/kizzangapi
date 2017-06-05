<?php

class Sponsors extends MY_Controller {

     function __construct() {

        parent::__construct(
            TRUE, // Controller secured
            array(
               'getSponsorWheelData' => array( 'Administrator', 'User', 'Guest' ),
               'getSponsorMapData' => array( 'Administrator', 'User', 'Guest' ),
            )//secured action
        );

        // loading model sponsor
        $this->load->model('sponsor');
    }

    /**
     * get all sponsors wheels
     * GET api/wheels/sponsors
     *     or api/wheels/sponsors/16/3/18/99
     */
    public function getSponsorWheelData_get( $slices = 16, $gender = 3, $ageMin = 18, $ageMax = 99 ) {

        $result = $this->sponsor->getSponsorWheelData( $slices, $gender, $ageMin, $ageMax );
        
        $this->formatResponse( $result );
    }

    public function getSponsorWheelData_post( $slices = 16, $gender = 3, $ageMin = 18, $ageMax = 99 ) {

    	$this->getSponsorWheelData_get($slices, $gender, $ageMin, $ageMax);
	}

    /**
     * get all sponsors maps
     * GET api/maps/sponsors
     *     or api/maps/sponsors/16/3/18/99
     */
    public function getSponsorMapData_get( $map = null, $gender  = null, $ageMin = null, $ageMax = null, $panelX = 0, $panelY = 0 ) {

        $result = $this->sponsor->getSponsorMapData( $map, $gender, $ageMin, $ageMax, $panelX, $panelY );
        
        $this->formatResponse( $result );
    }

    public function getSponsorMapData_post( $map = null, $gender  = null, $ageMin = null, $ageMax = null, $panelX = 0, $panelY = 0 ) {

        $this->getSponsorMapData_get($map, $gender, $ageMin, $ageMax, $panelX, $panelY);
    }

    /**
     * get random wheel sponsor 
     * GET /api/wheels/sponsor/<sponsorId>/spin
     */
    public function spin_get( $sponsorId ) {

        $result = $this->sponsor->sponsorSpin( $sponsorId ) ;

        // format response result
        $this->formatResponse( $result );
    }

    public function spin_post( $sponsorId ) {

        $this->spin_get( $sponsorId );
    }
}