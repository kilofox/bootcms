<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 论坛分类模型。
 *
 * @package	BootCMS
 * @category	论坛/模型
 * @author		Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class Model_Forum_Category extends Model {

	private $_values = NULL;
	private $_loaded = false;

	/**
	 * 创建并返回一个新的模型对象。
	 *
	 * @return 对象
	 */
	public static function factory($name)
	{
		return parent::factory($name);
	}

	/**
	 * 根据主键加载数据，并返回对象。
	 *
	 * @return 对象
	 */
	public function load($id = 0)
	{
		if (is_numeric($id) && $id > 0)
		{
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "bb_cats` WHERE `id` = $id";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 根据名称加载数据，并返回对象。
	 *
	 * @return 对象
	 */
	public function loadByName($name)
	{
		if ($name)
		{
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "bb_cats` WHERE `name` = '$name'";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 创建新版块。
	 *
	 * @param array 键值对
	 * @return mixed 插入的数据ID 或 false
	 */
	public function create($data)
	{
		if (is_object($data))
		{
			// 对象转数组（将来将 Validation 改写成操作对象后，就可以省略此步）
			$_arr = get_object_vars($data);
			foreach ($_arr as $key => $val)
			{
				$values[$key] = $val;
			}
			// 验证数据
			$validation = self::getValidation($values);
			if (!$validation->check())
			{
				throw new Validation_Exception('user', $validation);
			}
			return $this->_db->insert('bb_cats', $values);
		}
		return false;
	}

	/**
	 * 更新版块。
	 *
	 * @return mixed 执行结果
	 */
	public function update()
	{
		if ($this->_loaded)
		{
			// 对象转数组（将来将 Validation 改写成操作对象后，就可以省略此步）
			$_arr = get_object_vars($this->_values);
			foreach ($_arr as $key => $val)
			{
				$values[$key] = $val;
			}
			// 验证数据
			$validation = self::getValidation($values);
			if (!$validation->check())
			{
				throw new Validation_Exception('user', $validation);
			}
			$where = "`id` = {$this->_values->id}";
			return $this->_db->update('bb_cats', $this->_values, $where);
		}
		return false;
	}

	/**
	 * 删除版块。
	 *
	 * @return mixed 执行结果
	 */
	public function delete()
	{
		if ($this->_loaded)
		{
			$where = "`id` = {$this->_values->id}";
			return $this->_db->delete('bb_cats', $where);
		}
		return false;
	}

	/**
	 * 数据验证。
	 *
	 * @param array 要验证的数据
	 * @return Validation
	 */
	public static function getValidation($values)
	{
		return Validation::factory($values)
				->rule('name', 'not_empty')
				->rule('sort_id', 'not_empty')
				->rule('sort_id', 'digit');
	}

	/**
	 * 取得包括分类的版块信息。
	 *
	 * @param integer 分类ID
	 * @return mixed
	 */
	public function findWithCategory($fid = 0)
	{
		$result = NULL;
		if (is_numeric($fid) && $fid > 0)
		{
			$sql = "SELECT f.id, f.name, f.auth, f.topics, f.status, f.hide_mods_list, c.id AS cat_id, c.name AS cat_name FROM {$this->_db->tablePrefix}bb_cats f, {$this->_db->tablePrefix}bb_cats c WHERE f.id = $fid AND f.cat_id = c.id";
			$result = $this->_db->select($sql);
		}
		return $result;
	}

	/**
	 * 取得版块与分类等信息。
	 *
	 * @return mixed
	 */
	public function getForumsAndCategories()
	{
		$sql = "SELECT f.id, f.name, f.descr, f.status, f.topics, f.posts, f.auth, f.hide_mods_list,
			c.id AS cat_id, c.name AS cat_name,
			t.id AS topic_id, t.topic_title, t.last_post_id, t.count_replies,
			p.poster_id, p.post_time,
			u.displayed_name AS poster_name, u.level AS poster_level
			FROM {$this->_db->tablePrefix}bb_cats f
			LEFT JOIN {$this->_db->tablePrefix}bb_topics t ON f.last_topic_id = t.id
			LEFT JOIN {$this->_db->tablePrefix}bb_cats c ON f.cat_id = c.id
			LEFT JOIN {$this->_db->tablePrefix}bb_posts p ON t.last_post_id = p.id
			LEFT JOIN {$this->_db->tablePrefix}bb_members u ON p.poster_id = u.id
			ORDER BY c.sort_id ASC, c.name ASC, f.sort_id ASC, f.name ASC";
		return $this->_db->selectArray($sql);
	}

	/**
	 * 取得所有版块。
	 *
	 * @param string	排序
	 * @return array 数据
	 */
	public function findAll($order_by = '')
	{
		$order_by = $order_by ? $order_by : 'id';
		$sql = "SELECT * FROM `{$this->_db->tablePrefix}bb_cats` ORDER BY $order_by";
		return $this->_db->selectArray($sql);
	}

	/**
	 * 对分类排序。
	 *
	 * @param array 数据
	 * @return integer 影响的行数
	 */
	public function sortCategories($orders)
	{
		$count = 0;
		if (!is_array($orders))
			return $count;
		foreach ($orders as $p)
		{
			$p = explode('|', $p);
			if (count($p) == 2)
			{
				$cid = (int) $p[0];
				$order = (int) $p[1];
				$where = "`id` = $cid";
				$result = $this->_db->update('bb_cats', array('sort_id' => $order), $where);
				if ($result)
					$count++;
			}
		}
		return $count;
	}

}
