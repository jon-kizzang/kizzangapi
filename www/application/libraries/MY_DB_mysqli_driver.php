<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_DB_mysqli_driver extends CI_DB_mysqli_driver {

	/**
	 * Escape String
	 *
	 * @access	public
	 * @param	string
	 * @param	bool	whether or not the string will be used in a LIKE condition
	 * @return	string
	 */
	function escape_str($str, $like = FALSE) {

		if (is_array($str)) {

			foreach ($str as $key => $val) {

				$str[$key] = $this->escape_str($val, $like);
			}

			return $str;
		}

		if (function_exists('mysqli_real_escape_string') AND is_object($this->conn_id)) {

			$str = mysqli_real_escape_string($this->conn_id, $str);
		}
		// elseif (function_exists('mysql_escape_string'))
		// {
		// 	$str = mysql_escape_string($str);
		// }
		else {

			$str = addslashes($str);
		}

		// escape LIKE condition wildcards
		if ($like === TRUE) {

			$str = str_replace(array('%', '_'), array('\\%', '\\_'), $str);
		}

		return $str;
	}
}
