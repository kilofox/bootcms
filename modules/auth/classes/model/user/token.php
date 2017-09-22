<?php

defined('SYSPATH') || exit('Access Denied.');

class Model_User_Token extends Model {

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
	 * 根据主键加载数据，并返回对象
	 * @return	对象
	 */
	public function load($id = 0)
	{
		if ($id)
		{
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "user_tokens` WHERE `id` = '$id'";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
			// 做垃圾回收
			if (mt_rand(1, 100) === 1)
			{
				$this->deleteExpired();
			}
			// 令牌已过期
			if ($this->_values->expires < time())
			{
				$this->delete();
			}
		}
		return $this->_values;
	}

	/**
	 * 创建新令牌
	 * @param	object	键值对
	 * @return	mixed	插入的数据ID 或 false
	 */
	public function create($data)
	{
		$data->token = $this->createToken();
		if ($id = $this->_db->insert('user_tokens', $data))
		{
			$this->load($id);
			return $this->_values;
		}
	}

	/**
	 * 更新令牌
	 * @return	mixed	影响的行数或布尔值
	 */
	public function update()
	{
		if ($this->_loaded)
		{
			$where = "`id` = '{$this->_values->id}'";
			return $this->_db->update('user_tokens', $this->_values, $where);
		}
		return false;
	}

	/**
	 * 删除令牌
	 * @return	mixed	执行结果
	 */
	public function delete()
	{
		if ($this->_loaded)
		{
			$where = "`id` = '{$this->_values->id}'";
			return $this->_db->delete('user_tokens', $where);
		}
		return false;
	}

	private function createToken()
	{
		do
		{
			$token = sha1(uniqid(Text::random('alnum', 32), true));
		}
		while ($this->loadByToken($token));
		return $token;
	}

	/**
	 * 删除过期令牌
	 * @return	mixed	执行结果
	 */
	public function deleteExpired()
	{
		$time = time();
		$where = "`expires` < '$time'";
		return $this->_db->delete('user_tokens', $where);
	}

	/**
	 * 根据用户ID删除令牌
	 * @return	mixed	执行结果
	 */
	public function deleteByUser($userId = 0)
	{
		if ($userId)
		{
			$where = "`user_id` = '$userId'";
			return $this->_db->delete('user_tokens', $where);
		}
		return false;
	}

	/**
	 * 根据 token 加载数据，并返回对象属性
	 * @return	对象
	 */
	public function loadByToken($token)
	{
		if ($token)
		{
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "user_tokens` WHERE `token` = '$token'";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

}
