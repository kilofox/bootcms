<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 论坛板块模型。
 *
 * @package	BootCMS
 * @category	论坛/模型
 * @author		Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class Model_Forum_Member extends Model {

	private $_values = null;
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
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "bb_members` WHERE `id` = $id";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 根据主键加载会员及用户数据，并返回对象。
	 *
	 * @return 对象
	 */
	public function loadByUser($userId = 0)
	{
		if ($userId > 0)
		{
			$sql = "SELECT * FROM `{$this->_db->tablePrefix}bb_members` WHERE `user_id` = $userId";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 根据主键加载会员及用户数据，并返回对象。
	 *
	 * @return 对象
	 */
	public function loadWithUser($userId = 0)
	{
		$values = NULL;
		if ($userId > 0)
		{
			$sql = "SELECT m.*, u.created, u.last_login FROM `{$this->_db->tablePrefix}bb_members` m LEFT JOIN `{$this->_db->tablePrefix}users` u ON m.user_id = u.id WHERE m.`user_id` = $userId";
			$values = $this->_db->select($sql);
		}
		return $values;
	}

	/**
	 * 加载最新会员。
	 *
	 * @return 对象
	 */
	public function loadLatest()
	{
		$sql = "SELECT user_id, nickname FROM `" . $this->_db->tablePrefix . "bb_members` ORDER BY `id` DESC LIMIT 1";
		$this->_values = $this->_db->select($sql);
		$this->_loaded = true;
		return $this->_values;
	}

	/**
	 * 创建新会员。
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
			return $this->_db->insert('bb_members', $values);
		}
		return false;
	}

	/**
	 * 更新会员。
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
			return $this->_db->update('bb_members', $this->_values, $where);
		}
		return false;
	}

	/**
	 * 删除会员。
	 *
	 * @return mixed 执行结果
	 */
	public function delete()
	{
		if ($this->_loaded)
		{
			$where = "`id` = {$this->_values->id}";
			return $this->_db->delete('bb_members', $where);
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
				->rule('user_id', 'not_empty')
				->rule('user_id', 'digit');
	}

	/**
	 * 取得所有会员。
	 *
	 * @param string	排序
	 * @return array 数据
	 */
	public function findAll($order_by = '')
	{
		$order_by = $order_by ? $order_by : 'id';
		$sql = "SELECT * FROM `{$this->_db->tablePrefix}bb_members` ORDER BY $order_by";
		return $this->_db->selectArray($sql);
	}

	/**
	 * 根据ID更新指定会员。
	 *
	 * @return mixed 执行结果
	 */
	public function updateMember($member)
	{
		if (!isset($member->user_id) || $member->user_id <= 0)
			return false;
		// 对象转数组（将来将 Validation 改写成操作对象后，就可以省略此步）
		foreach ($member as $key => $val)
		{
			$values[$key] = $val;
		}
		// 验证数据
		$validation = self::getValidation($values);
		if (!$validation->check())
		{
			throw new Validation_Exception('user', $validation);
		}
		$where = "`user_id` = {$member->user_id}";
		unset($member->user_id);
		return $this->_db->update('bb_members', $member, $where);
	}

	/**
	 * 根据会员名称查找会员ID。
	 *
	 * @param array 会员名称列表
	 * @return array 执行结果
	 */
	public function findIdsByNames($names)
	{
		$ids = array();
		if (is_array($names))
		{
			foreach ($names as &$m)
			{
				if (!$m)
					unset($m);
			}
			if ($names)
			{
				$memberNames = "'" . implode("','", $memberNames) . "'";
				$sql = "SELECT `id` FROM `{$this->_db->tablePrefix}bb_members` WHERE `name` IN ($memberNames)";
				$ids = $this->_db->selectArray($sql);
			}
		}
		return $ids;
	}

}
