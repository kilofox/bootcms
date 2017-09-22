<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 论坛函数类
 *
 * @package	BootCMS
 * @category	辅助类
 * @author		Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class Forumfunctions {

	/**
	 * Disable HTML in a string without disabling entities.
	 *
	 * @param	string	$string	String to un-HTML
	 * @return	string	Parsed $string
	 */
	public static function unhtml($string)
	{
		$string = htmlspecialchars($string);
		// Code which is necessary to not break numeric entities (quirky support for strange encodings on a page).
		// Broken entities (without trailing ;) at string end are stripped since they break XML well-formedness.
		if (strpos($string, '&') !== false)
			$string = preg_replace(array('/&amp;\#(\d+)/', '/&\#?[a-z0-9]+$/'), array('&#$1', ''), $string);
		return $string;
	}

	/**
	 * Gives the length of a string and counts a HTML entitiy as one character.
	 *
	 * @param	string	$string	String to find length of
	 * @return	integer	Length of $string
	 */
	public static function entities_strlen($string)
	{
		if (strpos($string, '&') !== false)
			$string = preg_replace('#&\#?[^;]+;#', '.', $string);
		return strlen($string);
	}

	/**
	 * 将字符串右侧修剪为 $length 个字符，保持实体为一个字符。
	 *
	 * @param	string	$string	要修剪的字符串
	 * @param	integer	$length	新字符串的长度
	 * @return	string	修剪后的字符串
	 */
	public static function entities_rtrim($string, $length)
	{
		if (strpos($string, '&') === false)
			return mb_substr($string, 0, $length);
		$new_string = '';
		$new_length = $pos = 0;
		$entity_open = false;
		while ($pos < mb_strlen($string) && ( $new_length < $length || $entity_open ))
		{
			$char = mb_substr($string, $pos, 1);
			if ($char == '&')
			{
				$entity_open = true;
			}
			elseif ($char == ';' && $entity_open)
			{
				$entity_open = false;
				$new_length++;
			}
			elseif (!$entity_open)
			{
				$new_length++;
			}
			$new_string .= $char;
			$pos++;
		}
		return $new_string;
	}

	/**
	 * 获取配置变量
	 *
	 * @param	string	要取得的设置
	 * @return	mixed	设置的值
	 */
	public static function get_config($setting)
	{
		// 将设置加载到数组中
		$config = BootPHP::$config->load('forum');
		// 设置不存在时，用 false
		return isset($config[$setting]) ? $config[$setting] : false;
	}

	/**
	 * Generate a random key.
	 *
	 * @param	boolean	$is_password	Is the random key used as a password?
	 * @return	string	Random key
	 */
	public static function random_key($is_password = false)
	{
		if (!$is_password)
			return md5(mt_rand());
		$chars = range(33, 126); // ! until ~
		$max = count($chars) - 1;
		$passwd_min_length = (int) self::get_config('passwd_min_length');
		$length = ( $passwd_min_length > 10 ) ? $passwd_min_length : 10;
		do
		{
			$key = '';
			for ($i = 0; $i < $length; $i++)
				$key .= chr($chars[mt_rand(0, $max)]);
			$valid = self::validate_password($key, true);
		}
		while (!$valid);
		return $key;
	}

	/**
	 * 授权
	 *
	 * Defines whether a user has permission to take a certain action.
	 *
	 * @param	object	$user		用户
	 * @param	string	$authInt	授权“整数”
	 * @param	string	$action		要建立的动作
	 * @param	integer	$forumId	论坛ID
	 * @return	boolean	是否允许
	 */
	public static function auth($user, $authInt, $action, $forumId)
	{
		// 将来要使用的禁止IP功能： $user->ip_banned
		if (self::get_config('board_closed') && $user->level < 3 || !$authInt)
			return false;
		// 定义用户等级
		if ($user)
		{
			// 已登录用户
			if ($user->level == 2)
			{
				$forumIds = Model::factory('moderator')->getForumsByUser($user->id);
				$userLevel = in_array($forumId, $forumIds) ? 2 : 1;
			}
			else
			{
				$userLevel = $user->level;
			}
		}
		else
		{
			// 游客
			$userLevel = 0;
		}
		// 获取给定动作对应的整数
		$actions = array(
			'view' => 0,
			'read' => 1,
			'post' => 2,
			'reply' => 3,
			'edit' => 4,
			'move' => 5,
			'delete' => 6,
			'lock' => 7,
			'sticky' => 8,
			'html' => 9
		);
		$minLevel = intval($authInt[$actions[$action]]);
		return $userLevel >= $minLevel;
	}

	/**
	 * 返回版主列表，可点击，用逗号分隔
	 *
	 * @param	integer	$forum		论坛 ID
	 * @param	array	$listarray	Array with all moderators (automatically requested when missing)
	 * @return	string	版主列表
	 */
	public static function get_mods_list($forumId, $listarray = false)
	{
		$forumModerators = array();
		if (is_array($listarray) && count($listarray))
		{
			foreach ($listarray as $modsdata)
			{
				if ($modsdata['forum_id'] == $forumId)
					$forumModerators[] = self::make_profile_link($modsdata['id'], $modsdata['displayed_name'], $modsdata['level']);
			}
			if (!count($forumModerators))
			{
				return '无';
			}
		}
		else
		{
			$moderators = Model::factory('forum_moderator')->findByForum($forumId);
			$forumModerators = array();
			foreach ($moderators as $node)
				$forumModerators[] = self::make_profile_link($node->id, $node->displayed_name, $node->level);
			if (!count($forumModerators))
			{
				return '无';
			}
		}
		// Join all values in the array
		return implode(', ', $forumModerators);
	}

	/**
	 * Return a clickable list of pages.
	 *
	 * @param	integer	$pages_number	Total number of pages
	 * @param	integer	$current_page	Current page
	 * @param	integer	$items_number	Number of items
	 * @param	integer	$items_per_page	Items per page
	 * @param	string	$page_name		.php page name
	 * @param	integer	$page_id_val	URL id GET value
	 * @param	boolean	$back_forward_links	Enable back and forward links
	 * @param	array	$url_vars		Other URL vars
	 * @param	boolean	$force_php		Force linking to .php files
	 * @return	string	HTML
	 */
	public static function make_page_links($pages_number, $current_page, $items_number, $items_per_page, $page_name, $page_id_val = NULL, $back_forward_links = true, $url_vars = array(), $force_php = false)
	{
		global $lang;
		if (intval($items_number) > intval($items_per_page))
		{
			$page_links = array();
			$page_links_groups_length = 4;
			if (!$current_page)
			{
				$current_page = $pages_number + 1;
				$page_links_groups_length++;
			}
			for ($i = 1; $i <= $pages_number; $i++)
			{
				if ($current_page != $i)
				{
					if ($i + $page_links_groups_length >= $current_page && $i - $page_links_groups_length <= $current_page)
					{
						if (valid_int($page_id_val))
							$url_vars['id'] = $page_id_val;
						$url_vars['page'] = $i;
						$page_links[] = '<a href="' . self::make_url($page_name, $url_vars, true, true, $force_php) . '">' . $i . '</a>';
					} else
					{
						if (end($page_links) != '...')
							$page_links[] = '...';
					}
				} else
				{
					$page_links[] = '<strong>' . $i . '</strong>';
				}
			}
			$page_links = implode(' ', $page_links);
			if ($back_forward_links)
			{
				if (valid_int($page_id_val))
					$url_vars['id'] = $page_id_val;
				if ($current_page > 1)
				{
					$url_vars['page'] = $current_page - 1;
					$page_links = '<a href="' . self::make_url($page_name, $url_vars, true, true, $force_php) . '">&lt;</a> ' . $page_links;
				}
				if ($current_page < $pages_number)
				{
					$url_vars['page'] = $current_page + 1;
					$page_links .= ' <a href="' . self::make_url($page_name, $url_vars, true, true, $force_php) . '">&gt;</a>';
				}
				if ($current_page > 2)
				{
					$url_vars['page'] = 1;
					$page_links = '<a href="' . self::make_url($page_name, $url_vars, true, true, $force_php) . '">&laquo;</a> ' . $page_links;
				}
				if ($current_page + 1 < $pages_number)
				{
					$url_vars['page'] = $pages_number;
					$page_links .= ' <a href="' . self::make_url($page_name, $url_vars, true, true, $force_php) . '">&raquo;</a>';
				}
			}
			$page_links = sprintf($lang['PageLinks'], $page_links);
		}
		else
		{
			$page_links = sprintf($lang['PageLinks'], '1');
		}
		return $page_links;
	}

	/**
	 * Removes BBCode.
	 *
	 * @param	string	$string	Text string to clean
	 * @return	string	Cleaned text
	 */
	public static function bbcode_clear($string)
	{
		$existing_tags = array('code', 'b', 'i', 'u', 's', 'img', 'url', 'mailto', 'color', 'size', 'google', 'quote');
		return preg_replace('#\[/?(?:' . implode('|', $existing_tags) . ')(?:=[^\]]*)?\]#i', '', $string);
	}

	/**
	 * Check if a post is empty.
	 *
	 * Checks if the post is empty, with and without BBCode.
	 *
	 * @param	string	$string	Text
	 * @return	boolean	Is empty
	 */
	public static function post_empty($string)
	{
		if (empty($string) || is_array($string))
			return true;
		$copy = $string;
		$copy = self::bbcode_clear($copy);
		if (empty($copy))
			return true;
		return false;
	}

	/**
	 * Cleans up BBCode for parsing.
	 *
	 * Automatically called from within ::markup.
	 *
	 * @param	string	$string	Text string to preparse
	 * @return	string	Corrected BBCoded text
	 */
	public static function bbcode_prepare($string)
	{
		$string = trim($string);
		$existing_tags = array('code', 'b', 'i', 'u', 's', 'img', 'url', 'mailto', 'color', 'size', 'google', 'quote');
		// BBCode tags start with an alphabetic character, eventually followed by non [ and ] characters.
		$parts = array_reverse(preg_split('#(\[/?[a-z][^\[\]]*\])#i', $string, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY));
		$open_tags = $open_parameters = array();
		$new_string = '';
		while (count($parts))
		{
			$part = array_pop($parts);
			$matches = array();
			// Add open tag
			if (preg_match('#^\[([a-z]+)(=[^\]]*)?\]$#i', $part, $matches))
			{
				$matches[1] = strtolower($matches[1]);
				// Transform tags
				if (end($open_tags) == 'code')
				{
					$new_string .= str_replace(array('[', ']'), array('&#91;', '&#93;'), $part);
					continue;
				}
				// Is already open
				if ($matches[1] != 'quote' && in_array($matches[1], $open_tags))
					continue;
				// Only add this if it exists
				if (in_array($matches[1], $existing_tags))
				{
					array_push($open_tags, $matches[1]);
					array_push($open_parameters, ( isset($matches[2]) ) ? $matches[2] : '');
				}
				$new_string .= $part;
				continue;
			}
			// Add close tag
			if (preg_match('#^\[/([a-z]+)\]$#i', $part, $matches))
			{
				$matches[1] = strtolower($matches[1]);
				// Transform tags
				if (end($open_tags) == 'code' && $matches[1] != 'code')
				{
					$new_string .= str_replace(array('[', ']'), array('&#91;', '&#93;'), $part);
					continue;
				}
				// Unexisting tag
				if (!in_array($matches[1], $existing_tags))
				{
					$new_string .= $part;
					continue;
				}
				// Is current open tag
				if (end($open_tags) == $matches[1])
				{
					array_pop($open_tags);
					array_pop($open_parameters);
					$new_string .= $part;
					continue;
				}
				// Is other open tag
				if (in_array($matches[1], $open_tags))
				{
					$to_reopen_tags = $to_reopen_parameters = array();
					while ($open_tag = array_pop($open_tags))
					{
						$open_parameter = array_pop($open_parameters);
						$new_string .= '[/' . $open_tag . ']';
						if ($open_tag == $matches[1])
							break;
						array_push($to_reopen_tags, $open_tag);
						array_push($to_reopen_parameters, $open_parameter);
					}
					$to_reopen_tags = array_reverse($to_reopen_tags);
					$to_reopen_parameters = array_reverse($to_reopen_parameters);
					while ($open_tag = array_pop($to_reopen_tags))
					{
						$open_parameter = array_pop($to_reopen_parameters);
						$new_string .= '[' . $open_tag . $open_parameter . ']';
						array_push($open_tags, $open_tag);
						array_push($open_parameters, $open_parameter);
					}
				}
			}
			else
			{
				// Plain text
				$new_string .= ( end($open_tags) == 'code' && self::get_config('show_raw_entities_in_code') ) ? str_replace('&#', '&amp;#', $part) : $part;
			}
		}
		// Close opened tags
		while ($open_tag = array_pop($open_tags))
		{
			$open_parameter = array_pop($open_parameters);
			$new_string .= '[/' . $open_tag . $open_parameter . ']';
		}
		// Remove empties
		foreach ($existing_tags as $existing_tag)
			$new_string = preg_replace('#\[(' . $existing_tag . ')([^\]]+)?\]\[/(\1)\]#i', '', $new_string);
		return $new_string;
	}

	/**
	 * Apply BBCode and smilies to a string.
	 *
	 * @param	string	$string		String to markup
	 * @param	boolean	$bbcode		Enable BBCode
	 * @param	boolean	$smilies	Enable smilies
	 * @param	boolean	$html		Enable HTML
	 * @param	boolean	$links		Enable links parsing
	 * @param	string	$homeUrl	网站路径
	 * @return	string	HTML
	 */
	public static function markup($string, $bbcode = true, $smilies = true, $html = false, $links = true, $homeUrl = '')
	{
		static $random;
		$config = BootPHP::$config->load('forum');
		$string = preg_replace('/(script|about|applet|activex|chrome):/i', '$1&#058;', $string);
		if (!$html)
			$string = self::unhtml($string);
		if ($smilies)
		{
			$allSmilies = $config['smilies'];
			$string = preg_replace_callback('/:\w{2,15}:/', function($matches) use($allSmilies, $homeUrl) {
				return key_exists($matches[0], $allSmilies) ? '<img src="' . $homeUrl . 'assets/images/forum/smilies/' . $allSmilies[$matches[0]] . '" alt="' . $matches[0] . '"/>' : $matches[0];
			}, $string);
		}
		if ($bbcode)
		{
			$string = ' ' . self::bbcode_prepare($string) . ' ';
			$rel = array();
			if (self::get_config('target_blank'))
				$rel[] = 'external';
			if (self::get_config('rel_nofollow'))
				$rel[] = 'nofollow';
			$rel = count($rel) ? ' rel="' . implode(' ', $rel) . '"' : '';
			// Protect from infinite loops.
			// The while loop to parse nested quote tags has the sad side-effect of entering an infinite loop
			// when the parsed text contains $0 or \0.
			// Admittedly, this is a quick and dirty fix. For a nice "fix" I refer to the stack based parser in 2.0.
			if ($random == NULL)
				$random = self::random_key();
			$string = str_replace(array('$', "\\"), array('&#36;' . $random, '&#92;' . $random), $string);
			// Parse quote tags
			// Might seem a bit difficultly done, but trimming doesn't work the usual way
			while (preg_match("#\[quote\](.*?)\[/quote\]#is", $string, $matches))
			{
				$string = preg_replace("#\[quote\]" . preg_quote($matches[1], '#') . "\[/quote\]#is", '<blockquote class="quote"><div class="title">引用</div><div class="content">' . trim($matches[1]) . '</div></blockquote>', $string);
				unset($matches);
			}
			while (preg_match("#\[quote=(.*?)\](.*?)\[/quote\]#is", $string, $matches))
			{
				$string = preg_replace("#\[quote=" . preg_quote($matches[1], '#') . "\]" . preg_quote($matches[2], '#') . "\[/quote\]#is", '<blockquote class="quote"><div class="title">' . $matches[1] . ' 写到' . '</div><div class="content">' . trim($matches[2]) . '</div></blockquote>', $string);
				unset($matches);
			}
			// Undo the dirty fixing.
			$string = str_replace(array('&#36;' . $random, '&#92;' . $random), array('$', "\\"), $string);
			// Parse code tags
			preg_match_all("#\[code\](.*?)\[/code\]#is", $string, $matches);
			foreach ($matches[1] as $oldpart)
			{
				$newpart = preg_replace(array('#<img src="[^"]+" alt="([^"]+)" />#', "#\n#", "#\r#"), array('\\1', '<br />', ''), $oldpart); // replace smiley image tags
				$string = str_replace('[code]' . $oldpart . '[/code]', '[code]' . $newpart . '[/code]', $string);
			}
			$string = preg_replace("#\[code\](.*?)\[/code\]#is", '<pre class="code">$1</pre>', $string);
			// Parse URL's and e-mail addresses enclosed in special characters
			if ($links)
			{
				$ignore_chars = "([^a-z0-9/]|&\#?[a-z0-9]+;)*?";
				for ($i = 0; $i < 2; $i++)
				{
					$string = preg_replace(array(
						"#([\s]" . $ignore_chars . ")([\w]+?://[\w\#\$%&~/\.\-;:=,\?@\[\]\+\\\\\'!\(\)\*]*?)(" . $ignore_chars . "[\s])#is",
						"#([\s]" . $ignore_chars . ")(www\.[\w\#\$%&~/\.\-;:=,\?@\[\]\+\\\\\'!\(\)\*]*?)(" . $ignore_chars . "[\s])#is",
						"#([\s]" . $ignore_chars . ")([a-z0-9&\-_\.\+]+?@[\w\-]+\.([\w\-\.]+\.)?[\w]+)(" . $ignore_chars . "[\s])#is"
						), array(
						'\\1<a href="\\3" title="\\3"' . $rel . '>\\3</a>\\4',
						'\\1<a href="http://\\3" title="http://\\3"' . $rel . '>\\3</a>\\4',
						'\\1<a href="mailto:\\2" title="\\3">\\3</a>\\5'
						), $string);
				}
			}
			// 所有 BBCode 正则表达式
			$regexps = array(
				// [b]text[/b]
				"#\[b\](.*?)\[/b\]#is" => '<span style="font-weight:700">$1</span>',
				// [i]text[/i]
				"#\[i\](.*?)\[/i\]#is" => '<span style="font-style:italic">$1</span>',
				// [u]text[/u]
				"#\[u\](.*?)\[/u\]#is" => '<span style="text-decoration:underline">$1</span>',
				// [s]text[/s]
				"#\[s\](.*?)\[/s\]#is" => '<del>$1</del>',
				// [img]image[/img]
				"#\[img\]([\w]+?://[\w\#\$%&~/\.\-;:=,\?@\[\]\+\\\\\'!\(\)\*]*?)\[/img\]#is" => ( $links ) ? '<img src="$1" alt="' . $lang['UserPostedImage'] . '" class="user-posted-image" />' : '$1',
				// www.kilofox.net
				"#([\s])(www\.[\w\#\$%&~/\.\-;:=,\?@\[\]\+\\\\\'!\(\)\*]*?)#is" => ( $links ) ? '$1<a href="http://\\2" title="http://\\2"' . $rel . '>\\2</a>\\3' : '$1\\2\\3',
				// ftp.kilofox.net
				"#([\s])(ftp\.[\w\#\$%&~/\.\-;:=,\?@\[\]\+\\\\\'!\(\)\*]*?)([\s])#is" => ( $links ) ? '$1<a href="ftp://\\2" title="ftp://\\2"' . $rel . '>\\2</a>\\3' : '$1\\2\\3',
				// [url]http://www.kilofox.net[/url]
				"#\[url\]([\w]+?://[\w\#\$%&~/\.\-;:=,\?@\[\]\+\\\\\'!\(\)\*]*?)\[/url\]#is" => ( $links ) ? '<a href="$1" title="$1"' . $rel . '>$1</a>' : '$1',
				// [url=http://www.kilofox.net]BootBB[/url]
				"#\[url=([\w]+?://[\w\#\$%&~/\.\-;:=,\?@\[\]\+\\\\\'!\(\)\*]*?)\](.*?)\[/url\]#is" => ( $links ) ? '<a href="$1" title="$1"' . $rel . '>$2</a>' : '$2 [$1]',
				// [mailto]somebody@nonexistent.com[/mailto]
				"#\[mailto\]([a-z0-9&\-_\.\+]+?@[\w\-]+\.([\w\-\.]+\.)?[\w]+)\[/mailto\]#is" => ( $links ) ? '<a href="mailto:$1" title="$1">$1</a>' : '$1',
				// [mailto=somebody@nonexistent.com]mail me[/mailto]
				"#\[mailto=([a-z0-9&\-_\.\+]+?@[\w\-]+\.([\w\-\.]+\.)?[\w]+)\](.*?)\[/mailto\]#is" => ( $links ) ? '<a href="mailto:$1" title="$1">\\3</a>' : '\\3 [$1]',
				// [color=red]text[/color]
				"#\[color=([\#a-z0-9]+)\](.*?)\[/color\]#is" => '<span style="color:$1">$2</span>',
				// [size=14]text[/size]
				"#\[size=([0-9]*?)\](.*?)\[/size\]#is" => '<span style="font-size:$1pt">$2</span>'
			);
			// 解析这些正则表达式
			foreach ($regexps as $find => $replace)
				$string = preg_replace($find, $replace, $string);
			// Remove tags from attributes
			if (strpos($string, '<') !== false)
			{
				preg_match_all('#[a-z]+="[^"]*<[^>]*>[^"]*"#', $string, $matches);
				foreach ($matches[0] as $match)
					$string = str_replace($match, strip_tags($match), $string);
			}
		}
		if (!$html)
		{
			$string = str_replace("\n", "<br />", $string);
			$string = str_replace("\r", "", $string);
		}
		return trim($string);
	}

	/**
	 * Return the BBCode control buttons.
	 *
	 * @param	boolean	$links	Enable controls for links
	 * @return	string	HTML BBCode controls
	 */
	public static function get_bbcode_controls($links = true)
	{
		$controls = array(
			array('[b]', '[/b]', 'B', 'font-weight: bold'),
			array('[i]', '[/i]', 'I', 'font-style: italic'),
			array('[u]', '[/u]', 'U', 'text-decoration: underline'),
			array('[s]', '[/s]', 'S', 'text-decoration: line-through'),
			array('[quote]', '[/quote]', '引用', ''),
			array('[code]', '[/code]', 'Code', ''),
		);
		if ($links)
		{
			$controls = array_merge($controls, array(
				array('[img]', '[/img]', '图片', ''),
				array('[url=http://www.example.com]', '[/url]', 'URL', ''),
			));
		}
		$controls = array_merge($controls, array(
			array('[color=red]', '[/color]', '颜色', ''),
			array('[size=14]', '[/size]', '字号', '')
		));
		$out = array();
		foreach ($controls as $data)
			$out[] = '<a href="javascript:void(0);" data-open="' . $data[0] . '" data-close="' . $data[1] . '" style="' . $data[3] . '">' . $data[2] . '</a>';
		return implode("\n", $out);
	}

	/**
	 * 返回表情控制图片
	 *
	 * @return	string	HTML	表情控制
	 */
	public static function get_smiley_controls($homeUrl = '')
	{
		global $template;
		$config = BootPHP::$config->load('forum');
		$smilies = (array) $config['smilies'];
		$smilies = array_unique($smilies);
		$out = array();
		foreach ($smilies as $pattern => $img)
			$out[] = '<img src="' . $homeUrl . 'assets/images/forum/smilies/' . $img . '" alt="' . self::unhtml($pattern) . '"/>';
		return implode("\n", $out);
	}

	/**
	 * Censor text.
	 *
	 * @param	string	$string	Text to censor
	 * @return	string	Censored text
	 */
	public static function replace_badwords($string)
	{
		return $string;
		$config = BootPHP::$config->load('forum');
		if ($config['enable_badwords_filter'])
		{
			// Algorithm borrowed from phpBB
			if (!isset($config['badwords']))
			{
				$result = $db->query("SELECT word, replacement FROM " . TABLE_PREFIX . "bb_badwords ORDER BY word ASC");
				$badwords = array();
				while ($data = $db->fetch_result($result))
					$badwords['#\b(?:' . str_replace('\*', '\w*?', preg_quote(stripslashes($data['word']), '#')) . ')\b#i'] = stripslashes($data['replacement']);
			}
			foreach (self::badwords as $badword => $replacement)
				$string = preg_replace($badword, $replacement, $string);
		}
		return $string;
	}

	/**
	 * 生成用户的个人资料链接
	 *
	 * @param	string	$homeUrl	网站路径
	 * @param	integer	$user_id	用户ID
	 * @param	string	$username	用户名
	 * @param	integer	$level		等级
	 * @param	string	$title Title	属性
	 * @return	string	HTML
	 */
	public static function make_profile_link($homeUrl, $user_id, $username, $level, $title = NULL)
	{
		switch ($level)
		{
			default:
			case 1:
				$levelClass = '';
				break;
			case 2:
				$levelClass = ' class="moderator"';
				break;
			case 3:
				$levelClass = ' class="administrator"';
				break;
		}
		$title = !empty($title) ? ' title="' . unhtml($title) . '"' : '';
		return '<a href="' . $homeUrl . 'member/profile/' . $user_id . '/"' . $levelClass . $title . '>' . $username . '</a>';
	}

	/**
	 * Validate an email address
	 *
	 * @param	string	$email_address	Email address
	 * @return	boolean	Valid
	 */
	function validate_email($email_address)
	{
		if (!preg_match(EMAIL_PREG, $email_address))
			return false;
		if (self::get_config('enable_email_dns_check'))
		{
			$parts = explode('@', $email_address);
			return checkdnsrr($parts[1], 'MX');
		}
		return true;
	}

	/**
	 * Generate an antispam question.
	 *
	 * @param	integer	$mode	Anti-spam mode
	 */
	function generate_antispam_question($mode)
	{
		global $lang;
		switch ($mode)
		{
			case ANTI_SPAM_MATH:
				// Random math question
				$operator = mt_rand(0, 1);
				if ($operator == 0)
				{
					$num1 = mt_rand(0, 10);
					$num2 = mt_rand(0, 10);
					$_SESSION['antispam_question_question'] = $num1 . ' +  ' . $num2 . ' = ?';
					$_SESSION['antispam_question_answer'] = $num1 + $num2;
				}
				else
				{
					$num1 = mt_rand(1, 10);
					$num2 = mt_rand(1, $num1);
					$_SESSION['antispam_question_question'] = sprintf($lang['AntiSpamQuestionMathMinus'], $num1, $num2);
					$_SESSION['antispam_question_answer'] = $num1 - $num2;
				}
				break;
			case ANTI_SPAM_CUSTOM:
				// Custom admin-defined question
				$questionPairs = self::get_config('antispam_question_questions');
				if (!is_array($questionPairs) || !count($questionPairs))
					trigger_error('No custom anti-spam questions found.', E_USER_ERROR);
				$questions = array_keys($questionPairs);
				$answers = array_values($questionPairs);
				unset($questionPairs);
				$questionId = ( count($questions) == 1 ) ? 0 : mt_rand(0, count($questions) - 1);
				$_SESSION['antispam_question_question'] = $questions[$questionId];
				$_SESSION['antispam_question_answer'] = $answers[$questionId];
				break;
			default:
				trigger_error('Spam check mode ' . $mode . ' does not exist.', E_USER_ERROR);
		}
	}

	/**
	 * Pose the anti-spam question.
	 *
	 * This might render a form and halt further page execution.
	 */
	public static function pose_antispam_question()
	{
		global $template, $lang, $db;
		if (!$user->pose_antispam_question)
			return;
		$template->clear_breadcrumbs();
		$template->add_breadcrumb($lang['AntiSpamQuestion']);
		$mode = (int) self::get_config('antispam_question_mode');
		if (empty($_SESSION['antispam_question_question']))
			self::generate_antispam_question($mode);
		if (isset($_POST['answer']) && !is_array($_POST['answer']) && !strcasecmp(strval($_POST['answer']), strval($_SESSION['antispam_question_answer'])))
		{
			// Question passed, continuing...
			$_SESSION['antispam_question_posed'] = true;
			unset($_SESSION['antispam_question_question'], $_SESSION['antispam_question_answer']);
			self::redirect($_SERVER['PHP_SELF'], $_GET);
			return;
		}
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$template->parse('msgbox', 'global', array(
				'box_title' => $lang['Error'],
				'content' => $lang['AntiSpamWrongAnswer']
			));
		}
		$size = ( $mode === ANTI_SPAM_MATH ) ? 'size="2" maxlength="2"' : 'size="35"';
		$template->parse('anti_spam_question', 'various', array(
			'form_begin' => '<form action="' . self::make_url($_SERVER['PHP_SELF'], $_GET) . '" method="post">',
			'question' => unhtml($_SESSION['antispam_question_question']),
			'answer_input' => '<input type="text" name="answer" id="answer" ' . $size . ' />',
			'submit_button' => '<input type="submit" name="submit" value="' . $lang['Send'] . '" />',
			'form_end' => '</form>'
		));
		$template->set_js_onload("set_focus('answer')");
		// Include the page footer
		require ROOT_PATH . 'sources/page_foot.php';
		exit;
	}

	/**
	 * 生成一个安全令牌。
	 *
	 * @return	string	令牌
	 */
	public static function generate_token()
	{
		static $token;
		if (isset($token))
			return $token;
		list($usec, $sec) = explode(' ', microtime());
		$time = (float) $usec + (float) $sec;
		$key = self::random_key();
		if (!isset($_SESSION['oldest_token']))
			$_SESSION['oldest_token'] = $time;
		// 由于某些原因，在使用 strval() 时，PHP 在逗号与句号之间作为小数点分隔符（PHP 5.3.6 on OS X 10.6.7）
		$stime = number_format($time, 4, '.', '');
		$_SESSION['tokens'][$stime] = $key;
		$token = $stime . '-' . $key;
		return $token;
	}

	/**
	 * Verify a token.
	 *
	 * @param	string	$try_token	Token to test
	 * @return	boolean	Verified
	 */
	public static function verify_token($try_token)
	{
		if (!preg_match('#^[0-9]+\.[0-9]{4}\-[0-9a-f]{32}$#', $try_token))
			return false;
		list($time, $key) = explode('-', $try_token);
		$sess_idx = $time;
		return !empty($_SESSION['tokens'][$sess_idx]) && $_SESSION['tokens'][$sess_idx] === $key;
	}

	/**
	 * Token error.
	 *
	 * Parse a msgbox template with a suitable message.
	 *
	 * @param	string	$type	Error type ("form" or "url")
	 */
	public static function token_error($type)
	{
		$content = '';
		switch ($type)
		{
			case 'form':
				$content = '安全令牌无效或过期。';
				break;
			case 'url':
				$content = '安全令牌无效或过期。';
				break;
		}
		/* print_r(array(
		  'box_title' => '注意',
		  'content' => nl2br($content)
		  )); */
	}

	/**
	 * Verify a form for tokens.
	 *
	 * @param	boolean	$enable_message Enable error message
	 * @return	boolean	Verified
	 */
	public static function verify_form($enable_message = true)
	{
		$result = !empty($_POST['form_token']) && self::verify_token($_POST['form_token']);
		if ($enable_message && !$result)
			self::token_error('form');
		return $result;
	}

	/**
	 * Verify a URL for tokens.
	 *
	 * @param	boolean	$enable_message Enable error message
	 * @return	boolean	Verified
	 */
	public static function verify_url($enable_message = true)
	{
		$get_idx = '_url_token_';
		$result = (!empty($_GET[$get_idx]) && self::verify_token($_GET[$get_idx]) );
		if ($enable_message && !$result)
			self::token_error('url');
		return $result;
	}

	/**
	 * Read a remote URL into string
	 *
	 * @param	string	$url	URL
	 * @return	string	Contents
	 */
	function read_url($url)
	{
		if (function_exists('curl_init') && function_exists('curl_exec'))
		{
			// cURL
			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 3);
			$result = curl_exec($curl);
			if ($result === false)
				return false;
			$contents = trim($result);
			curl_close($curl);
			return $contents;
		}
		// URL fopen()
		if (!ini_get('allow_url_fopen'))
			return false;
		$fp = fopen($url, 'r');
		if (!$fp)
			return false;
		$contents = '';
		if (function_exists('stream_get_contents'))
		{
			// PHP 5 stream
			$result = stream_get_contents($fp);
			if ($result === false)
				return false;
			$contents = trim($result);
		} else
		{
			// fread() packet reading
			while (!feof($fp))
			{
				$result = fread($fp, 8192);
				if ($result === false)
					return false;
				$contents .= $result;
			}
			$contents = trim($contents);
		}
		fclose($fp);
		return $contents;
	}

	/**
	 * Stop Forum Spam API request
	 *
	 * @link http://www.stopforumspam.com/usage
	 *
	 * @param	string	$email	Email address
	 * @return	mixed	false if nothing found, array otherwise
	 */
	function sfs_api_request($email)
	{
		// Not really clean XML parsing code. Will improve for BootBB 2.
		// Session cache
		if (isset($_SESSION['sfs_ban_cache'][$email]))
			return $_SESSION['sfs_ban_cache'][$email];
		$result = self::read_url('http://www.stopforumspam.com/api?email=' . urlencode($email));
		// Failed request
		if ($result === false || !preg_match('#<response[^>]+success="true"[^>]*>#', $result))
			return false;
		// Not in database
		if (strpos($result, '<appears>yes</appears>') === false)
		{
			$_SESSION['sfs_ban_cache'][$email] = false;
			return false;
		}
		$return = array();
		if (preg_match('#<lastseen>([0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})</lastseen>#i', $result, $matches))
			$return['lastseen'] = strtotime($matches[1]);
		if (preg_match('#<frequency>([0-9]+)</frequency>#i', $result, $matches))
			$return['frequency'] = (int) $matches[1];
		$_SESSION['sfs_ban_cache'][$email] = $return;
		return $return;
	}

	/**
	 * Stop Forum Spam email check
	 *
	 * Check Stop Forum Spam for a banned email address.
	 *
	 * @param	string	$email Email address
	 * @return	boolean	Banned
	 */
	function sfs_email_banned($email)
	{
		global $db;
		if (!self::get_config('sfs_email_check'))
			return false;
		$info = self::sfs_api_request($email);
		// Not banned
		if ($info === false)
			return false;
		$min_frequency = self::get_config('sfs_min_frequency');
		$max_lastseen = self::get_config('sfs_max_lastseen');
		// Does not meet requirements
		if (( $min_frequency > 0 && (!isset($info['frequency']) || $info['frequency'] < $min_frequency ) ) || ( $max_lastseen > 0 && (!isset($info['lastseen']) || $info['lastseen'] < time() - $max_lastseen * 86400 ) ))
			return false;
		if (self::get_config('sfs_save_bans'))
			$db->query("INSERT INTO " . TABLE_PREFIX . "bb_bans VALUES(NULL, '', '" . $email . "', '')");
		return true;
	}

	/**
	 * Stop Forum Spam API submit
	 *
	 * Submit account information to the Stop Forum Spam database.
	 *
	 * @param	array	$data	Array with username, email and ip_addr.
	 * @return	boolean	Success
	 */
	function sfs_api_submit($data)
	{
		$key = self::get_config('sfs_api_key');
		if (empty($data['username']) || empty($data['email']) || empty($data['ip_addr']) || empty($key))
			return false;
		$url = 'http://www.stopforumspam.com/add.php'
			. '?username=' . urlencode($data['username'])
			. '&ip_addr=' . urlencode($data['ip_addr'])
			. '&email=' . urlencode($data['email'])
			. '&api_key=' . urlencode($key);
		$result = self::read_url($url);
		return ( $result !== false );
	}

	/**
	 * Active value for user.
	 *
	 * Calculate whether the user gets (in)active or is a potential spammer.
	 *
	 * @param	array	$user User array with active, level and posts.
	 * @param	boolean	$new_post	Whether this is in a query increasing the post count.
	 * @param	boolean	$activate	Whether this is when activating a user.
	 * @return	integer	Active value
	 */
	public static function user_active_value($user = NULL, $new_post = false, $activate = false)
	{
		// Potential spammer status not enabled
		if (!self::get_config('antispam_disable_post_links') && !self::get_config('antispam_disable_profile_links'))
			return 1;
		// New (no) user = potential spammer
		if ($user === NULL)
			return 2;
		// poster_level is sometimes used
		if (!isset($user['level']) && isset($user['poster_level']))
			$user['level'] = $user['poster_level'];
		if (!isset($user['level']))
			trigger_error('Missing data for calculating active value.', E_USER_ERROR);
		// Guests are potential spammers (when enabled)
		if ($user['level'] == 0 && self::get_config('antispam_status_for_guests'))
			return 2;
		// Only for regular members
		if ($user['level'] != 1)
			return 1;
		if (!isset($user['active']))
			trigger_error('Missing data for calculating active value.', E_USER_ERROR);
		// Keep status for no new post or active user, unless is activating
		if (!$activate && (!$new_post || $user['active'] == 1 ))
			return $user['active'];
		if (!isset($user['posts']))
			trigger_error('Missing data for calculating active value.', E_USER_ERROR);
		$max_posts = (int) self::get_config('antispam_status_max_posts');
		if ($new_post)
			$user['posts'] += 1;
		// When max posts is set and user has more posts,
		// user gets active status, otherwise still potential spammer.
		return $max_posts > 0 && $user['posts'] > $max_posts ? 1 : 2;
	}

	/**
	 * 是否为潜在的垃圾帖发布者
	 *
	 * @param	object	用户
	 * @param	boolean	是否为新增帖子
	 * @returns	boolean	是否为潜在的垃圾邮件发送者
	 */
	public static function antispam_is_potential_spammer($user, $new_post = false)
	{
		// 未激活会员都是潜在的垃圾帖发布者，无论是否为开启状态
		$config = BootPHP::$config->load('forum');
		if (($config['antispam_disable_post_links'] || $config['antispam_disable_profile_links']) && $user->level == 1 && $user->active == 0)
			return true;
		return self::user_active_value($user, $new_post) == 2;
	}

	/**
	 * Can post links.
	 *
	 * @param	array	$user		User array with active, level and posts.
	 * @param	boolean	$new_post	Whether this is for a request increasing the post count.
	 * @return	boolean	Whether can post links
	 */
	public static function antispam_can_post_links($user, $new_post = false)
	{
		return false;
		$config = BootPHP::$config->load('forum');
		return !self::antispam_is_potential_spammer($user, $new_post) || !$config['antispam_disable_post_links'];
	}

	/**
	 * Can add profile links.
	 *
	 * @param	array	$user	User array with active, level and posts.
	 * @return	boolean	Whether can add profile links
	 */
	public static function antispam_can_add_profile_links($user)
	{
		return false;
		$config = BootPHP::$config->load('forum');
		return !self::antispam_is_potential_spammer($user, false) || !$config['antispam_disable_profile_links'];
	}

}
