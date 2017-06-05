<?php
/**
 * @Author: Dung Ho
 * @Date:   2015-02-11 14:28:34
 * @Last Modified by:   Dung Ho
 * @Last Modified time: 2015-02-11 16:11:52
 */

class MY_Loader extends CI_Loader {

	/**
     * Database Loader
     *
     * @access    public
     * @param    string    the DB credentials
     * @param    bool    whether to return the DB object
     * @param    bool    whether to enable active record (this allows us to override the config setting)
     * @return    object
     */
    function database($params = '', $return = FALSE, $active_record = NULL ) {

        // Do we even need to load the database class?
        // if (class_exists('CI_DB') AND $return == FALSE AND $active_record == NULL)
		if (class_exists('CI_DB') AND $return == FALSE AND $active_record == NULL ) {

        	return FALSE;
        }

        require_once(BASEPATH.'database/DB'.EXT);

        // Load the DB class
        $db =& DB($params, $active_record);

        $my_driver = config_item('subclass_prefix').'DB_'.$db->dbdriver.'_driver';
        $my_driver_file = APPPATH.'libraries/'.$my_driver.EXT;

        if (file_exists($my_driver_file)) {

            require_once($my_driver_file);

            $db = new $my_driver(get_object_vars($db));
        }

        if ($return === TRUE) {
        	
            return $db;
        }

        // Grab the super object
        $CI =& get_instance();

        // Initialize the db variable.  Needed to prevent
        // reference errors with some configurations
        $CI->db = '';
        $CI->db = $db;
        
    }
}
