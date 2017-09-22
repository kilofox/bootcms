<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 论坛订阅模型。
 *
 * @package	BootCMS
 * @category	论坛/模型
 * @author		Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class Model_Forum_Subscription extends Model {

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
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "bb_subscriptions` WHERE `id` = $id";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 创建新订阅。
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
			return $this->_db->insert('bb_subscriptions', $values);
		}
		return false;
	}

	/**
	 * 更新订阅。
	 *
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
			return $this->_db->update('subscriptions', $this->_values, $where);
		}
		return false;
	}

	/**
	 * 删除订阅。
	 *
	 * @return mixed 执行结果
	 */
	public function delete()
	{
		if ($this->_loaded)
		{
			$where = "`id` = {$this->_values->id}";
			return $this->_db->delete('subscriptions', $where);
		}
		return false;
	}

	/**
	 * 数据验证。
	 *
	 * @param array $values
	 * @return Validation
	 */
	public static function getValidation($values)
	{
		return Validation::factory($values)
				->rule('topic_id', 'not_empty')
				->rule('topic_id', 'digit')
				->rule('user_id', 'not_empty')
				->rule('user_id', 'digit');
	}

	/**
	 * 取得所有订阅。
	 *
	 * @param string	排序
	 * @return array 数据
	 */
	public function findAll($order_by = '')
	{
		$result = array();
		$order_by = $order_by ? $order_by : 'id';
		$sql = "SELECT * FROM `{$this->_db->tablePrefix}bb_subscriptions` ORDER BY $order_by";
		$result = $this->_db->selectArray($sql);
		return $result;
	}

	/**
	 * 取得某用户对某主题的订阅数。
	 *
	 * @param integer 主题ID
	 * @param integer 用户ID
	 * @return integer 订阅数
	 */
	public function getNumByTopicAndUser($topicId = 0, $userId = 0)
	{
		$result = 0;
		if ($topicId > 0 && $userId > 0)
		{
			$sql = "SELECT COUNT(*) AS subscribed FROM {$this->_db->tablePrefix}bb_subscriptions WHERE topic_id = $topicId AND user_id = $userId";
			$result = $this->_db->select($sql)->subscribed;
		}
		return $result;
	}

	/**
	 * 取得对某主题的除某用户以外的所有订阅。
	 *
	 * @param integer 主题ID
	 * @param integer 用户ID
	 * @return integer 订阅数
	 */
	public function findByTopicExceptUser($topicId = 0, $userId = 0)
	{
		$result = array();
		if ($topicId && $userId)
		{
			$sql = "SELECT s.user_id AS id, u.level, u.email, u.language FROM {$this->_db->tablePrefix}bb_subscriptions s LEFT JOIN {$this->_db->tablePrefix}bb_members u ON s.user_id = u.id WHERE s.topic_id = $topicId AND s.user_id <> $userId";
			$result = $this->_db->selectArray($sql);
		}
		return $result;
	}

}
