<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 喜欢模型。
 *
 * @package	BootCMS
 * @category	模型
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Model_Picture_Like extends Model {

	private $_values = NULL;
	private $_loaded = false;

	/**
	 * 创建并返回一个新的模型对象。
	 *
	 * @return	对象
	 */
	public static function factory($name)
	{
		return parent::factory($name);
	}

	/**
	 * 根据主键加载数据，并返回对象。
	 *
	 * @return	对象
	 */
	public function load($id = 0)
	{
		if (is_numeric($id) && $id > 0)
		{
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "node_likes` WHERE `id` = $id";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 创建新喜欢。
	 *
	 * @param	array	键值对
	 * @return	mixed	插入的数据ID 或 false
	 */
	public function create($data)
	{
		if (is_object($data))
		{
			return $this->_db->insert('node_likes', $data);
		}
		return false;
	}

	/**
	 * 更新喜欢信息。
	 *
	 * @return	mixed	影响的行数或布尔值
	 */
	public function update()
	{
		if ($this->_loaded)
		{
			$where = "`id` = {$this->_values->id}";
			return $this->_db->update('node_likes', $this->_values, $where);
		}
		return false;
	}

	/**
	 * 取得所有喜欢。
	 *
	 * @return	array	数据
	 */
	public function findAll()
	{
		$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "node_likes`";
		return $this->_db->selectArray($sql);
	}

	/**
	 * 用户是否已喜欢某图片。
	 *
	 * @param	integer	用户IP地址
	 * @param	integer	图片ID
	 * @return	boolean
	 */
	public function isLiked($ipAddr, $picId = 0, $timeLimit = 86400)
	{
		// 删除已过期评分记录
		if (is_int($timeLimit))
		{
			$aimTime = time() - $timeLimit;
			$where = "`like_time` < $aimTime";
			$this->_db->delete('node_likes', $where);
		}
		// 检查近期是否喜欢过该图片
		if ($picId)
		{
			$sql = "SELECT `id` FROM `" . $this->_db->tablePrefix . "node_likes` WHERE `node_id` = $picId AND `ip` = '$ipAddr'";
			$result = $this->_db->select($sql);
			return $result->id ? true : false;
		}
		return true;
	}

}
