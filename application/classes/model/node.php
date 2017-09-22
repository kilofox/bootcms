<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 节点模型。
 *
 * @package	BootCMS
 * @category	模型
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Model_Node extends Model {

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
	 * 根据主键加载数据，并返回对象
	 * @return	对象
	 */
	public function load($id = 0)
	{
		if (is_numeric($id) && $id > 0)
		{
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "nodes` WHERE `id` = $id";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 创建新节点
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
			return $this->_db->insert('nodes', $values);
		}
		return false;
	}

	/**
	 * 更新节点
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
			return $this->_db->update('nodes', $this->_values, $where);
		}
		return false;
	}

	/**
	 * 删除节点
	 * @return	mixed	执行结果
	 */
	public function delete()
	{
		if ($this->_loaded)
		{
			$where = "`id` = {$this->_values->id}";
			return $this->_db->delete('nodes', $where);
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
				->rule('slug', 'not_empty')
				->rule('slug', 'max_length', array(':value', 32))
				->rule('node_title', 'not_empty')
				->rule('node_title', 'max_length', array(':value', 60))
				->rule('node_content', 'not_empty')
				->rule('keywords', 'max_length', array(':value', 120))
				->rule('descript', 'max_length', array(':value', 250));
	}

	public function loadBySlug($slug)
	{
		$result = NULL;
		if ($slug)
		{
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "nodes` WHERE `slug` = '$slug' AND `status` = 1";
			$result = $this->_db->select($sql);
		}
		return $result;
	}

	/**
	 * 取得所有主页
	 */
	public function findByType($type)
	{
		$result = NULL;
		if ($type)
		{
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "nodes` WHERE `type` = $type AND `status` = 1";
			$result = $this->_db->select($sql);
		}
		return $result;
	}

	/**
	 * 取得所有单页
	 */
	public function findAll($order_by = '')
	{
		$result = array();
		$order_by = $order_by ? $order_by : 'created DESC';
		$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "nodes` WHERE `type` = 1 ORDER BY $order_by";
		$result = $this->_db->selectArray($sql);
		return $result;
	}

	/**
	 * 取得包括 Homepage 在内的所有单页
	 * @param	string	查询条件
	 * @param	string	排序方式
	 * @param	integer	开始
	 * @param	integer	数量
	 * @return	array	数据
	 */
	public function getPagesByPage($where = '', $orderBy = '', $start = 0, $limit = 10)
	{
		$orderBy = $orderBy ? $orderBy : 'n.id';
		$sql = "SELECT n.*, u.username FROM `" . $this->_db->tablePrefix . "nodes` n LEFT JOIN `" . $this->_db->tablePrefix . "users` u ON n.author_id = u.id WHERE 1 $where ORDER BY $orderBy LIMIT $start, $limit";
		$result = $this->_db->selectArray($sql);
		$sql = "SELECT COUNT(*) AS num FROM `" . $this->_db->tablePrefix . "nodes` n LEFT JOIN `" . $this->_db->tablePrefix . "users` u ON n.author_id = u.id WHERE 1 $where";
		$total = $this->_db->select($sql)->num;
		return array($result, $total);
	}

	/**
	 * 创建别名
	 * @return	string	别名
	 */
	public function createSlug($title)
	{
		// 别名最大长度为 32
		$title = mb_substr(trim($title), 0, 32);
		$slug = $temp = Functions::toLink($title);
		$i = 0;
		do
		{
			$node = $this->loadBySlug($slug);
			if ($node->id)
			{
				$i++;
				$slug = $temp . $i;
			}
		}
		while ($node->id);
		return $slug;
	}

	/**
	 * 搜索单页
	 * @param	string	搜索关键字
	 * @return	array	结果数组
	 */
	public function search($keyword, $start = 0, $limit = 10)
	{
		$result = array();
		$totla = 0;
		if ($keyword)
		{
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "nodes` WHERE ( `type` = 1 OR `type` = 2 ) AND `status` = 1 AND (`node_title` LIKE '%$keyword%' OR `node_content` LIKE '%$keyword%') LIMIT $start, $limit";
			$result = $this->_db->selectArray($sql);
			$sql = "SELECT COUNT(*) AS num FROM `" . $this->_db->tablePrefix . "nodes` WHERE ( `type` = 1 OR `type` = 2 ) AND `status` = 1 AND (`node_title` LIKE '%$keyword%' OR `node_content` LIKE '%$keyword%')";
			$total = $this->_db->select($sql)->num;
		}
		return array($result, $total);
	}

	/**
	 * 设置首页
	 * @param	string	搜索关键字
	 * @return	array	结果数组
	 */
	public function setHomepage($nodeId = 0)
	{
		if (intval($nodeId) > 0)
		{
			$set = array('type' => 1);
			$where = "`type` = 2";
			$this->_db->update('nodes', $set, $where);
			$set = array('type' => 2);
			$where = "`id` = $nodeId AND `type` = 1";
			return $this->_db->update('nodes', $set, $where);
		}
		return false;
	}

}
