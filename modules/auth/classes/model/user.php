<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 用户模型
 *
 * @package		BootPHP/授权
 * @category	模型
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Model_User extends Model {

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
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "users` WHERE `id` = '$id'";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	public function loadByUsername($username)
	{
		if ($username)
		{
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "users` WHERE `username` = '$username'";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	public function loadByNickname($nickname)
	{
		if ($nickname)
		{
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "users` WHERE `nickname` = '$nickname'";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	public function loadByEmail($email)
	{
		if ($email)
		{
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "users` WHERE `email` = '$email'";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 创建新用户
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
			// 验证用户名
			if (isset($values['username']))
			{
				$validation = self::getUsernameValidation($values);
				if (!$validation->check())
				{
					throw new Validation_Exception('user', $validation);
				}
			}
			// 验证密码
			if (isset($values['password']))
			{
				$validation = self::getPasswordValidation($values);
				unset($values['password_confirm']);
				if ($validation->check())
				{
					$auth = Auth::instance();
					$values['password'] = $auth->hash($values['password']);
				}
				else
				{
					throw new Validation_Exception('user', $validation);
				}
			}
			// 验证昵称
			if (isset($values['nickname']))
			{
				$values['nickname'] = iconv('UTF-8', 'GBK', $values['nickname']);
				$validation = self::getNicknameValidation($values);
				if (!$validation->check())
				{
					throw new Validation_Exception('user', $validation);
				}
				$values['nickname'] = iconv('GBK', 'UTF-8', $values['nickname']);
			}
			// 验证 E-mail
			if (isset($values['email']))
			{
				$validation = self::getEmailValidation($values);
				if (!$validation->check())
				{
					throw new Validation_Exception('user', $validation);
				}
			}
			return $this->_db->insert('users', $values);
		}
		return false;
	}

	/**
	 * 删除用户
	 * @return	mixed	执行结果
	 */
	public function delete()
	{
		if ($this->_loaded)
		{
			$where = "`id` = '{$this->_values->id}'";
			return $this->_db->delete('users', $where);
		}
		return false;
	}

	/**
	 * 取得所有用户
	 * @param	string	排序方式
	 * @return	array	数据
	 */
	public function findAll()
	{
		$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "users`";
		$result = $this->_db->selectArray($sql);
		return $result;
	}

	/**
	 * 更新登录信息
	 * @param	用户 ID
	 * @return	mixed
	 */
	public function updateLogin($id)
	{
		if ($id > 0)
		{
			// 设置最后登录时间
			$lastLoginTime = time();
			$sql = "UPDATE `" . $this->_db->tablePrefix . "users` SET `logins` = `logins` + 1, `last_login` = '$lastLoginTime' WHERE`id` = '$id'";
			return $this->_db->query('update', $sql);
		}
		return false;
	}

	/**
	 * 更新用户信息
	 * @param	array	column => value 数组
	 * @param	array	来自 $values 的键的数组
	 * @return	mixed	执行结果
	 */
	public function update()
	{
		if ($this->_loaded)
		{
			// 对象转数组（将来将 Validation 改写成操作对象后，就可以省略此步）
			$_arr = get_object_vars($this->_values);
			foreach ($_arr as $key => $val)
			{
				$values[$key] = $val;
			}
			// 验证密码
			if (isset($this->_values->password))
			{
				$validation = self::getPasswordValidation($values);
				unset($this->_values->password_confirm);
				if ($validation->check())
				{
					$auth = Auth::instance();
					$this->_values->password = $auth->hash($this->_values->password);
				}
				else
				{
					throw new Validation_Exception('user', $validation);
				}
			}
			// 验证昵称
			if (isset($this->_values->nickname))
			{
				$values['nickname'] = iconv('UTF-8', 'GBK', $values['nickname']);
				$validation = self::getNicknameValidation($values);
				if (!$validation->check())
				{
					throw new Validation_Exception('user', $validation);
				}
			}
			$where = "`id` = '{$this->_values->id}'";
			return $this->_db->update('users', $this->_values, $where);
		}
		return false;
	}

	/**
	 * 用户名验证
	 * @param	array	值
	 * @return	Validation
	 */
	public static function getUsernameValidation($values)
	{
		return Validation::factory($values)
				->rule('username', 'not_empty')
				->rule('username', 'min_length', array(':value', 2))
				->rule('username', 'max_length', array(':value', 32));
	}

	/**
	 * 密码验证
	 * @param	array	值
	 * @return	Validation
	 */
	public static function getPasswordValidation($values)
	{
		return Validation::factory($values)
				->rule('password', 'not_empty')
				->rule('password', 'min_length', array(':value', 6))
				->rule('password', 'max_length', array(':value', 16))
				->rule('password_confirm', 'matches', array(':validation', ':field', 'password'));
	}

	/**
	 * 昵称验证
	 * 包含 GB2312 汉字：'/^[\x{b0}-\x{f7}][\x{a1}-\x{fe}]|[a-z0-9]+$/i'
	 * 包含 CJK 统一编码：'/^[\x{4e00}-\x{9fa5}a-z0-9]+$/ui'
	 * @param	array	值
	 * @return	Validation
	 */
	public static function getNicknameValidation($values)
	{
		return Validation::factory($values)
				->rule('nickname', 'not_empty')
				->rule('nickname', 'min_length', array(':value', 2))
				->rule('nickname', 'max_length', array(':value', 16))
				->rule('nickname', 'regex', array(':value', '/^([\x{b0}-\x{f7}][\x{a1}-\x{fe}]|[a-z0-9])+$/i'));
	}

	/**
	 * E-mail 验证
	 * @param	array	值
	 * @return	Validation
	 */
	public static function getEmailValidation($values)
	{
		return Validation::factory($values)
				->rule('email', 'not_empty')
				->rule('email', 'email')
				->rule('secondary_email', 'email');
	}

	/**
	 * 取得所有用户
	 * @param	string	查询条件
	 * @param	string	排序方式
	 * @param	integer	开始
	 * @param	integer	数量
	 * @return	array	数据
	 */
	public function getUsersByPage($where = '', $orderBy = '', $start = 0, $limit = 10)
	{
		$orderBy = $orderBy ? $orderBy : '`id`';
		$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "users` WHERE 1 $where ORDER BY $orderBy LIMIT $start, $limit";
		$result = $this->_db->selectArray($sql);
		$sql = "SELECT COUNT(*) AS num FROM `" . $this->_db->tablePrefix . "users` WHERE 1 $where";
		$total = $this->_db->select($sql)->num;
		return array($result, $total);
	}

	/**
	 * 根据角色取得所有用户
	 * @param	integer	角色ID
	 * @return	array	数据
	 */
	public function getUsersByRole($roleId = 0)
	{
		$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "users` WHERE `role_id` = '$roleId'";
		$result = $this->_db->selectArray($sql);
		return $result;
	}

	/**
	 * 根据查询条件取得所有用户
	 * @return	array	查询结果数组
	 */
	public function getUsersByCompany()
	{
		$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "users` WHERE company = '" . Setup::siteInfo('company') . "'";
		$result = $this->_db->selectArray($sql);
		return $result;
	}

}
