<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 节点分类模型。
 *
 * @package	BootCMS
 * @category	模型
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Model_Node_Category extends Model {

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
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "node_categories` WHERE `id` = $id";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 创建分类。
	 *
	 */
	public function create($data)
	{
		$this->_db->insert('node_categories', $data);
	}

	/**
	 * 更新分类。
	 *
	 */
	public function update()
	{
		if ($this->_loaded)
		{
			$where = "`id` = {$this->_values->id}";
			return $this->_db->update('node_categories', $this->_values, $where);
		}
		return false;
	}

	/**
	 * 删除分类。
	 *
	 * @return	mixed	影响的行数或布尔值
	 */
	public function delete($id)
	{
		if ($id > 0)
		{
			$where = "`id='$id'";
			return $this->_db->delete('node_categories', $where);
		}
		return false;
	}

	public function getInfoBySlug($slug = 0)
	{
		$result = NULL;
		if ($slug)
		{
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "node_categories` WHERE `slug` = '$slug'";
			$result = $this->_db->select($sql);
		}
		return $result;
	}

	/**
	 * 取得所有分类。
	 *
	 */
	public function getAllCategories()
	{
		$result = array();
		$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "node_categories`";
		$result = $this->_db->selectArray($sql);
		return $result;
	}

}
