<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Role extends MY_Model {

	protected $_table = 'Role';
	public $has_many = array( 'player' => array( 'model' => 'Player', 'primary_key' => 'id') );

}