<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Kohana: The small, swift, and secure PHP5 framework
 *
 * @package          Kohana
 * @author           Kohana Team
 * @copyright        Copyright (c) 2007 Kohana Team
 * @link             http://kohanaphp.com
 * @license          http://kohanaphp.com/user_guide/kohana/license.html
 * @since            Version 1.0
 * @orig_package     CodeIgniter
 * @orig_author      Rick Ellis
 * @orig_copyright   Copyright (c) 2006, EllisLab, Inc.
 * @orig_license     http://www.codeigniter.com/user_guide/license.html
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * HTML Generation Helper
 *
 * $Id$
 *
 * @package     Kohana
 * @subpackage  Helpers
 * @category    Helpers
 * @author      Kohana Team
 * @link        http://kohanaphp.com/user_guide/helpers/html.html
 */
class html {

	/**
	 * Convert special characters to HTML entities
	 *
	 * @access public
	 * @param  string
	 * @param  boolean
	 * @return string
	 */
	public static function specialchars($str, $double_encode = TRUE)
	{
		// Do encode existing HTML entities (default)
		if ($double_encode == TRUE)
			return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');

		// Do not encode existing HTML entities
		// From PHP 5.2.3 this functionality is built-in, otherwise use a regex
		if (version_compare(PHP_VERSION, '5.2.3', '>='))
			return htmlspecialchars($str, ENT_QUOTES, 'UTF-8', FALSE);

		$str = preg_replace('/&(?!(?:#\d+|[a-z]+);)/i', '&amp;', $str);
		$str = str_replace(array('<', '>', '\'', '"'), array('&lt;', '&gt;', '&#39;', '&quot;'), $str);

		return $str;
	}

	/**
	 * HTML anchor generator
	 *
	 * @access public
	 * @param  string
	 * @param  string
	 * @param  mixed
	 * @return string
	 */
	public static function anchor($uri, $title = FALSE, $attributes = FALSE, $protocol = FALSE)
	{
		if (strpos($uri, '://') === FALSE)
		{
			$id = ''; // anchor#id
			$qs = ''; // anchor?query=string

			if (($start = strpos($uri, '?')) !== FALSE)
			{
				$qs  = substr($uri, $start);
				$uri = substr($uri, 0, $start);

				if (($start = strpos($qs, '#')) !== FALSE)
				{
					$id = substr($qs, $start);
					$qs = substr($qs, 0, $start);
				}
			}
			elseif (($start = strpos($uri, '#')) !== FALSE)
			{
				$id  = substr($uri, $start);
				$uri = substr($uri, 0, $start);
			}

			$site_url = url::site($uri, $protocol).$qs.$id;
		}
		else
		{
			$site_url = $uri;
		}

		$title = ($title == FALSE) ? $site_url : $title;

		$attributes = ($attributes == TRUE) ? self::attributes($attributes) : '';

		return '<a href="'.$site_url.'"'.$attributes.'>'.$title.'</a>';
	}

	public static function panchor($protocol, $uri, $title = FALSE, $attributes = FALSE)
	{
		return self::anchor($uri, $title, $attributes, $protocol);
	}

	public static function mailto($email, $title = FALSE, $attributes = FALSE)
	{
		// Remove the subject or other parameters that do not need to be encoded
		$subject = FALSE;
		if (strpos($email, '?') !== FALSE)
		{
			list ($email, $subject) = explode('?', $email);
		}

		$safe = '';
		foreach(str_split($email) as $i => $letter)
		{
			switch (($letter == '@') ? rand(1, 2) : rand(1, 3))
			{
				// HTML entity code
				case 1: $safe .= '&#'.ord($letter).';'; break;
				// Hex character code
				case 2: $safe .= '&#x'.dechex(ord($letter)).';'; break;
				// Raw (no) encoding
				case 3: $safe .= $letter;
			}
		}

		// Title defaults to the encoded email address
		$title = ($title == FALSE) ? $safe : $title;

		// URL encode the subject line
		$subject = ($subject == TRUE) ? '?'.rawurlencode($subject) : '';

		// Parse attributes
		$attributes = ($attributes == TRUE) ? self::attributes($attributes) : '';

		// Encoded start of the href="" is a static encoded version of 'mailto:'
		return '<a href="&#109;&#097;&#105;&#108;&#116;&#111;&#058;'.$safe.$subject.'"'.$attributes.'>'.$title.'</a>';
	}

	public static function stylesheet($style, $index = FALSE, $media = FALSE)
	{
		$compiled = '';

		if (is_array($style))
		{
			foreach($style as $name)
			{
				$compiled .= self::stylesheet($name, $index, $media)."\n";
			}
		}
		else
		{
			$media = ($media == FALSE) ? '' : ' media="'.$media.'"';

			$compiled = '<link rel="stylesheet" href="'.url::base($index).$style.'.css"'.$media.' />';
		}

		return $compiled;
	}

	/**
	 * Script generator
	 *
	 * @access public
	 * @param  mixed    String or array of script names
	 * @param  boolean  Add index to the URL
	 * @return string
	 */
	public static function script($script, $index = FALSE)
	{
		$compiled = '';

		if (is_array($script))
		{
			foreach($script as $name)
			{
				$compiled .= self::script($name, $index)."\n";
			}
		}
		else
		{
			$compiled = '<script type="text/javascript" src="'.url::base($index).$script.'.js"></script>';
		}

		return $compiled;
	}

	/**
	 * HTML Attribute Parser
	 *
	 * @access public
	 * @param  mixed
	 * @return string
	 */
	public static function attributes($attrs)
	{
		if (is_string($attrs))
			return ($attrs == FALSE) ? '' : ' '.$attrs;

		$compiled = '';

		foreach($attrs as $key => $val)
		{
			$compiled .= ' '.$key.'="'.$val.'"';
		}

		return $compiled;
	}

} // End html class