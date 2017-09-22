<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 购物车模型。
 *
 * @package	BootCMS
 * @category	模型
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Model_Cart extends Model {

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
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "carts` WHERE `id` = $id";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 根据用户ID和产品ID加载数据，并返回对象。
	 *
	 * @return	对象
	 */
	public function loadByUserAndProduct($userId = 0, $productId = 0)
	{
		if ($userId > 0 && $productId > 0)
		{
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "carts` WHERE `user_id` = $userId AND `product_id` = $productId";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 创建新购物车。
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
			return $this->_db->insert('carts', $values);
		}
		return false;
	}

	/**
	 * 更新购物车。
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
			return $this->_db->update('carts', $this->_values, $where);
		}
		return false;
	}

	/**
	 * 删除购物车。
	 *
	 * @return	mixed	执行结果
	 */
	public function delete()
	{
		if ($this->_loaded)
		{
			$where = "`id` = {$this->_values->id}";
			return $this->_db->delete('carts', $where);
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
				->rule('product_id', 'not_empty')
				->rule('user_id', 'digit')
				->rule('product_id', 'digit');
	}

	/**
	 * 根据用户查找购物车中的商品。
	 *
	 * @return	array	数据
	 */
	public function findByUser($userId = 0)
	{
		$sql = "SELECT c.*, p.product_name FROM `" . $this->_db->tablePrefix . "carts` c LEFT JOIN `" . $this->_db->tablePrefix . "products` p ON c.product_id = p.id WHERE c.user_id = $userId ORDER BY `list_order`";
		return $this->_db->selectArray($sql);
	}

	/**
	 * 取得多个购物车。
	 *
	 * @return	array	数据
	 */
	public function findByIds($ids)
	{
		if (is_array($ids) && count($ids))
		{
			$in = '';
			foreach ($ids as $id)
			{
				$in.= "$id,";
			}
			$in = substr($in, 0, -1);
			$sql = "SELECT c.*, p.product_name FROM `" . $this->_db->tablePrefix . "carts` c LEFT JOIN `" . $this->_db->tablePrefix . "products` p ON c.product_id = p.id WHERE c.id IN ($in)";
			return $this->_db->selectArray($sql);
		}
		return false;
	}

	/**
	 * 清空会员购物车中的多个商品。
	 *
	 * @param	integer	用户ID
	 * @param	array	商品ID数组
	 * @return	mixed	结果
	 */
	public function deleteByIds($userId = 0, $ids)
	{
		if (is_array($ids) && count($ids))
		{
			$in = '';
			foreach ($ids as $id)
			{
				$in.= "$id,";
			}
			$in = substr($in, 0, -1);
			$where = "`id` IN ($in)";
			return $this->_db->delete('carts', $where);
		}
		return false;
	}

}
