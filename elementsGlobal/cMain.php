<?php
/*
 * Version	1.02
 * Date		01.12.2022
*/

class MAIN {
	private static $instance;
	
	public function __construct()
	{
		self::$instance =& $this;
	}
	
	public static function &get_instance()
	{
		return self::$instance;
	}
	
	public function database($params = '', $return = FALSE, $query_builder = NULL)
	{
		// Do we even need to load the database class?
		if ($return === FALSE && $query_builder === NULL && isset($this->db) && is_object($this->db) && ! empty($this->db->conn_id))
		{
			return FALSE;
		}

		require_once(BASEPATH.'/lib/database/DB.php');

		if ($return === TRUE)
		{
			return DB($params, $query_builder);
		}

		// Initialize the db variable. Needed to prevent
		// reference errors with some configurations
		$this->db = '';

		// Load the DB class
		$this->db =& DB($params, $query_builder);
		return $this;
	}
	
	public function db_escape_name($s_string)
	{
		return preg_replace('#[^A-za-z0-9_]+#', '', $s_string);
	}
}
function &get_instance()
{
	return MAIN::get_instance();
}

function show_error($s_txt)
{
	//print_r($s_txt);
}
function log_message($s_txt)
{
	//print_r($s_txt);
}
if(!function_exists('is_php'))
{
	/**
	 * Determines if the current version of PHP is equal to or greater than the supplied value
	 *
	 * @param	string
	 * @return	bool	TRUE if the current version is $version or higher
	 */
	function is_php($version)
	{
		static $_is_php;
		$version = (string) $version;

		if(!isset($_is_php[$version]))
		{
			$_is_php[$version] = version_compare(PHP_VERSION, $version, '>=');
		}

		return $_is_php[$version];
	}
}

$o_main = new MAIN();
$o_main->database('',false,true);

if(!function_exists('sanitize_escape'))
{
	/**
	 * Casts variable to specified type and in addition sanitizes and escapes
	 *
	 * @param			string	Variable to handle
	 * @param			string	Cast to specific type (int, float, bool, string (default))
	 * @param/return	mix		Casted and sanitized variable
	 * @param/return	mix		Casted, sanitized and escaped variable
	 * @return			mix		Casted variable in specified type
	 */
	function sanitize_escape($value, $type = '', &$sanitized = NULL, &$sanitized_escaped = NULL)
	{
		if('int' == $type)
		{
			$value = $sanitized = $sanitized_escaped = (int)$value;
		} else if('float' == $type)
		{
			$value = $sanitized = $sanitized_escaped = (float)$value;
		} else if('bool' == $type)
		{
			$value = $sanitized = $sanitized_escaped = (boolean)$value;
		} else {
			$o_main = get_instance();
			$value = (string)$value;
			$sanitized = (string)trim(strip_tags($value));
			$sanitized_escaped = (string)"'".$o_main->db->escape_str($sanitized)."'";
		}
		return $value;
	}
}
