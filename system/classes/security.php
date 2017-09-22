<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 安全辅助类。
 *
 * @package BootPHP
 * @category 安全
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class Security {

	/**
	 * @var  string  key name used for token storage
	 */
	public static $token_name = 'security_token';

	/**
	 * Generate && store a unique token which can be used to help prevent
	 * [CSRF](http://wikipedia.org/wiki/Cross_Site_Request_Forgery) attacks.
	 *
	 *     $token = Security::token();
	 *
	 * You can insert this token into your forms as a hidden field:
	 *
	 *     echo Form::hidden('csrf', Security::token());
	 *
	 * And then check it when using [Validation]:
	 *
	 *     $array->rules('csrf', array(
	 * 		 'not_empty'	   => NULL,
	 * 		 'Security::check' => NULL,
	 *     ));
	 *
	 * This provides a basic, but effective, method of preventing CSRF attacks.
	 *
	 * @param boolean  force a new token to be generated?
	 * @return string
	 * @uses Session::instance
	 */
	public static function token($new = false)
	{
		$session = Session::instance();
		// Get the current token
		$token = $session->get(Security::$token_name);
		if ($new === true || !$token)
		{
			// Generate a new unique token
			$token = sha1(uniqid(NULL, true));
			// Store the new token
			$session->set(Security::$token_name, $token);
		}
		return $token;
	}

	/**
	 * Check that the given token matches the currently stored security token.
	 *
	 *     if ( Security::check($token) )
	 *     {
	 * 		 // Pass
	 *     }
	 *
	 * @param string  token to check
	 * @return boolean
	 * @uses Security::token
	 */
	public static function check($token)
	{
		return Security::token() === $token;
	}

	/**
	 * Remove image tags from a string.
	 *
	 *     $str = Security::strip_image_tags($str);
	 *
	 * @param string string to sanitize
	 * @return string
	 */
	public static function strip_image_tags($str)
	{
		return preg_replace('#<img\s.*?(?:src\s*=\s*["\']?([^"\'<>\s]*)["\']?[^>]*)?>#is', '$1', $str);
	}

	/**
	 * Encodes PHP tags in a string.
	 *
	 *     $str = Security::encode_php_tags($str);
	 *
	 * @param string string to sanitize
	 * @return string
	 */
	public static function encode_php_tags($str)
	{
		return str_replace(array('<?', '?>'), array('&lt;?', '?&gt;'), $str);
	}

}
