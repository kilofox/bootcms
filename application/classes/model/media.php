<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 媒体模型。
 *
 * @package	BootCMS
 * @category	模型
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Model_Media extends Model {

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
	 * 根据主键加载数据，并返回对象
	 * @return	对象
	 */
	public function load($id = 0)
	{
		if (is_numeric($id) && $id > 0)
		{
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "media` WHERE `id` = $id";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 创建媒体信息
	 * @return	mixed	插入的数据ID 或 false
	 */
	public function create($data)
	{
		return $this->_db->insert('media', $data);
	}

	/**
	 * 更新媒体信息
	 * @return	mixed	执行结果
	 */
	public function update()
	{
		if ($this->_values->id)
		{
			$where = "`id` = {$this->_values->id}";
			return $this->_db->update('media', $this->_values, $where);
		}
		return false;
	}

	/**
	 * 删除媒体信息
	 * @return	mixed	执行结果
	 */
	public function delete()
	{
		if ($this->_values->id)
		{
			$where = "`id` = {$this->_values->id}";
			return $this->_db->delete('media', $where);
		}
		return false;
	}

	/**
	 * 根据类型取得媒体
	 * @param	string	媒体类型，0 为图片
	 * @return	array	数据
	 */
	public function findByType($type = 0)
	{
		$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "media` WHERE `type` = $type";
		$result = $this->_db->selectArray($sql);
		return $result;
	}

	/**
	 * 按分页取得媒体
	 * @param	string	查询条件
	 * @param	string	排序方式
	 * @param	integer	开始
	 * @param	integer	数量
	 * @return	array	数据
	 */
	public function findByPage($where = array(), $orderBy = '', $start = 0, $limit = 10)
	{
		$orderBy = $orderBy ? 'm.' . $orderBy : 'm.id';
		$sql = "SELECT m.*, g.`group_name`, g.`slug` FROM `" . $this->_db->tablePrefix . "media` m LEFT JOIN `" . $this->_db->tablePrefix . "media_groups` g ON m.`group` = g.`id` WHERE m.`type` = 0 AND $where ORDER BY $orderBy LIMIT $start, $limit";
		$result = $this->_db->selectArray($sql);
		$sql = "SELECT COUNT(*) AS num FROM `" . $this->_db->tablePrefix . "media` WHERE `type` = 0 AND $where";
		$total = $this->_db->select($sql)->num;
		return array($result, $total);
	}

	/**
	 * 根据ID取得媒体
	 * @param	array	媒体ID
	 * @return	array	数据
	 */
	public function findByIds($ids)
	{
		$result = array();
		if (is_array($ids) && count($ids))
		{
			$ids = implode(',', $ids);
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "media` WHERE `id` IN ($ids)";
			$result = $this->_db->selectArray($sql);
		}
		return $result;
	}

	/**
	 * 根据ID取得媒体
	 * @param	integer	媒体分组
	 * @return	array	数据
	 */
	public function findByGroup($group = 0)
	{
		$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "media` WHERE `group` = $group";
		$result = $this->_db->selectArray($sql);
		return $result;
	}

}
