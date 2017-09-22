<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 产品模型
 *
 * @package	BootCMS
 * @category	模型
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Model_Product extends Model {

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
	 * 根据主键加载数据，并返回对象。
	 *
	 * @return	对象
	 */
	public function load($id = 0)
	{
		if (is_numeric($id) && $id > 0)
		{
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "products` WHERE `id` = $id";
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
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "products` WHERE `product_name` = '$name'";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 创建新产品。
	 *
	 * @param	array	键值对
	 * @return	mixed	插入的数据ID 或 false
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
			return $this->_db->insert('products', $values);
		}
		return false;
	}

	/**
	 * 更新产品。
	 *
	 * @return	执行结果
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
			return $this->_db->update('products', $this->_values, $where);
		}
		return false;
	}

	/**
	 * 删除产品。
	 *
	 * @return	mixed	执行结果
	 */
	public function delete()
	{
		if ($this->_loaded)
		{
			$where = "`id` = {$this->_values->id}";
			return $this->_db->delete('products', $where);
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
				->rule('category', 'not_empty')
				->rule('product_name', 'not_empty')
				->rule('commodity_price', 'not_empty')
				->rule('promotion_price', 'numeric');
	}

	/**
	 * 取得所有产品。
	 *
	 * @return	array	数据
	 */
	public function findAll()
	{
		$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "products`";
		return $this->_db->selectArray($sql);
	}

	/**
	 * 根据分类取得产品
	 * @return	array	数据
	 */
	public function findByCategory($category = 0)
	{
		$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "products` WHERE `category` = $category";
		return $this->_db->selectArray($sql);
	}

	/**
	 * 按分页取得产品。
	 *
	 * @param	string	查询条件
	 * @param	string	排序方式
	 * @param	integer	开始
	 * @param	integer	数量
	 * @return	array	数据
	 */
	public function findByPage($where = '', $orderBy = '', $start = 0, $limit = 10)
	{
		$orderBy = $orderBy ? $orderBy : 'p.id';
		$sql = "SELECT p.*, c.name FROM `" . $this->_db->tablePrefix . "products` p LEFT JOIN `" . $this->_db->tablePrefix . "product_categories` c ON p.category = c.id WHERE 1 $where ORDER BY $orderBy LIMIT $start, $limit";
		$result = $this->_db->selectArray($sql);
		$sql = "SELECT COUNT(*) AS num FROM `" . $this->_db->tablePrefix . "products` p LEFT JOIN `" . $this->_db->tablePrefix . "product_categories` c ON p.category = c.id WHERE 1 $where";
		$total = $this->_db->select($sql)->num;
		return array($result, $total);
	}

	/**
	 * 对产品排序。
	 *
	 * @param	array	数据
	 * @return	integer	影响的行数
	 */
	public function sortProducts($orders)
	{
		$count = 0;
		if (is_array($orders))
		{
			foreach ($orders as $p)
			{
				$p = explode('|', $p);
				if (count($p) == 2)
				{
					foreach ($p as $f)
					{
						if (!is_numeric($f))
							return 0;
					}
					$where = "`id` = {$p[0]}";
					$result = $this->_db->update('products', array('list_order' => $p[1]), $where);
					if ($result)
						$count++;
				}
			}
		}
		return $count;
	}

	/**
	 * 更新产品价格。
	 *
	 * @param	array	数据
	 * @return	integer	影响的行数
	 */
	public function updatePrices($products)
	{
		$count = 0;
		if (is_array($products))
		{
			foreach ($products as $p)
			{
				$p = explode('|', substr($p, 0, -1));
				if (count($p) == 4)
				{
					foreach ($p as $f)
					{
						if (!is_numeric($f))
							return 0;
					}
					$set = array(
						'commodity_price' => $p[1],
						'promotion_price' => $p[2],
						'promote' => $p[3]
					);
					$where = "`id` = {$p[0]}";
					$result = $this->_db->update('products', $set, $where);
					if ($result)
						$count++;
				}
			}
		}
		return $count;
	}

	/**
	 * 按分类取得产品。
	 *
	 * @return	array	数据
	 */
	public function findAllWithCategory()
	{
		$sql = "SELECT p.* FROM `" . $this->_db->tablePrefix . "products` p LEFT JOIN `" . $this->_db->tablePrefix . "product_categories` c ON p.category = c.id WHERE `status` = 0 ORDER BY c.order, p.order";
		return $this->_db->selectArray($sql);
	}

}
