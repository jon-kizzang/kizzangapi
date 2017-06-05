<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ParlayPlace extends MY_Model {

    // set table Gender
	protected $_table = 'SportParlayPlaces';

	protected $belongs_to = array(
            'config' => array( 'model' => 'ParlayConfig', 'primary_key' => 'parlayCardId' )
        );
	
}