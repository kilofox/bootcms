<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 日志模型。
 *
 * 日志类型 type：
 * 	1 = 创建；2 = 删除；3 = 修改；4 = 查询
 *
 * @package	BootCMS
 * @category	模型
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Model_Log extends Model {

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
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "logs` WHERE `id` = $id";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 创建新日志。
	 *
	 * @param	array	键值对
	 * @return	mixed	插入的数据ID 或 false
	 */
	public function create($data)
	{
		if (is_object($data))
		{
			return $this->_db->insert('logs', $data);
		}
		return false;
	}

	/**
	 * 更新日志。
	 *
	 * @return	mixed	影响的行数或布尔值
	 */
	public function update()
	{
		if ($this->_loaded)
		{
			$where = "`id` = {$this->_values->id}";
			return $this->_db->update('logs', $this->_values, $where);
		}
		return false;
	}

	/**
	 * 清理日志。
	 *
	 * @param	时间戳
	 * @param	保留的天数
	 * @param	保留的数量
	 * @return	mixed	执行结果
	 */
	public function clearLogs($days = 90, $num = 100)
	{
		if (is_numeric($days) && is_numeric($num))
		{
			$timestamp = time();
			$seconds = $timestamp - $days * 86400;
			// 删除状态为“已删除”且已过期的记录
			$where = "`status` = 1 AND `trashed` < $seconds";
			$this->_db->delete('logs', $where);
			// 标记新的“已删除”记录
			$where = "`status` = 0 AND `created` < $seconds ORDER BY `id` DESC";
			$sql = "SELECT `id` FROM `" . $this->_db->tablePrefix . "logs` WHERE $where";
			$rs = $this->_db->selectArray($sql);
			if (isset($rs[$num - 1]))
			{
				$log = $rs[$num - 1];
				$set = array(
					'status' => 1,
					'trashed' => $timestamp
				);
				$where = "`status` = 0 AND `created` < $seconds AND `id` < {$log->id}";
				return $this->_db->update('logs', $set, $where);
			}
			return false;
		}
		return false;
	}

	/**
	 * 按分页取得日志。
	 *
	 * @param	string	查询条件
	 * @param	string	排序方式
	 * @param	integer	开始
	 * @param	integer	数量
	 * @return	array	数据
	 */
	public function findByPage($where = '', $orderBy = '', $start = 0, $limit = 10)
	{
		$orderBy = $orderBy ? $orderBy : 'l.`id`';
		$sql = "SELECT l.*, u.username FROM `" . $this->_db->tablePrefix . "logs` l LEFT JOIN `" . $this->_db->tablePrefix . "users` u ON l.user_id=u.id WHERE l.`status` = 0 $where ORDER BY $orderBy LIMIT $start, $limit";
		$result = $this->_db->selectArray($sql);
		$sql = "SELECT COUNT(*) AS num FROM `" . $this->_db->tablePrefix . "logs` l LEFT JOIN `" . $this->_db->tablePrefix . "users` u ON l.user_id=u.id WHERE l.`status` = 0 $where";
		$total = $this->_db->select($sql)->num;
		return array($result, $total);
	}

}
