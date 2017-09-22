<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 产品分类模型。
 *
 * @package	BootCMS
 * @category	模型
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Model_Product_Category extends Model {

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
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "product_categories` WHERE `id` = $id";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 根据名称加载数据，并返回对象。
	 *
	 * @return	对象
	 */
	public function loadByName($name)
	{
		if ($name)
		{
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "product_categories` WHERE `name` = '$name'";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 创建产品分类。
	 *
	 * @param	object	要创建的对象
	 * @return	mixed	创建结果
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
			return $this->_db->insert('product_categories', $data);
		}
		return false;
	}

	/**
	 * 更新产品分类。
	 *
	 * @return	mixed	更新结果
	 */
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
			return $this->_db->update('product_categories', $this->_values, $where);
		}
		return false;
	}

	/**
	 * 删除产品分类。
	 *
	 * @param	integer	要删除的 ID
	 * @return	mixed	影响的行数或布尔值
	 */
	public function delete($id)
	{
		if ($this->_loaded)
		{
			$where = "`id` = {$this->_values->id}";
			return $this->_db->delete('product_categories', $where);
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
				->rule('name', 'not_empty');
	}

	/**
	 * 取得所有产品产品分类
	 */
	public function findAll()
	{
		$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "product_categories`";
		return $this->_db->selectArray($sql);
	}

	/**
	 * 对产品分类排序。
	 *
	 * @param	array	数据
	 * @return	integer	影响的行数
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
				$result = $this->_db->update('product_categories', array('list_order' => $order), $where);
				if ($result)
					$count++;
			}
		}
		return $count;
	}

	/**
	 * 按顺序取得所有产品产品分类。
	 *
	 * @return	array	数据
	 */
	public function findByOrder()
	{
		$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "product_categories` ORDER BY `list_order`";
		return $this->_db->selectArray($sql);
	}

}
