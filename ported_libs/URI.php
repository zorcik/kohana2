<?php  if (!defined('SYSPATH')) exit('No direct script access allowed');
/**
 * Kohana
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * NOTE: This file has been modified from the original CodeIgniter version for
 * the Kohana framework by the Kohana Development Team.
 *
 * @package          Kohana
 * @author           Kohana Development Team
 * @copyright        Copyright (c) 2007, Kohana Framework Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/user_guide/license.html
 * @since            Version 1.0
 * @orig_package     CodeIgniter
 * @orig_author      Rick Ellis
 * @orig_copyright   Copyright (c) 2006, EllisLab, Inc.
 * @orig_license     http://www.codeigniter.com/user_guide/license.html
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * URI Class
 *
 * Parses URIs and determines routing
 *
 * @package    Kohana
 * @subpackage Libraries
 * @category   URI
 * @author     Rick Ellis, Kohana Team
 * @link       http://kohanaphp.com/user_guide/libraries/uri.html
 */
class Core_URI {

	var $router;
	var $keyval = array();

	/**
	 * Constructor
	 *
	 * Simply globalizes the $RTR object.  The front
	 * loads the Router class early on so it's not available
	 * normally as other classes are.
	 *
	 * @access	public
	 */
	public function __construct()
	{
		$this->router = load_class('Router');
		Log::add('debug', 'URI Class Initialized');
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch a URI Segment
	 *
	 * This function returns the URI segment based on the number provided.
	 *
	 * @access	public
	 * @param	integer
	 * @param	bool
	 * @return	string
	 */
	public function segment($n, $no_result = FALSE)
	{
		return ( ! isset($this->router->segments[$n])) ? $no_result : $this->router->segments[$n];
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch a URI "routed" Segment
	 *
	 * This function returns the re-routed URI segment (assuming routing rules are used)
	 * based on the number provided.  If there is no routing this function returns the
	 * same result as $this->segment()
	 *
	 * @access	public
	 * @param	integer
	 * @param	bool
	 * @return	string
	 */
	public function rsegment($n, $no_result = FALSE)
	{
		return ( ! isset($this->router->rsegments[$n])) ? $no_result : $this->router->rsegments[$n];
	}

	// --------------------------------------------------------------------

	/**
	 * Generate a key value pair from the URI string
	 *
	 * This function generates and associative array of URI data starting
	 * at the supplied segment. For example, if this is your URI:
	 *
	 *	www.your-site.com/user/search/name/joe/location/UK/gender/male
	 *
	 * You can use this function to generate an array with this prototype:
	 *
	 * array (
	 *			name => joe
	 *			location => UK
	 *			gender => male
	 *		 )
	 *
	 * @access	public
	 * @param	integer	the starting segment number
	 * @param	array	an array of default values
	 * @return	array
	 */
	public function uri_to_assoc($n = 3, $default = array())
	{
	 	return $this->_uri_to_assoc($n, $default, 'segment');
	}
	/**
	 * Identical to above only it uses the re-routed segment array
	 *
	 */
	public function ruri_to_assoc($n = 3, $default = array())
	{
	 	return $this->_uri_to_assoc($n, $default, 'rsegment');
	}

	// --------------------------------------------------------------------

	/**
	 * Generate a key value pair from the URI string or Re-routed URI string
	 *
	 * @access	private
	 * @param	integer	the starting segment number
	 * @param	array	an array of default values
	 * @param	string	which array we should use
	 * @return	array
	 */
	private function _uri_to_assoc($n = 3, $default = array(), $which = 'segment')
	{
		$n = (int) abs($n);

		if ($n == 0)
			return $default;

		if (isset($this->keyval[$n]))
			return $this->keyval[$n];

		$total_segments	= ($which == 'segment') ? 'total_segments' : 'total_rsegments';
		$segment_array	= ($which == 'segment') ? 'segment_array' : 'rsegment_array';

		if ($this->$total_segments() < $n)
		{
			$retval = array();
			foreach ($default as $val)
			{
				$retval[$val] = FALSE;
			}
			return $retval;
		}

		$segments = array_slice($this->$segment_array(), ($n - 1));

		$i = 0;
		$lastval = "";
		$retval  = array();
		foreach ($segments as $seg)
		{
			if ($i % 2)
			{
				$retval[$lastval] = $seg;
			}
			else
			{
				$retval[$seg] = FALSE;
				$lastval = $seg;
			}

			$i++;
		}

		if (count($default) > 0)
		{
			foreach ($default as $val)
			{
				if ( ! array_key_exists($val, $retval))
				{
					$retval[$val] = FALSE;
				}
			}
		}

		// Cache the array for reuse
		$this->keyval[$n] = $retval;
		return $retval;
	}

	/**
	 * Generate a URI string from an associative array
	 *
	 *
	 * @access	public
	 * @param	array	an associative array of key/values
	 * @return	array
	 */	function assoc_to_uri($array)
	{
		$temp = array();
		foreach ((array)$array as $key => $val)
		{
			$temp[] = $key;
			$temp[] = $val;
		}

		return implode("/", $temp);
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch a URI Segment and add a trailing slash
	 *
	 * @access	public
	 * @param	integer
	 * @param	string
	 * @return	string
	 */
	public function slash_segment($n, $where = 'trailing')
	{
		return $this->_slash_segment($n, $where, 'segment');
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch a URI Segment and add a trailing slash
	 *
	 * @access	public
	 * @param	integer
	 * @param	string
	 * @return	string
	 */
	public function slash_rsegment($n, $where = 'trailing')
	{
		return $this->_slash_segment($n, $where, 'rsegment');
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch a URI Segment and add a trailing slash - helper function
	 *
	 * @access	private
	 * @param	integer
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	private function _slash_segment($n, $where = 'trailing', $which = 'segment')
	{
		$leading	= '/';
		$trailing	= '/';

		if ($where == 'trailing')
		{
			$leading	= '';
		}
		elseif ($where == 'leading')
		{
			$trailing	= '';
		}

		return $leading . $this->$which($n) . $trailing;
	}

	// --------------------------------------------------------------------

	/**
	 * Segment Array
	 *
	 * @access	public
	 * @return	array
	 */
	public function segment_array()
	{
		return $this->router->segments;
	}

	// --------------------------------------------------------------------

	/**
	 * Routed Segment Array
	 *
	 * @access	public
	 * @return	array
	 */
	public function rsegment_array()
	{
		return $this->router->rsegments;
	}

	// --------------------------------------------------------------------

	/**
	 * Total number of segments
	 *
	 * @access	public
	 * @return	integer
	 */
	public	function total_segments()
	{
		return count($this->router->segments);
	}

	// --------------------------------------------------------------------

	/**
	 * Total number of routed segments
	 *
	 * @access	public
	 * @return	integer
	 */
	public function total_rsegments()
	{
		return count($this->router->rsegments);
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch the entire URI string
	 *
	 * @access	public
	 * @return	string
	 */
	public function uri_string()
	{
		return $this->router->uri_string;
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch the entire Re-routed URI string
	 *
	 * @access	public
	 * @return	string
	 */
	public function ruri_string()
	{
		return '/'.implode('/', $this->rsegment_array());
	}

}
// END URI Class
?>