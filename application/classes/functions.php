<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 一些实用功能。
 *
 * @package	BootCMS
 * @category	辅助类
 * @author		Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class Functions {

	/**
	 * 根据日期信息创建时间戳。
	 *
	 * @param	integer	月
	 * @param	integer	日
	 * @param	integer	年
	 * @param	integer	小时
	 * @param	integer	分
	 * @return	integer	时间戳
	 */
	public static function toTimestamp($month = 0, $day = 0, $year = 0, $hour = 0, $minute = 0)
	{
		return strtotime($year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minute);
	}

	/**
	 * 从指定字符最后一次出现的位置开始，得到字符串的第二段。
	 */
	public static function nameEnd($filename, $char, $offset = 0)
	{
		$pos = strrpos($filename, $char) + $offset;
		$return = substr($filename, $pos, strlen($filename) - $pos);
		return $return;
	}

	/**
	 * Create a basic M/D/Year Hour:Minute form using the post
	 */
	public static function date_form($label, $name, $years_after = NULL, $years_before = NULL, $current_time = NULL)
	{
		$default_date = getdate();
		if ($current_time != NULL)
			$date = getdate($current_time);
		else
			$date = $default_date;
		$year_end = $default_date['year'] - 15;
		$year = $default_date['year'];
		if ($label != NULL)
			$e = '<label for="inv_date" style="width: 156px;">' . $label . '</label>';
		else
			$e = '';
		// months
		$e .= '<select id="' . $name . 'y" name="' . $name . 'y">';
		if (isset($_POST[$name . 'y']))
			$selected = $_POST[$name . 'y'];
		else
			$selected = $date['year'];
		if ($years_before != NULL)
			$year_end = $date['year'] - $years_before;
		if ($years_after != NULL)
			$year = $date['year'] + $years_after;
		for ($i = $year; $i >= $year_end; $i--)
		{
			if ($i == $selected)
				$selected_tag = ' selected="selected"';
			else
				$selected_tag = '';
			$e .= '<option value="' . $i . '"' . $selected_tag . '>' . $i . '</option>';
		} // years
		$e .= '</select> 年
			<select id="' . $name . 'm" name="' . $name . 'm">';
		if (isset($_POST[$name . 'm']))
			$selected = $_POST[$name . 'm'];
		else
			$selected = $date['mon'];
		for ($i = 1; $i <= 12; $i++)
		{
			if ($i == $selected)
				$selected_tag = ' selected="selected"';
			else
				$selected_tag = '';
			$e .= '<option value="' . $i . '"' . $selected_tag . '>' . $i . '</option>';
		}
		$e .= '</select> 月
			<select id="' . $name . 'd" name="' . $name . 'd">';
		if (isset($_POST[$name . 'd']))
			$selected = $_POST[$name . 'd'];
		else
			$selected = $date['mday'];
		for ($i = 1; $i <= 31; $i++)
		{
			if ($i == $selected)
				$selected_tag = ' selected="selected"';
			else
				$selected_tag = '';
			$e .= '<option value="' . $i . '"' . $selected_tag . '>' . $i . '</option>';
		} // days
		$e .= '</select> 日
			<select id="' . $name . 'hours" name="' . $name . 'hours">';
		if (isset($_POST[$name . 'hours']))
			$selected = $_POST[$name . 'hours'];
		else
			$selected = $date['hours'];
		for ($i = 0; $i < 24; $i++)
		{
			if ($i == $selected)
				$selected_tag = ' selected="selected"';
			else
				$selected_tag = '';
			$e .= '<option value="' . str_pad($i, 2, '0', STR_PAD_LEFT) . '"' . $selected_tag . '>' . str_pad($i, 2, '0', STR_PAD_LEFT) . '</option>';
		} // Hours
		$e .= '</select> :
			<select id="' . $name . 'mins" name="' . $name . 'mins">';
		if (isset($_POST[$name . 'mins']))
			$selected = $_POST[$name . 'mins'];
		else
			$selected = $date['minutes'];
		for ($i = 0; $i < 60; $i++)
		{
			if ($i == $selected)
				$selected_tag = ' selected="selected"';
			else
				$selected_tag = '';
			$e .= '<option value="' . str_pad($i, 2, '0', STR_PAD_LEFT) . '"' . $selected_tag . '>' . str_pad($i, 2, '0', STR_PAD_LEFT) . '</option>';
		} // 秒
		$e .= '</select>';
		return $e;
	}

	/**
	 * 生成 6 位（默认）授权码，用于用户注册
	 * @return	string	授权码
	 */
	public static function generateCode($length = 6)
	{
		do
		{
			$code = '';
			$charPool = '0123456789abcdefghijklmnopqrstuvwxyz';
			for ($p = 0; $p < $length; $p++)
				$code .= $charPool[mt_rand(0, strlen($charPool) - 1)];
			$check = Model::factory('role')->getInfoByCode($code);
		}
		while (!empty($check));
		return $code;
	}

	/**
	 * 检查用户是否拥有足够的权限
	 */
	public static function login_level_check($user, $required = 4, $views = 'manage')
	{
		if (!$user && $required != 0)
			return array('views_login', 'manage/login');
		else if ($required == 0)
			return array('views_login');
		if ($user->role_id >= $required)
			return array('views_' . $views);
		else
			return array('views_login', 'manage/denied');
	}

	/**
	 * 将字符串转换为链接样式
	 */
	public static function toLink($value)
	{
		$value = preg_replace(array('/[^\x{4e00}-\x{9fa5}a-z0-9-]/ui', '/-{2,}/'), '-', $value);
		return strtolower(trim($value, '-'));
	}

	/**
	 * 根据给定的时间戳生成日期
	 *
	 * @param integer $stamp Unix 时间戳
	 * @param string $format 日期格式
	 * @return	string 日期
	 */
	public static function makeDate($stamp, $format = '')
	{
		$cache = Cache::instance();
		if (!$config = $cache->get('site_config', false))
		{
			$config = Model::factory('site')->load(1);
			$cache->set('site_config', $config);
		}
		$format = $format ? $format : (empty($config->date_format) ? 'Y-m-d H:i' : $config->date_format);
		$timezone = empty($config->timezone) ? 0 : (int) $config->timezone;
		return gmdate($format, $stamp + 3600 * $timezone);
	}

	/**
	 * 后台分页（Ajax）
	 *
	 * @param	integer	当前页数
	 * @param	integer	数据总条数
	 * @param	integer	每页显示条数
	 * @param	string	分页的链接
	 * @return	string	分页
	 */
	public static function pageAdmin($page = 1, $total = 0, $numPerPage = 10)
	{
		// 总页数
		$totalPages = ceil($total / $numPerPage);
		$totalPages = $totalPages < 1 ? 1 : $totalPages;
		// 要显示的分页数量
		$numToDisplay = 10;
		$numHalf = ceil($numToDisplay / 2);
		$pages = '<div class="page">';
		if ($page > 1)
		{
			$prePage = $page - 1;
			$pages.= '<a class="prev">上一页</a>';
		}
		if ($page > $numHalf + 1)
			$pages.= "<a>1</a><span class=\"omission\">...</span>";
		for ($i = $page - $numHalf; $i <= $page - 1; $i++)
		{
			if ($i < 1)
				continue;
			$numToDisplay--;
			$pages.= "<a>$i</a>";
		}
		$pages.= "<a class=\"active\">$page</a>";
		if ($page < $totalPages)
		{
			$flag = 2;
			for ($i = $page + 1; $i <= $totalPages; $i++)
			{
				$pages.= "<a>$i</a>";
				if (++$flag > $numToDisplay)
					break;
			}
		}
		if ($i < $totalPages)
			$pages.= "<span class=\"omission\">...</span><a>{$totalPages}</a>";
		if ($page < $totalPages)
		{
			$nextPage = $page + 1;
			$pages.= '<a class="next">下一页</a>';
		}
		$pages.= "（共 $total 条记录）</div>";
		return $pages;
	}

	/**
	 * 前台分页
	 *
	 * @param	integer	$page		当前页数
	 * @param	integer	$total		数据总条数
	 * @param	integer	$numPerPage	每页显示条数
	 * @param	string	$url		分页链接
	 * @param	string	$suffix		分页链接后缀
	 * @return	string	分页
	 */
	public static function page($page = 1, $total = 0, $numPerPage = 10, $url = '', $suffix = '')
	{
		// 总页数
		$totalPages = ceil($total / $numPerPage);
		$totalPages = $totalPages < 1 ? 1 : $totalPages;
		// 要显示的分页数量
		$numToDisplay = 10;
		$numHalf = ceil($numToDisplay / 2);
		$pages = '';
		if ($page > 1)
		{
			$prePage = $page - 1;
			$pages.= "<a href=\"{$url}{$prePage}{$suffix}\" class=\"prev\">&lt;</a>";
		}
		if ($page > $numHalf + 1)
			$pages.= "<a href=\"{$url}1{$suffix}\">1 ...</a>";
		for ($i = $page - $numHalf; $i <= $page - 1; $i++)
		{
			if ($i < 1)
				continue;
			$numToDisplay--;
			$pages.= "<a href=\"{$url}{$i}{$suffix}\">$i</a>";
		}
		$pages.= "<a class=\"current\">$page</a>";
		if ($page < $totalPages)
		{
			$flag = 2;
			for ($i = $page + 1; $i <= $totalPages; $i++)
			{
				$pages.= "<a href=\"{$url}{$i}{$suffix}\">$i</a>";
				if (++$flag > $numToDisplay)
					break;
			}
		}
		if ($i < $totalPages)
			$pages.= "<a href=\"{$url}{$totalPages}{$suffix}\">... {$totalPages}</a>";
		if ($page < $totalPages)
		{
			$nextPage = $page + 1;
			$pages.= "<a href=\"{$url}{$nextPage}{$suffix}\" class=\"next\">&gt;</a>";
		}
		return $pages;
	}

	/**
	 * 用于过滤标签，输出没有 HTML 的干净的文本。
	 *
	 * @param	string	$text	文本内容
	 * @return	string	处理后内容
	 */
	public static function text($value)
	{
		$value = nl2br((string) $value);
		$value = strip_tags(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
		$value = addslashes(trim($value));

		return $value;
	}

}
