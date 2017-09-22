<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 区域模型
 *
 * @package	BootCMS
 * @category	模型
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Model_Region extends Model {

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
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "regions` WHERE `id` = $id";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 创建新的区域。
	 *
	 * @param	array	键值对
	 */
	public function create($data)
	{
		if (is_object($data))
		{
			// 对象转数组（将来将 Validation 改写成操作对象后，就可以省略此步）
			$values = array();
			foreach ($data as $key => $val)
			{
				$values[$key] = $val;
			}
			// 验证数据
			$validation = self::getValidation($values);
			if (!$validation->check())
			{
				throw new Validation_Exception('user', $validation);
			}
			return $this->_db->insert('regions', $data);
		}
		return false;
	}

	public function update()
	{
		if ($this->_loaded)
		{
			// 对象转数组（将来将 Validation 改写成操作对象后，就可以省略此步）
			$values = array();
			foreach ($this->_values as $key => $val)
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
			return $this->_db->update('regions', $this->_values, $where);
		}
		return false;
	}

	/**
	 * 删除区域。
	 *
	 * @return	mixed	执行结果
	 */
	public function delete()
	{
		if ($this->_loaded)
		{
			$where = "`id` = {$this->_values->id}";
			return $this->_db->delete('regions', $where);
		}
		return false;
	}

	/**
	 * 数据验证。
	 *
	 * @param	array	$values
	 * @return	Validation
	 */
	public static function getValidation($values)
	{
		return Validation::factory($values)
				->rule('type', 'not_empty')
				->rule('region_title', 'not_empty');
	}

	/**
	 * 取得所有区域。
	 *
	 * @return	mixed
	 */
	public function findAll()
	{
		$result = array();
		$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "regions`";
		return $this->_db->selectArray($sql);
	}

	/**
	 * 按页取得区域。
	 *
	 */
	public function getRegionsByPage($where = '', $orderBy = '', $start = 0, $limit = 10)
	{
		$where = $where ? 'WHERE ' . $where : '';
		$orderBy = $orderBy ? $orderBy : '`id`';
		$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "regions` $where ORDER BY $orderBy LIMIT $start, $limit";
		$result = $this->_db->selectArray($sql);
		$sql = "SELECT COUNT(*) AS num FROM `" . $this->_db->tablePrefix . "regions` $where";
		$total = $this->_db->select($sql)->num;
		return array($result, $total);
	}

	public function getRegionsNoMenu()
	{
		$result = array();
		$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "regions` WHERE `type` NOT LIKE 'menu%'";
		$result = $this->_db->selectArray($sql);
		return $result;
	}

	public function getAllMenus()
	{
		$result = array();
		$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "regions` WHERE `type` = 0";
		$result = $this->_db->selectArray($sql);
		return $result;
	}

	/**
	 * 取得所有推荐位。
	 *
	 */
	public function getAllRecPos()
	{
		$result = array();
		$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "regions` WHERE `type` = 3";
		$result = $this->_db->selectArray($sql);
		return $result;
	}

	/**
	 * 取得主菜单。
	 *
	 */
	public function getMainMenu()
	{
		$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "regions` WHERE `type` = 0";
		return $this->_db->select($sql);
	}

	/**
	 * 将主菜单设置为普通菜单。
	 *
	 */
	public function dropMainMenu()
	{
		$data = array(
			'type' => 'menu'
		);
		$where = "`type` = 'menu_main'";
		return $this->_db->update('regions', $data, $where);
	}

}
