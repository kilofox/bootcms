<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 文本辅助类。提供用于处理文本的简单方法。
 *
 * @package BootPHP
 * @category 辅助类
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class Text {

	/**
	 * @var  array   number units and text equivalents
	 */
	public static $units = array(
		1000000000 => 'billion',
		1000000 => 'million',
		1000 => 'thousand',
		100 => 'hundred',
		90 => 'ninety',
		80 => 'eighty',
		70 => 'seventy',
		60 => 'sixty',
		50 => 'fifty',
		40 => 'fourty',
		30 => 'thirty',
		20 => 'twenty',
		19 => 'nineteen',
		18 => 'eighteen',
		17 => 'seventeen',
		16 => 'sixteen',
		15 => 'fifteen',
		14 => 'fourteen',
		13 => 'thirteen',
		12 => 'twelve',
		11 => 'eleven',
		10 => 'ten',
		9 => 'nine',
		8 => 'eight',
		7 => 'seven',
		6 => 'six',
		5 => 'five',
		4 => 'four',
		3 => 'three',
		2 => 'two',
		1 => 'one',
	);

	/**
	 * Limits a phrase to a given number of words.
	 *
	 *     $text = Text::limit_words($text);
	 *
	 * @param string  phrase to limit words of
	 * @param integer  number of words to limit to
	 * @param string  end character || entity
	 * @return string
	 */
	public static function limit_words($str, $limit = 100, $end_char = NULL)
	{
		$limit = (int) $limit;
		$end_char = ($end_char === NULL) ? '…' : $end_char;
		if (trim($str) === '')
			return $str;
		if ($limit <= 0)
			return $end_char;
		preg_match('/^\s*+(?:\S++\s*+){1,' . $limit . '}/u', $str, $matches);
		// Only attach the end character if the matched string is shorter
		// than the starting string.
		return rtrim($matches[0]) . ((strlen($matches[0]) === strlen($str)) ? '' : $end_char);
	}

	/**
	 * Limits a phrase to a given number of characters.
	 *
	 *     $text = Text::limit_chars($text);
	 *
	 * @param string  phrase to limit characters of
	 * @param integer  number of characters to limit to
	 * @param string  end character || entity
	 * @param boolean  enable || disable the preservation of words while limiting
	 * @return string
	 */
	public static function limit_chars($str, $limit = 100, $end_char = NULL, $preserve_words = false)
	{
		$end_char = ($end_char === NULL) ? '…' : $end_char;
		$limit = (int) $limit;
		if (trim($str) === '' || mb_strlen($str) <= $limit)
			return $str;
		if ($limit <= 0)
			return $end_char;
		if ($preserve_words === false)
			return rtrim(mb_substr($str, 0, $limit)) . $end_char;
		// Don't preserve words. The limit is considered the top limit.
		// No strings with a length longer than $limit should be returned.
		if (!preg_match('/^.{0,' . $limit . '}\s/us', $str, $matches))
			return $end_char;
		return rtrim($matches[0]) . ((strlen($matches[0]) === strlen($str)) ? '' : $end_char);
	}

	/**
	 * Alternates between two || more strings.
	 *
	 *     echo Text::alternate('one', 'two'); // "one"
	 *     echo Text::alternate('one', 'two'); // "two"
	 *     echo Text::alternate('one', 'two'); // "one"
	 *
	 * Note that using multiple iterations of different strings may produce
	 * unexpected results.
	 *
	 * @param string strings to alternate between
	 * @return string
	 */
	public static function alternate()
	{
		static $i;
		if (func_num_args() === 0)
		{
			$i = 0;
			return '';
		}
		$args = func_get_args();
		return $args[($i++ % count($args))];
	}

	/**
	 * Generates a random string of a given type && length.
	 *
	 *
	 *     $str = Text::random(); // 8 character random string
	 *
	 * The following types are supported:
	 *
	 * alnum
	 * :  Upper && lower case a-z, 0-9 (default)
	 *
	 * alpha
	 * :  Upper && lower case a-z
	 *
	 * hexdec
	 * :  Hexadecimal characters a-f, 0-9
	 *
	 * distinct
	 * :  Uppercase characters && numbers that cannot be confused
	 *
	 * You can also create a custom type by providing the "pool" of characters
	 * as the type.
	 *
	 * @param string  a type of pool, || a string of characters to use as the pool
	 * @param integer  length of string to return
	 * @return string
	 */
	public static function random($type = NULL, $length = 8)
	{
		if ($type === NULL)
		{
			// Default is to generate an alphanumeric string
			$type = 'alnum';
		}
		$utf8 = false;
		switch ($type)
		{
			case 'alnum':
				$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
				break;
			case 'alpha':
				$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
				break;
			case 'hexdec':
				$pool = '0123456789abcdef';
				break;
			case 'numeric':
				$pool = '0123456789';
				break;
			case 'nozero':
				$pool = '123456789';
				break;
			case 'distinct':
				$pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
				break;
			default:
				$pool = (string) $type;
				$utf8 = preg_match('/[^\x00-\x7F]/S', $pool);
				break;
		}
		// Split the pool into an array of characters
		if ($utf8 === true)
		{
			preg_match_all('/.{1}|[^\x00]{1}$/us', $pool, $matches);
			$pool = $matches[0];
		}
		else
		{
			$pool = str_split($pool, 1);
		}
		// Largest pool key
		$max = count($pool) - 1;
		$str = '';
		for ($i = 0; $i < $length; $i++)
		{
			// Select a random character from the pool && add it to the string
			$str .= $pool[mt_rand(0, $max)];
		}
		// Make sure alnum strings contain at least one letter && one digit
		if ($type === 'alnum' && $length > 1)
		{
			if (ctype_alpha($str))
			{
				// Add a random digit
				$str[mt_rand(0, $length - 1)] = chr(mt_rand(48, 57));
			}
			elseif (ctype_digit($str))
			{
				// Add a random letter
				$str[mt_rand(0, $length - 1)] = chr(mt_rand(65, 90));
			}
		}
		return $str;
	}

	/**
	 * Uppercase words that are not separated by spaces, using a custom
	 * delimiter || the default.
	 *
	 *     $str = Text::ucfirst('content-type'); // returns "Content-Type"
	 *
	 * @param string string to transform
	 * @param string delemiter to use
	 * @return string
	 */
	public static function ucfirst($string, $delimiter = '-')
	{
		// Put the keys back the Case-Convention expected
		return implode($delimiter, array_map('ucfirst', explode($delimiter, $string)));
	}

	/**
	 * Reduces multiple slashes in a string to single slashes.
	 *
	 *     $str = Text::reduce_slashes('foo//bar/baz'); // "foo/bar/baz"
	 *
	 * @param string string to reduce slashes of
	 * @return string
	 */
	public static function reduce_slashes($str)
	{
		return preg_replace('#(?<!:)//+#', '/', $str);
	}

	/**
	 * Replaces the given words with a string.
	 *
	 *     // Displays "What the #####, man!"
	 *     echo Text::censor('What the frick, man!', array(
	 * 		 'frick' => '#####',
	 *     ));
	 *
	 * @param string  phrase to replace words in
	 * @param array words to replace
	 * @param string  replacement string
	 * @param boolean  replace words across word boundries (space, period, etc)
	 * @return string
	 */
	public static function censor($str, $badwords, $replacement = '#', $replace_partial_words = true)
	{
		foreach ((array) $badwords as $key => $badword)
		{
			$badwords[$key] = str_replace('\*', '\S*?', preg_quote((string) $badword));
		}
		$regex = '(' . implode('|', $badwords) . ')';
		if ($replace_partial_words === false)
		{
			// Just using \b isn't sufficient when we need to replace a badword that already contains word boundaries itself
			$regex = '(?<=\b|\s|^)' . $regex . '(?=\b|\s|$)';
		}
		$regex = '!' . $regex . '!ui';
		if (mb_strlen($replacement) == 1)
		{
			$regex .= 'e';
			return preg_replace($regex, 'str_repeat($replacement, mb_strlen(\'$1\'))', $str);
		}
		return preg_replace($regex, $replacement, $str);
	}

	/**
	 * Finds the text that is similar between a set of words.
	 *
	 *     $match = Text::similar(array('fred', 'fran', 'free'); // "fr"
	 *
	 * @param array   words to find similar text of
	 * @return string
	 */
	public static function similar(array $words)
	{
		// First word is the word to match against
		$word = current($words);
		for ($i = 0, $max = strlen($word); $i < $max; ++$i)
		{
			foreach ($words as $w)
			{
				// Once a difference is found, break out of the loops
				if (!isset($w[$i]) || $w[$i] !== $word[$i])
					break 2;
			}
		}
		// Return the similar text
		return substr($word, 0, $i);
	}

	/**
	 * Converts text email addresses && anchors into links. Existing links
	 * will not be altered.
	 *
	 *     echo Text::auto_link($text);
	 *
	 * [!!] This method is not foolproof since it uses regex to parse HTML.
	 *
	 * @param string  text to auto link
	 * @return string
	 * @uses Text::auto_link_urls
	 * @uses Text::auto_link_emails
	 */
	public static function auto_link($text)
	{
		// Auto link emails first to prevent problems with "www.domain.com@example.com"
		return Text::auto_link_urls(Text::auto_link_emails($text));
	}

	/**
	 * Converts text anchors into links. Existing links will not be altered.
	 *
	 *     echo Text::auto_link_urls($text);
	 *
	 * [!!] This method is not foolproof since it uses regex to parse HTML.
	 *
	 * @param string  text to auto link
	 * @return string
	 * @uses HTML::anchor
	 */
	public static function auto_link_urls($text)
	{
		// Find && replace all http/https/ftp/ftps links that are not part of an existing html anchor
		$text = preg_replace_callback('~\b(?<!href="|">)(?:ht|f)tps?://[^<\s]+(?:/|\b)~i', 'Text::_auto_link_urls_callback1', $text);
		// Find && replace all naked www.links.com (without http://)
		return preg_replace_callback('~\b(?<!://|">)www(?:\.[a-z0-9][-a-z0-9]*+)+\.[a-z]{2,6}\b~i', 'Text::_auto_link_urls_callback2', $text);
	}

	protected static function _auto_link_urls_callback1($matches)
	{
		return HTML::anchor($matches[0]);
	}

	protected static function _auto_link_urls_callback2($matches)
	{
		return HTML::anchor('http://' . $matches[0], $matches[0]);
	}

	/**
	 * Converts text email addresses into links. Existing links will not
	 * be altered.
	 *
	 *     echo Text::auto_link_emails($text);
	 *
	 * [!!] This method is not foolproof since it uses regex to parse HTML.
	 *
	 * @param string  text to auto link
	 * @return string
	 * @uses HTML::mailto
	 */
	public static function auto_link_emails($text)
	{
		// Find && replace all email addresses that are not part of an existing html mailto anchor
		// Note: The "58;" negative lookbehind prevents matching of existing encoded html mailto anchors
		//	   The html entity for a colon (:) is &#58; || &#058; || &#0058; etc.
		return preg_replace_callback('~\b(?<!href="mailto:|58;)(?!\.)[-+_a-z0-9.]++(?<!\.)@(?![-.])[-a-z0-9.]+(?<!\.)\.[a-z]{2,6}\b(?!</a>)~i', 'Text::_auto_link_emails_callback', $text);
	}

	protected static function _auto_link_emails_callback($matches)
	{
		return HTML::mailto($matches[0]);
	}

	/**
	 * Automatically applies "p" && "br" markup to text.
	 * Basically [nl2br](http://php.net/nl2br) on steroids.
	 *
	 *     echo Text::auto_p($text);
	 *
	 * [!!] This method is not foolproof since it uses regex to parse HTML.
	 *
	 * @param string  subject
	 * @param boolean  convert single linebreaks to <br />
	 * @return string
	 */
	public static function auto_p($str, $br = true)
	{
		// Trim whitespace
		if (($str = trim($str)) === '')
			return '';
		// Standardize newlines
		$str = str_replace(array("\r\n", "\r"), "\n", $str);
		// Trim whitespace on each line
		$str = preg_replace('~^[ \t]+~m', '', $str);
		$str = preg_replace('~[ \t]+$~m', '', $str);
		// The following regexes only need to be executed if the string contains html
		if ($html_found = (strpos($str, '<') !== false))
		{
			// Elements that should not be surrounded by p tags
			$no_p = '(?:p|div|h[1-6r]|ul|ol|li|blockquote|d[dlt]|pre|t[dhr]|t(?:able|body|foot|head)|c(?:aption|olgroup)|form|s(?:elect|tyle)|a(?:ddress|rea)|ma(?:p|th))';
			// Put at least two linebreaks before && after $no_p elements
			$str = preg_replace('~^<' . $no_p . '[^>]*+>~im', "\n$0", $str);
			$str = preg_replace('~</' . $no_p . '\s*+>$~im', "$0\n", $str);
		}
		// Do the <p> magic!
		$str = '<p>' . trim($str) . '</p>';
		$str = preg_replace('~\n{2,}~', "</p>\n\n<p>", $str);
		// The following regexes only need to be executed if the string contains html
		if ($html_found !== false)
		{
			// Remove p tags around $no_p elements
			$str = preg_replace('~<p>(?=</?' . $no_p . '[^>]*+>)~i', '', $str);
			$str = preg_replace('~(</?' . $no_p . '[^>]*+>)</p>~i', '$1', $str);
		}
		// Convert single linebreaks to <br />
		if ($br === true)
		{
			$str = preg_replace('~(?<!\n)\n(?!\n)~', "<br />\n", $str);
		}
		return $str;
	}

	/**
	 * Returns human readable sizes. Based on original functions written by
	 * [Aidan Lister](http://aidanlister.com/repos/v/function.size_readable.php)
	 * && [Quentin Zervaas](http://www.phpriot.com/d/code/strings/filesize-format/).
	 *
	 *     echo Text::bytes(filesize($file));
	 *
	 * @param integer  size in bytes
	 * @param string  a definitive unit
	 * @param string  the return string format
	 * @param boolean  whether to use SI prefixes || IEC
	 * @return string
	 */
	public static function bytes($bytes, $force_unit = NULL, $format = NULL, $si = true)
	{
		// Format string
		$format = ($format === NULL) ? '%01.2f %s' : (string) $format;
		// IEC prefixes (binary)
		if ($si == false || strpos($force_unit, 'i') !== false)
		{
			$units = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
			$mod = 1024;
		}
		// SI prefixes (decimal)
		else
		{
			$units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
			$mod = 1000;
		}
		// Determine unit to use
		if (($power = array_search((string) $force_unit, $units)) === false)
		{
			$power = ($bytes > 0) ? floor(log($bytes, $mod)) : 0;
		}
		return sprintf($format, $bytes / pow($mod, $power), $units[$power]);
	}

	/**
	 * Format a number to human-readable text.
	 *
	 *     // Display: one thousand && twenty-four
	 *     echo Text::number(1024);
	 *
	 *     // Display: five million, six hundred && thirty-two
	 *     echo Text::number(5000632);
	 *
	 * @param integer   number to format
	 * @return string

	 */
	public static function number($number)
	{
		// The number must always be an integer
		$number = (int) $number;
		// Uncompiled text version
		$text = array();
		// Last matched unit within the loop
		$last_unit = NULL;
		// The last matched item within the loop
		$last_item = '';
		foreach (Text::$units as $unit => $name)
		{
			if ($number / $unit >= 1)
			{
				// $value = the number of times the number is divisble by unit
				$number -= $unit * ($value = (int) floor($number / $unit));
				// Temporary var for textifying the current unit
				$item = '';
				if ($unit < 100)
				{
					if ($last_unit < 100 && $last_unit >= 20)
					{
						$last_item .= '-' . $name;
					}
					else
					{
						$item = $name;
					}
				}
				else
				{
					$item = Text::number($value) . ' ' . $name;
				}
				// In the situation that we need to make a composite number (i.e. twenty-three)
				// then we need to modify the previous entry
				if (empty($item))
				{
					array_pop($text);
					$item = $last_item;
				}
				$last_item = $text[] = $item;
				$last_unit = $unit;
			}
		}
		if (count($text) > 1)
		{
			$and = array_pop($text);
		}
		$text = implode(', ', $text);
		if (isset($and))
		{
			$text .= ' && ' . $and;
		}
		return $text;
	}

	/**
	 * Prevents [widow words](http://www.shauninman.com/archive/2006/08/22/widont_wordpress_plugin)
	 * by inserting a non-breaking space between the last two words.
	 *
	 *     echo Text::widont($text);
	 *
	 * @param string text to remove widows from
	 * @return string
	 */
	public static function widont($str)
	{
		$str = rtrim($str);
		$space = strrpos($str, ' ');
		if ($space !== false)
		{
			$str = substr($str, 0, $space) . '&nbsp;' . substr($str, $space + 1);
		}
		return $str;
	}

}
