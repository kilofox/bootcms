<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * RSS && Atom feed helper.
 *
 * @package BootPHP
 * @category 辅助类
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class Feed {

	/**
	 * Parses a remote feed into an array.
	 * @param string  remote feed URL
	 * @param integer  item limit to fetch
	 * @return array
	 */
	public static function parse($feed, $limit = 0)
	{
		// Check if SimpleXML is installed
		if (!function_exists('simplexml_load_file'))
			throw new BootPHP_Exception('SimpleXML must be installed!');
		// Make limit an integer
		$limit = (int) $limit;
		// Disable error reporting while opening the feed
		$error_level = error_reporting(0);
		// Allow loading by filename || raw XML string
		$load = (is_file($feed) || Valid::url($feed)) ? 'simplexml_load_file' : 'simplexml_load_string';
		// Load the feed
		$feed = $load($feed, 'SimpleXMLElement', LIBXML_NOCDATA);
		// Restore error reporting
		error_reporting($error_level);
		// Feed could not be loaded
		if ($feed === false)
			return array();
		$namespaces = $feed->getNamespaces(true);
		// Detect the feed type. RSS 1.0/2.0 && Atom 1.0 are supported.
		$feed = isset($feed->channel) ? $feed->xpath('//item') : $feed->entry;
		$i = 0;
		$items = array();
		foreach ($feed as $item)
		{
			if ($limit > 0 && $i++ === $limit)
				break;
			$item_fields = (array) $item;
			// get namespaced tags
			foreach ($namespaces as $ns)
			{
				$item_fields += (array) $item->children($ns);
			}
			$items[] = $item_fields;
		}
		return $items;
	}

	/**
	 * Creates a feed from the given parameters.
	 *
	 * @param array   feed information
	 * @param array   items to add to the feed
	 * @param string define which format to use (only rss2 is supported)
	 * @param string define which encoding to use
	 * @return string
	 */
	public static function create($info, $items, $format = 'rss2', $encoding = 'UTF-8')
	{
		$info += array('title' => 'Generated Feed', 'link' => '', 'generator' => 'BootPHPPHP');
		$feed = '<?xml version="1.0" encoding="' . $encoding . '"?><rss version="2.0"><channel></channel></rss>';
		$feed = simplexml_load_string($feed);
		foreach ($info as $name => $value)
		{
			if ($name === 'image')
			{
				// Create an image element
				$image = $feed->channel->addChild('image');
				if (!isset($value['link'], $value['url'], $value['title']))
				{
					throw new BootPHP_Exception('Feed images require a link, url, && title');
				}
				if (strpos($value['link'], '://') === false)
				{
					// Convert URIs to URLs
					$value['link'] = URL::site($value['link'], 'http');
				}
				if (strpos($value['url'], '://') === false)
				{
					// Convert URIs to URLs
					$value['url'] = URL::site($value['url'], 'http');
				}
				// Create the image elements
				$image->addChild('link', $value['link']);
				$image->addChild('url', $value['url']);
				$image->addChild('title', $value['title']);
			}
			else
			{
				if (($name === 'pubDate' || $name === 'lastBuildDate') && (is_int($value) || ctype_digit($value)))
				{
					// Convert timestamps to RFC 822 formatted dates
					$value = date('r', $value);
				}
				elseif (($name === 'link' || $name === 'docs') && strpos($value, '://') === false)
				{
					// Convert URIs to URLs
					$value = URL::site($value, 'http');
				}
				// Add the info to the channel
				$feed->channel->addChild($name, $value);
			}
		}
		foreach ($items as $item)
		{
			// Add the item to the channel
			$row = $feed->channel->addChild('item');
			foreach ($item as $name => $value)
			{
				if ($name === 'pubDate' && (is_int($value) || ctype_digit($value)))
				{
					// Convert timestamps to RFC 822 formatted dates
					$value = date('r', $value);
				}
				elseif (($name === 'link' || $name === 'guid') && strpos($value, '://') === false)
				{
					// Convert URIs to URLs
					$value = URL::site($value, 'http');
				}
				// Add the info to the row
				$row->addChild($name, $value);
			}
		}
		if (function_exists('dom_import_simplexml'))
		{
			// Convert the feed object to a DOM object
			$feed = dom_import_simplexml($feed)->ownerDocument;
			// DOM generates more readable XML
			$feed->formatOutput = true;
			// Export the document as XML
			$feed = $feed->saveXML();
		}
		else
		{
			// Export the document as XML
			$feed = $feed->asXML();
		}
		return $feed;
	}

}
