<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 站点模型。
 *
 * @package	BootCMS
 * @category	模型
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Model_Site extends Model {

	private $_values = NULL;
	private $_loaded = false;

	/**
	 * 创建并返回一个新的模型对象
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
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "sites` WHERE `id` = $id";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 更新站点信息
	 * @return	mixed	影响的行数或布尔值
	 */
	public function update()
	{
		if ($this->_loaded)
		{
			$where = "`id` = {$this->_values->id}";
			return $this->_db->update('sites', $this->_values, $where);
		}
		return false;
	}

	/**
	 * 取得所有站点
	 * @return	array	数据
	 */
	public function findAll()
	{
		$result = array();
		$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "sites`";
		$result = $this->_db->selectArray($sql);
		return $result;
	}

}
