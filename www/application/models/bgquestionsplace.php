<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class BGQuestionsPlace extends MY_Model {

    // set table Gender
	protected $_table = 'BGQuestionsPlaces';

	protected $belongs_to = array(
            'config' => array( 'model' => 'BGQuestionConfig', 'primary_key' => 'parlayCardId' )
        );
	
}