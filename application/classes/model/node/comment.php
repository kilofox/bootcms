<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 评论模型。
 *
 * @package	BootCMS
 * @category	模型
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Model_Node_Comment extends Model {

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
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "node_comments` WHERE `id` = $id";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 创建新评论。
	 *
	 * @param	array	键值对
	 * @return	mixed	插入的数据ID 或 false
	 */
	public function create($data)
	{
		if (is_object($data))
		{
			// 对象转数组（将来将 Validation 改写成操作对象后，就可以省略此步）
			$_arr = get_object_vars($data);
			foreach ($_arr as $key => $val)
			{
				$values[$key] = $val;
			}
			// 表单验证
			$validation = self::getDataValidation($values);
			if (!$validation->check())
			{
				throw new Validation_Exception('user', $validation);
			}
			return $this->_db->insert('node_comments', $data);
		}
		return false;
	}

	/**
	 * 数据验证。
	 *
	 * @param	array	$values
	 * @return	Validation
	 */
	public static function getDataValidation($values)
	{
		return Validation::factory($values)
				->rule('user_id', 'not_empty')
				->rule('node_id', 'not_empty')
				->rule('comment', 'not_empty');
	}

	/**
	 * 更新评论。
	 *
	 * @return	mixed	影响的行数或布尔值
	 */
	public function update()
	{
		if ($this->_loaded)
		{
			$where = "`id` = {$this->_values->id}";
			return $this->_db->update('node_comments', $this->_values, $where);
		}
		return false;
	}

	/**
	 * 删除评论。
	 *
	 * @return	mixed	执行结果
	 */
	public function delete()
	{
		if ($this->_loaded)
		{
			$where = "`id` = {$this->_values->id}";
			return $this->_db->delete('node_comments', $where);
		}
		return false;
	}

	/**
	 * 取得所有评论。
	 *
	 * @return	array	数据
	 */
	public function findAll()
	{
		$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "node_comments`";
		return $this->_db->selectArray($sql);
	}

	/**
	 * 按分页取得评论。
	 *
	 * @param	string	查询条件
	 * @param	string	排序方式
	 * @param	integer	开始
	 * @param	integer	数量
	 * @return	array	数据
	 */
	public function findByPage($where = '', $orderBy = '', $start = 0, $limit = 10)
	{
		$orderBy = $orderBy ? $orderBy : 'c.id DESC';
		$sql = "SELECT c.*, u.nickname FROM `" . $this->_db->tablePrefix . "node_comments` c LEFT JOIN `" . $this->_db->tablePrefix . "users` u ON c.user_id = u.id WHERE 1 $where ORDER BY $orderBy LIMIT $start, $limit";
		$result = $this->_db->selectArray($sql);
		$sql = "SELECT COUNT(*) AS num FROM `" . $this->_db->tablePrefix . "node_comments` c LEFT JOIN `" . $this->_db->tablePrefix . "users` u ON c.user_id = u.id WHERE 1 $where";
		$total = $this->_db->select($sql)->num;
		return array($result, $total);
	}

	/**
	 * 用户是否已评论某节点。
	 *
	 * @param	integer	用户ID
	 * @param	integer	节点ID
	 * @param	integer	发表评论间隔时间（秒）
	 * @return	boolean
	 */
	public function isCommented($userId = 0, $picId = 0, $timeLimit = 0)
	{
		$aimTime = is_int($timeLimit) ? time() - $timeLimit : time();
		// 检查近期是否评论过该节点
		if ($userId && $picId)
		{
			$sql = "SELECT `id` FROM `" . $this->_db->tablePrefix . "node_comments` WHERE `node_id` = $picId AND `user_id` = $userId AND `created` > $aimTime";
			$result = $this->_db->select($sql);
			return $result->id ? true : false;
		}
		return true;
	}

}
