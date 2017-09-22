<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 论坛版主模型
 *
 * @package	BootCMS
 * @category	论坛/模型
 * @author		Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class Model_Forum_Moderator extends Model {

	private $_values = NULL;
	private $_loaded = false;

	/**
	 * 创建并返回一个新的模型对象
	 * @return 对象
	 */
	public static function factory($name)
	{
		return parent::factory($name);
	}

	/**
	 * 根据主键加载数据，并返回对象
	 * @return 对象
	 */
	public function load($id = 0)
	{
		if (is_numeric($id) && $id > 0)
		{
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "moderators` WHERE `id` = $id";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 创建新节点
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
			return $this->_db->insert('nodes', $values);
		}
		return false;
	}

	/**
	 * 更新节点
	 * @return 执行结果
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
			return $this->_db->update('nodes', $this->_values, $where);
		}
		return false;
	}

	/**
	 * 删除节点
	 * @return mixed 执行结果
	 */
	public function delete()
	{
		if ($this->_loaded)
		{
			$where = "`id` = {$this->_values->id}";
			return $this->_db->delete('nodes', $where);
		}
		return false;
	}

	/**
	 * 数据验证
	 * @param array $values
	 * @return Validation
	 */
	public static function getValidation($values)
	{
		return Validation::factory($values)
				->rule('node_title', 'not_empty')
				->rule('slug', 'not_empty')
				->rule('node_content', 'not_empty');
	}

	/**
	 * 根据版块ID取得版主列表
	 */
	public function findByForum($fid = 0)
	{
		$result = array();
		if (is_int($fid))
		{
			$sql = "SELECT u.id, u.displayed_name, u.level FROM {$this->_db->tablePrefix}bb_members u, {$this->_db->tablePrefix}bb_moderators m WHERE m.forum_id = $forumId AND m.user_id = u.id ORDER BY u.displayed_name";
			$result = $this->_db->selectArray($sql);
		}
		return $result;
	}

	/**
	 * 取得指定版块ID的版主列表
	 * @param array 版块ID
	 * @return array 版主列表
	 */
	public function findInForum($forumIds = array())
	{
		$sqlIn = '';
		foreach ($forumIds as $fid)
		{
			if ($fid)
				$sqlIn.= $fid . ',';
		}
		$sqlIn = substr($sqlIn, 0, -1);
		if ($sqlIn)
		{
			$sql = "SELECT m.forum_id, u.id, u.displayed_name, u.level FROM {$this->_db->tablePrefix}bb_moderators m, {$this->_db->tablePrefix}bb_members u WHERE m.forum_id IN(" . $sqlIn . ") AND m.user_id = u.id ORDER BY u.displayed_name";
			return $this->_db->selectArray($sql);
		}
		return array();
	}

	/**
	 * 取得某用户管理的所有论坛ID
	 * @param integer 用户ID
	 * @return array 论坛ID数组
	 */
	public function getForumsByUser($userId = 0)
	{
		$result = array();
		if (is_int($userId))
		{
			$sql = "SELECT `forum_id` FROM `{$this->_db->tablePrefix}moderators` WHERE `user_id` = $userId";
			$result = $this->_db->selectArray($sql);
		}
		return $result;
	}

}
