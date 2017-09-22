<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 后台管理相关功能。
 *
 * @package	BootCMS
 * @category	辅助类
 * @author		Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class Admin {

	/**
	 * 创建发布选项 <select> 下拉列表。
	 *
	 * @param	integer	当前节点状态
	 * @param	integer	等级
	 * @param	string	类型
	 * @param	string	动作
	 * @return	string	下拉列表
	 */
	public static function publishing_options($cur, $level, $type = 'page', $action = 'edit')
	{
		$options = array(
			'0' => '垃圾筒',
			'2' => '草稿',
			'3' => '待审核'
		);
		if ($type == 'page')
			$minimum = 7;
		else
			$minimum = 5;
		if ($level >= $minimum)
		{
			if ($action == 'create')
				$publish = '立即发布';
			else
				$publish = '已发布';
			$options['1'] = $publish;
		}
		$e = '<select id="status" name="status">';
		foreach ($options as $key => $option)
		{
			if ($cur == $key)
				$selected = ' selected="selected"';
			$e .= '<option value="' . $key . '"' . $selected . '>' . $option . '</option>';
			$selected = '';
		}
		$e .= '</select>';
		return $e;
	}

	/**
	 * 为给定的 controller/action 生成最小等级需求
	 */
	public static function minimumLevel($action)
	{
		$level = array(
			'index' => 6,
			'setting_general' => 8,
			'setting_payment' => 8,
			'cache' => 9,
			'list_logs' => 8,
			'list_pages' => 7,
			'create_page' => 7,
			'edit_page' => 7,
			'delete_page' => 7,
			'list_comments' => 7,
			'delete_comment' => 7,
			'login' => 1,
			'list_users' => 7,
			'edit_user' => 5,
			'delete_user' => 7,
			'list_orders' => 7,
			'list_products' => 7,
			'create_product' => 7,
			'edit_product' => 7,
			'delete_product' => 7,
			'sort_products' => 7,
			'list_product_categories' => 7,
			'create_product_category' => 7,
			'edit_product_category' => 7,
			'delete_product_category' => 7,
			'list_shops' => 7,
			'create_shop' => 7,
			'edit_shop' => 7,
			'delete_shop' => 7,
			'list_shippings' => 7,
			'create_shipping' => 7,
			'edit_shipping' => 7,
			'delete_shipping' => 7,
		);
		if (isset($level[$action]))
			return $level[$action];
		else
			return 9;
	}

}
