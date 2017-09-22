<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 一些设置网站的功能。例如设置 CSS 文件、Javascript 文件，等等。
 *
 * @package	BootCMS
 * @category	辅助类
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Setup {

	/**
	 * 循环样式表数组，并将其输出。
	 *
	 */
	public static function css($css)
	{
		$output = '';
		foreach ($css as $style)
		{
			$output.= HTML::style($style[0], $style[1]) . "\n";
		}
		return $output;
	}

	/**
	 * 循环脚本数组，并将其输出。
	 *
	 */
	public static function js($js)
	{
		$output = '';
		foreach ($js as $script)
		{
			$output.= HTML::script($script[0], $script[1]) . "\n";
		}
		return $output;
	}

	/**
	 * 生成侧边栏。
	 *
	 */
	public static function makeSidebar($sidebarId, $homeUrl = '/')
	{
		$cache = Cache::instance();
		if (!($sidebar = $cache->get('sidebar-' . $sidebarId, false)))
		{
			$block = Model::factory('region_block')->load($sidebarId);
			$sidebar = '<h3>' . $block->block_title . '</h3><p>' . $block->block_content . '</p>';
			$cache->set('sidebar-' . $block->id, $sidebar);
		}
		return $sidebar;
	}

	/**
	 * 将主菜单分解成网站的布局样式。
	 *
	 */
	public static function makeMainMenu($cur, $homeUrl = '/')
	{
		$cache = Cache::instance();
		if (!($menus = $cache->get('menus', false)))
		{
			$region = Model::factory('Region')->getMainMenu();
			if ($region->id)
			{
				$menus = Model::factory('region_block')->findByRegion($region->id);
				$cache->set('menus', $menus);
			}
		}
		$mainMenu = '';
		foreach ($menus as $menu)
		{
			if ($menu->status == '0')
			{
				$active = $menu->block_content == $cur ? ' class="selected"' : '';
				if (strpos($menu->block_content, 'http://') !== false || strpos($menu->block_content, 'https://') !== false)
					$link = '<a href="' . $menu->block_content . '" target="_blank">' . $menu->block_title . '</a>';
				else
					$link = '<a href="' . $homeUrl . $menu->block_content . '">' . $menu->block_title . '</a>';
				$mainMenu.= '<li' . $active . '>' . $link . '</li>';
			}
		}
		return $mainMenu;
	}

	/**
	 * 根据给定的选项返回合适的版权信息。
	 *
	 */
	public static function copyright($option = 'main')
	{
		$year = date('Y');
		$site = Model::factory('Site')->load(1);
		switch ($option)
		{
			case 'login':
				$e = 'Copyright &copy; 2005 - ' . $year . ' <strong><a href="http://www.kilofox.net" target="_blank">Kilofox Studio</a></strong>. All rights reserved.<br /><a href="http://www.kilofox.net" target="_blank">千狐工作室</a> 版权所有<br />Powered by <strong><a href="http://bootcms.kilofox.net" target="_blank">BootCMS</a></strong>, based on <strong><a href="http://www.kilofox.net/bootphp" target="_blank">BootPHP</a></strong>.';
				break;
			case 'admin':
				$e = 'Powered by <strong><a href="http://bootcms.kilofox.net" target="_blank">BootCMS</a></strong> v' . $site->version . ', based on <strong><a href="http://www.kilofox.net/bootphp" target="_blank">BootPHP</a></strong>.';
				break;
			case 'main':
				$e = 'Copyright &copy; 2005 - ' . $year . ' <strong><a href="http://www.kilofox.net" target="_blank">Kilofox Studio</a></strong>. All rights reserved.<br /><a href="http://www.kilofox.net" target="_blank">千狐工作室</a> 版权所有<br />Powered by <strong><a href="http://bootcms.kilofox.net" target="_blank">BootCMS</a></strong>, based on <strong><a href="http://www.kilofox.net/bootphp" target="_blank">BootPHP</a></strong>.';
				break;
		}
		return $e;
	}

	/**
	 * Returns the appropriate copyright message based on the given option.
	 * If the chosen option is not valid, it will display the default copyright message.
	 * company_phone_number
	 */
	public static function siteInfo($option = '*')
	{
		//$cache = Cache::instance();
		// 设置缓存
		//if ( !($website = $cache->get('site', false)) )
		//{
		$website = Model::factory('Site')->findAll();
		//	$cache->set('site', $website);
		//}
		if ($option != '*' && isset($website[0]->$option))
			return $website[0]->$option;
		else if (isset($website[0]))
			return $website[0];
	}

	/**
	 * 返回叠加信息。通常用于错误显示。
	 */
	public static function message($msg = array())
	{
		if (!is_array($msg))
			return false;
		$e = '';
		$br = '';
		// 如果有错误
		if (isset($msg['message']) && is_array($msg['errors']))
		{
			$e .= '<p class="red">';
			// 循环错误
			foreach ($msg['message'] as $error_msg)
			{
				if (is_string($error_msg))
				{
					$e .= $br . $error_msg;
					$br = '<br />';
				}
				else if (is_array($error_msg))
				{
					foreach ($error_msg as $error)
					{
						$e .= $br . $error;
						$br = '<br />';
					}
				}
			}
			$e .= '</p>';
		}
		if ($e)
		{
			return '<div class="alert_box">
					<div class="filter"></div>
					<div class="msg_box">' . $e . '
						<a class="close_btn" href="javascript:void(0);">关 闭</a>
					</div>
				</div>';
		}
	}

	/**
	 * Explode and then impode the category IDs into category names
	 */
	public static function explode_categories($cats, $homeUrl = '/', $link = true, $find = NULL)
	{
		$cache = Cache::instance();
		$cat_search = explode(';;;;', $cats);
		$names = array();
		foreach ($cat_search as $cat)
		{
			// 设置缓存
			if (!($this_cat = $cache->get('cat-' . $cat, false)))
			{
				$this_cat = Model::factory('node_category')->load($cat);
				if (isset($this_cat->id))
					$cache->set('cat-' . $cat, $this_cat);
			}
			if ($link)
				$names[] = '<a href="' . $homeUrl . 'game/category/' . $this_cat->slug . '">' . $this_cat->name . '</a>';
			else if ($find != NULL && $find == $this_cat->id)
				$names[] = '<em class="red">' . $this_cat->name . '</em>';
			else
				$names[] = $this_cat->name;
		}
		return implode(', ', $names);
	}

	/**
	 * 查找分类信息
	 */
	public static function category_info($id, $find)
	{
		$cache = Cache::instance();
		// 为侧边栏设置缓存
		if (!($cat = $cache->get('cat-' . $id, false)))
		{
			$cat = Model::factory('node_category')->load($id);
			if (isset($cat->id))
				$cache->set('cat-' . $id, $cat);
		}
		return $cat->$find;
	}

	/**
	 * Get the default webpage title for the <title> tag
	 */
	public static function tags($order = 0)
	{
		//$cache = Cache::instance();
		// 设置缓存
		//if ( !($website = $cache->get('site', false)) )
		//{
		$website = Model::factory('Site')->findAll();
		//	$cache->set('site', $website);
		//}
		return array(
			'site_title' => $website[$order]->site_title,
			'site_description' => $website[$order]->site_description,
			'meta_keywords' => $website[$order]->meta_keywords,
			'meta_description' => $website[$order]->meta_description,
			'primary_number' => $website[$order]->phone
		);
	}

}
