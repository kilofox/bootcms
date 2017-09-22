<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 媒体分组模型。
 *
 * @package	BootCMS
 * @category	模型
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Model_Media_Group extends Model {

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
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "media_groups` WHERE `id` = $id";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 创建媒体分组。
	 *
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
			return $this->_db->insert('media_groups', $data);
		}
		return false;
	}

	/**
	 * 更新媒体分组。
	 *
	 * @return	mixed	执行结果
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
			return $this->_db->update('media_groups', $this->_values, $where);
		}
		return false;
	}

	/**
	 * 删除媒体分组。
	 *
	 * @return	mixed	执行结果
	 */
	public function delete()
	{
		if ($this->_values->id)
		{
			$where = "`id` = {$this->_values->id}";
			return $this->_db->delete('media_groups', $where);
		}
		return false;
	}

	/**
	 * 数据验证
	 * @param	array	$values
	 * @return	Validation
	 */
	public static function getValidation($values)
	{
		return Validation::factory($values)
				->rule('group_name', 'not_empty')
				->rule('slug', 'not_empty')
				->rule('rs_width', 'digit')
				->rule('rs_height', 'digit')
				->rule('tn_width', 'digit')
				->rule('tn_height', 'digit');
	}

	/**
	 * 取得媒体分组。
	 *
	 * @return	array	数据
	 */
	public function findAll()
	{
		$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "media_groups`";
		$result = $this->_db->selectArray($sql);
		return $result;
	}

}
