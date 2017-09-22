<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * PDO 数据库连接。
 *
 * @package		BootPHP/数据库
 * @category	驱动
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Database_PDO extends Database {

	// PDO 使用不带引号的标识符
	protected $_identifier = '';

	protected function __construct($name, array $config)
	{
		parent::__construct($name, $config);
		if (isset($this->_config['identifier']))
		{
			// 让标识符在每个连接中都被重载
			$this->_identifier = (string) $this->_config['identifier'];
		}
	}

	public function connect()
	{
		if ($this->_connection)
			return;
		// 提取连接参数，添加需要的变量
		extract($this->_config['connection'] + array(
			'dsn' => '',
			'username' => NULL,
			'password' => NULL,
			'persistent' => false,
		));
		// 出于安全考虑，清除连接参数
		unset($this->_config['connection']);
		// 强制 PDO 对所有错误使用异常
		$attrs = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
		if ($persistent)
		{
			// 使连接持久
			$attrs[PDO::ATTR_PERSISTENT] = true;
		}
		try
		{
			// 创建一个新的 PDO 连接
			$this->_connection = new PDO($dsn, $username, $password, $attrs);
		}
		catch (PDOException $e)
		{
			throw new Database_Exception(':error', array(':error' => $e->getMessage()), $e->getCode());
		}
		if ($this->_config['charset'])
		{
			// 设置字符集
			$this->setCharset($this->_config['charset']);
		}
	}

	/**
	 * 创建或重新定义一个SQL聚合函数。
	 *
	 * [!!] 仅适用于 SQLite
	 *
	 * @link http://php.net/manual/function.pdo-sqlitecreateaggregate
	 *
	 * @param	string		$name		要创建或重新定义的SQL函数名称
	 * @param	callback	$step		被结果集的每一行调用
	 * @param	callback	$final		结果集的所有行执行完毕后被调用
	 * @param	integer		$arguments	SQL函数接受的参数数量
	 * @return	boolean
	 */
	public function create_aggregate($name, $step, $final, $arguments = -1)
	{
		$this->_connection || $this->connect();
		return $this->_connection->sqliteCreateAggregate(
				$name, $step, $final, $arguments
		);
	}

	/**
	 * 创建或重新定义一个SQL函数。
	 *
	 * [!!] 仅适用于 SQLite
	 *
	 * @link http://php.net/manual/function.pdo-sqlitecreatefunction
	 * @param	string		$name		要创建或重新定义的SQL函数名称
	 * @param	callback	$callback	实现SQL函数的回调
	 * @param	integer		$arguments	SQL函数接受的参数数量
	 * @return	boolean
	 */
	public function create_function($name, $callback, $arguments = -1)
	{
		$this->_connection || $this->connect();
		return $this->_connection->sqliteCreateFunction(
				$name, $callback, $arguments
		);
	}

	public function disconnect()
	{
		// 销毁 PDO 对象
		$this->_connection = NULL;
		return parent::disconnect();
	}

	public function setCharset($charset)
	{
		// 确保数据库已连接
		$this->_connection || $this->connect();
		// 这个 SQL-92 语法不是所有驱动都支持
		$this->_connection->exec('SET NAMES ' . $this->quote($charset));
	}

	public function query($type, $sql, $as_object = false, array $params = NULL)
	{
		// 确保数据库已连接
		$this->_connection || $this->connect();
		if (isset($this->_config['profiling']))
		{
			// Benchmark this query for the current instance
			$benchmark = Profiler::start("Database ({$this->_instance})", $sql);
		}
		try
		{
			$this->result = $this->_connection->query($sql);
		}
		catch (Exception $e)
		{
			if (isset($benchmark))
			{
				// This benchmark is worthless
				Profiler::delete($benchmark);
			}
			// Convert the exception in a database exception
			throw new Database_Exception(':error [ :query ]', array(
			':error' => $e->getMessage(),
			':query' => $sql
			), $e->getCode());
		}
		if (isset($benchmark))
		{
			Profiler::stop($benchmark);
		}
		// 设置最后一次查询
		$this->last_query = $sql;
		if ($type === 'select')
		{
			$this->result->setFetchMode(PDO::FETCH_CLASS, 'stdClass');
		}
		elseif ($type === 'insert')
		{
			// 返回插入的ID
			return $this->_connection->lastInsertId();
		}
		else
		{
			// 返回受影响的行数
			return $this->result->rowCount();
		}
	}

	/**
	 * 执行 SQL 查询，并返回一个 stdClass 类型的记录。
	 *
	 * @param	string	$sql	SQL查询
	 * @return	object	stdClass 类型的记录
	 */
	public function select($sql)
	{
		self::query('select', $sql);
		$this->result->setFetchMode(PDO::FETCH_OBJ);
		return $this->result->fetch();
	}

	/**
	 * 执行 SQL 查询，并返回一个由 stdClass 类型的记录构成的数组。
	 *
	 * @param	string	$sql	SQL查询
	 * @return	array	stdClass 类型的记录构成的数组
	 */
	public function selectArray($sql)
	{
		self::query('select', $sql);
		$results = array();
		$this->result->setFetchMode(PDO::FETCH_OBJ);
		while ($obj = $this->result->fetch())
		{
			$results[] = $obj;
		}
		return $results;
	}

	/**
	 * 执行 SQL 查询，用于插入一条记录。
	 *
	 * @param	string	表名
	 * @param	array	要插入的数据
	 * @return	integer	INSERT 查询产生的 ID 号
	 */
	public function insert($table, $data)
	{
		$fields = $values = '';
		foreach ($data as $k => $v)
		{
			$fields.= empty($fields) ? "`$k`" : ", `$k`";
			$values.= empty($values) ? "'$v'" : ", '$v'";
		}
		$sql = "INSERT INTO `" . $this->tablePrefix . "$table` ($fields) VALUES ($values)";
		return self::query('insert', $sql);
	}

	/**
	 * 执行 SQL 查询，用于更新一条记录。
	 *
	 * @param	string	表名
	 * @param	array	要更新的数据
	 * @param	string	查询条件
	 * @return	integer	前一次SQL操作所影响的记录行数
	 */
	public function update($table, $data, $where)
	{
		$set = '';
		foreach ($data as $k => $v)
		{
			if (substr($v, 0, 2) == ':+' && is_numeric($len = substr($v, 2)))
				$v = "`$k` + $len";
			else if (substr($v, 0, 2) == ':-' && is_numeric($len = substr($v, 2)))
				$v = "`$k` - $len";
			else
				$v = "'$v'";
			$set.= "`$k` = $v, ";
		}
		$set = substr($set, 0, -2);
		$sql = "UPDATE `" . $this->tablePrefix . "$table` SET $set WHERE $where";
		return self::query('update', $sql);
	}

	/**
	 * 执行 SQL 查询，用于删除一条记录。
	 *
	 * @param	string	表名
	 * @param	string	查询条件
	 * @return	integer	前一次SQL操作所影响的记录行数
	 */
	public function delete($table, $where)
	{
		$sql = "DELETE FROM `" . $this->tablePrefix . "$table` WHERE $where";
		return self::query('delete', $sql);
	}

	public function begin($mode = NULL)
	{
		// 确保数据库已连接
		$this->_connection || $this->connect();
		return $this->_connection->beginTransaction();
	}

	public function commit()
	{
		// 确保数据库已连接
		$this->_connection || $this->connect();
		return $this->_connection->commit();
	}

	public function rollback()
	{
		// 确保数据库已连接
		$this->_connection || $this->connect();
		return $this->_connection->rollBack();
	}

	public function list_tables($like = NULL)
	{
		throw new BootPHP_Exception('Database method :method is not supported by :class', array(':method' => __FUNCTION__, ':class' => __CLASS__));
	}

	public function list_columns($table, $like = NULL, $add_prefix = true)
	{
		throw new BootPHP_Exception('Database method :method is not supported by :class', array(':method' => __FUNCTION__, ':class' => __CLASS__));
	}

	public function escape($value)
	{
		// 确保数据库已连接
		$this->_connection || $this->connect();
		return $this->_connection->quote($value);
	}

}
