<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 块模型。
 *
 * @package	BootCMS
 * @category	模型
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Model_Region_Block extends Model {

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
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "region_blocks` WHERE `id` = $id";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 创建新的块。
	 *
	 * @param	array	键值对
	 * @return	mixed	插入的数据ID或布尔值
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
			return $this->_db->insert('region_blocks', $data);
		}
		return false;
	}

	/**
	 * 更新块。
	 *
	 * @param	integer	块 ID
	 * @param	array	键值对
	 * @return	mixed	影响的行数或布尔值
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
			return $this->_db->update('region_blocks', $this->_values, $where);
		}
		return false;
	}

	/**
	 * 删除块。
	 *
	 * @param	integer	块 ID
	 * @return	mixed	影响的行数或布尔值
	 */
	public function delete($id)
	{
		if ($this->_loaded)
		{
			$where = "`id` = {$this->_values->id}";
			return $this->_db->delete('region_blocks', $where);
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
				->rule('region_id', 'not_empty')
				->rule('block_title', 'not_empty')
				->rule('status', 'not_empty');
	}

	/**
	 * 根据区域 ID 取得所有块。
	 *
	 * @param	integer	区域 ID
	 * @return	array
	 */
	public function findByRegion($regionId)
	{
		$result = array();
		if ($regionId)
		{
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "region_blocks` WHERE `region_id` = $regionId ORDER BY `list_order` ASC";
			$result = $this->_db->selectArray($sql);
		}
		return $result;
	}

	/**
	 * 取得所有块。
	 *
	 * @return	array
	 */
	public function findAll()
	{
		$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "region_blocks` ORDER BY `list_order` ASC";
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
	public function findWithRegionByPage($where = '', $orderBy = '', $start = 0, $limit = 10)
	{
		$orderBy = $orderBy ? $orderBy : 'b.id';
		$sql = "SELECT b.*, r.type, r.region_title FROM `" . $this->_db->tablePrefix . "region_blocks` b LEFT JOIN `" . $this->_db->tablePrefix . "regions` r ON b.region_id = r.id WHERE 1 $where ORDER BY b.region_id, $orderBy LIMIT $start, $limit";
		$result = $this->_db->selectArray($sql);
		$sql = "SELECT COUNT(*) AS num FROM `" . $this->_db->tablePrefix . "region_blocks` b LEFT JOIN `" . $this->_db->tablePrefix . "regions` r ON b.region_id = r.id  WHERE 1 $where";
		$total = $this->_db->select($sql)->num;
		return array($result, $total);
	}

	/**
	 * 指定标题和内容的菜单是否存在。
	 *
	 * @param	string	块的标题
	 * @param	string	块的内容
	 * @return	mixed	影响的行数或布尔值
	 */
	public function isMenuExist($title, $content)
	{
		if ($title && $content)
		{
			$sql = "SELECT `id` FROM `" . $this->_db->tablePrefix . "region_blocks` WHERE `block_title` = '$title' AND `block_content` = '$content'";
			return $this->_db->select($sql);
		}
		return false;
	}

}
