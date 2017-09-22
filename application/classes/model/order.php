<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 订单模型。
 *
 * @package	BootCMS
 * @category	模型
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Model_Order extends Model {

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
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "orders` WHERE `id` = $id";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 根据订单号加载数据，并返回对象。
	 *
	 * @return	对象
	 */
	public function loadByOrderNo($orderNo)
	{
		if ($orderNo)
		{
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "orders` WHERE `order_no` = '$orderNo'";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 创建新订单。
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
			return $this->_db->insert('orders', $values);
		}
		return false;
	}

	/**
	 * 更新订单。
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
			return $this->_db->update('orders', $this->_values, $where);
		}
		return false;
	}

	/**
	 * 删除订单。
	 *
	 * @return	mixed	执行结果
	 */
	public function delete()
	{
		if ($this->_loaded)
		{
			$where = "`id` = {$this->_values->id}";
			return $this->_db->delete('orders', $where);
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
				->rule('user_id', 'not_empty')
				->rule('amount', 'not_empty')
				->rule('consignee', 'not_empty')
				->rule('phone', 'not_empty')
				->rule('addr_prov', 'not_empty')
				->rule('addr_city', 'not_empty')
				->rule('addr_detail', 'not_empty')
				->rule('phone', 'phone', array(':value', array(7, 8, 11, 12, 13, 14)));
	}

	/**
	 * 取得所有订单。
	 *
	 * @return	array	数据
	 */
	public function findAll()
	{
		$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "orders`";
		return $this->_db->selectArray($sql);
	}

	/**
	 * 按分页取得订单。
	 *
	 * @param	string	查询条件
	 * @param	string	排序方式
	 * @param	integer	开始
	 * @param	integer	数量
	 * @return	array	数据
	 */
	public function findByPage($where = '', $orderBy = '', $start = 0, $limit = 10)
	{
		$orderBy = $orderBy ? $orderBy : 'id';
		$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "orders` WHERE 1 $where ORDER BY $orderBy LIMIT $start, $limit";
		$result = $this->_db->selectArray($sql);
		$sql = "SELECT COUNT(*) AS num FROM `" . $this->_db->tablePrefix . "orders` o WHERE 1 $where";
		$total = $this->_db->select($sql)->num;
		return array($result, $total);
	}

	/**
	 * 按分页取得订单。
	 *
	 * @param	string	查询条件
	 * @param	string	排序方式
	 * @param	integer	开始
	 * @param	integer	数量
	 * @return	array	数据
	 */
	public function findWithProducts($where = '', $orderBy = '', $start = 0, $limit = 10)
	{
		$orderBy = $orderBy ? $orderBy : 'o.id';
		$sql = "SELECT o.*, op.product, op.price, op.quantity FROM `" . $this->_db->tablePrefix . "orders` o LEFT JOIN `" . $this->_db->tablePrefix . "order_products` op ON o.id = op.order_id WHERE 1 $where ORDER BY $orderBy LIMIT $start, $limit";
		$result = $this->_db->selectArray($sql);
		$sql = "SELECT COUNT(*) AS num FROM `" . $this->_db->tablePrefix . "orders` o WHERE 1 $where";
		$total = $this->_db->select($sql)->num;
		return array($result, $total);
	}

	/**
	 * 清理过期订单。
	 *
	 * @param	integer	过期小时数
	 * @return	boolean	结果
	 */
	public function calcelOverdue($hours = 24)
	{
		if (is_int($hours) && $hours)
		{
			$set = array('status' => 4);
			$timestamp = time() - $hours * 3600;
			$where = "`status` = 0 AND `created` < $timestamp";
			return $this->_db->update('orders', $set, $where);
		}
		return false;
	}

}
