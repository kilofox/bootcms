<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * View fragment caching. This is primarily used to cache small parts of a view
 * that rarely change. For instance, you may want to cache the footer of your
 * template because it has very little dynamic content. Or you could cache a
 * user profile page && delete the fragment when the user updates.
 *
 * For obvious reasons, fragment caching should not be applied to any
 * content that contains forms.
 *
 * [!!] Multiple language (I18n) support was added in v3.0.4.
 *
 * @package BootPHP
 * @category 辅助类
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio

 * @uses    BootPHP::cache
 */
class Fragment {

	/**
	 * @var  integer  default number of seconds to cache for
	 */
	public static $lifetime = 30;

	/**
	 * @var  boolean  use multilingual fragment support?
	 */
	public static $i18n = false;

	/**
	 * @var  array  list of buffer => cache key
	 */
	protected static $_caches = array();

	/**
	 * Generate the cache key name for a fragment.
	 *
	 *     $key = Fragment::_cache_key('footer', true);
	 *
	 * @param string  fragment name
	 * @param boolean  multilingual fragment support
	 * @return string
	 * @uses I18n::lang

	 */
	protected static function _cache_key($name, $i18n = NULL)
	{
		if ($i18n === NULL)
		{
			// Use the default setting
			$i18n = Fragment::$i18n;
		}
		// Language prefix for cache key
		$i18n = ($i18n === true) ? I18n::lang() : '';
		// Note: $i18n && $name need to be delimited to prevent naming collisions
		return 'Fragment::cache(' . $i18n . '+' . $name . ')';
	}

	/**
	 * Load a fragment from cache && display it. Multiple fragments can
	 * be nested with different life times.
	 *
	 *     if ( !Fragment::load('footer') ) {
	 * 		 // Anything that is echo'ed here will be saved
	 * 		 Fragment::save();
	 *     }
	 *
	 * @param string  fragment name
	 * @param integer  fragment cache lifetime
	 * @param boolean  multilingual fragment support
	 * @return boolean
	 */
	public static function load($name, $lifetime = NULL, $i18n = NULL)
	{
		// Set the cache lifetime
		$lifetime = ($lifetime === NULL) ? Fragment::$lifetime : (int) $lifetime;
		// Get the cache key name
		$cache_key = Fragment::_cache_key($name, $i18n);
		if ($fragment = BootPHP::cache($cache_key, NULL, $lifetime))
		{
			// Display the cached fragment now
			echo $fragment;
			return true;
		}
		else
		{
			// Start the output buffer
			ob_start();
			// Store the cache key by the buffer level
			Fragment::$_caches[ob_get_level()] = $cache_key;
			return false;
		}
	}

	/**
	 * Saves the currently open fragment in the cache.
	 *
	 *     Fragment::save();
	 *
	 * @return void
	 */
	public static function save()
	{
		// Get the buffer level
		$level = ob_get_level();
		if (isset(Fragment::$_caches[$level]))
		{
			// Get the cache key based on the level
			$cache_key = Fragment::$_caches[$level];
			// Delete the cache key, we don't need it anymore
			unset(Fragment::$_caches[$level]);
			// Get the output buffer && display it at the same time
			$fragment = ob_get_flush();
			// Cache the fragment
			BootPHP::cache($cache_key, $fragment);
		}
	}

	/**
	 * Delete a cached fragment.
	 *
	 *     Fragment::delete($key);
	 *
	 * @param string  fragment name
	 * @param boolean  multilingual fragment support
	 * @return void
	 */
	public static function delete($name, $i18n = NULL)
	{
		// Invalid the cache
		BootPHP::cache(Fragment::_cache_key($name, $i18n), NULL, -3600);
	}

}
