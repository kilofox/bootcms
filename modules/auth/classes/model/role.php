<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 角色模型
 *
 * @package		BootPHP/授权
 * @category	模型
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Model_Role extends Model {

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
		$result = NULL;
		if ($id)
		{
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "roles` WHERE `id` = '$id'";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 更新角色信息
	 * @return	执行结果
	 */
	public function update()
	{
		if ($this->_loaded)
		{
			$where = "`id` = '{$this->_values->id}'";
			return $this->_db->update('nodes', $this->_values, $where);
		}
		return false;
	}

	/**
	 * 根据授权码取得角色信息
	 * @param	string	授权码
	 */
	public function getInfoByCode($code)
	{
		$result = NULL;
		if ($code)
		{
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "roles` WHERE `authorization_code` = '$code'";
			$result = $this->_db->select($sql);
		}
		return $result;
	}

	/**
	 * 取得所有角色
	 * @return	array	角色数组
	 */
	public function findAll()
	{
		$result = array();
		$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "roles`";
		$result = $this->_db->selectArray($sql);
		return $result;
	}

}
