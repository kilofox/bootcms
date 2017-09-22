<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 数组与变量验证。
 *
 * @package BootPHP
 * @category 安全
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class Validation implements ArrayAccess {

	// 绑定值
	protected $_bound = array();
	// 域规则
	protected $_rules = array();
	// 域标签
	protected $_labels = array();
	// 执行的规则
	protected $_empty_rules = array('not_empty', 'matches');
	// 错误列表，field => rule
	protected $_errors = array();
	// 要验证的数组
	protected $_data = array();

	/**
	 * 创建一个新的 Validation 实例
	 *
	 * @param array 要验证的数组
	 * @return Validation
	 */
	public static function factory(array $array)
	{
		return new Validation($array);
	}

	/**
	 * 设置要验证的数组
	 *
	 * @param array 要验证的数组
	 * @return void
	 */
	public function __construct(array $array)
	{
		$this->_data = $array;
	}

	/**
	 * 抛出一个异常，因为 Validation 是只读的。
	 *
	 * @throws	object	BootPHP_Exception
	 * @param string 要设置的键
	 * @param mixed 要设置的值
	 * @return void
	 */
	public function offsetSet($offset, $value)
	{
		throw new BootPHP_Exception('Validation objects are read-only.');
	}

	/**
	 * 检查数组数据中是否设置了键
	 *
	 * @param string 要检查的键
	 * @return bool	是否设置了键
	 */
	public function offsetExists($offset)
	{
		return isset($this->_data[$offset]);
	}

	/**
	 * 抛出一个异常，因为 Validation 是只读的。
	 *
	 * @throws	object	BootPHP_Exception
	 * @param string 要取消设置的键
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		throw new BootPHP_Exception('Validation objects are read-only.');
	}

	/**
	 * 从数组数据中得到值
	 *
	 * @param string 要返回的键
	 * @return mixed	来自数组的值
	 */
	public function offsetGet($offset)
	{
		return $this->_data[$offset];
	}

	/**
	 * 将当前规则复制给新的数组
	 *
	 * 	$copy = $array->copy($new_data);
	 *
	 * @param array 设置的新数据
	 * @return Validation
	 */
	public function copy(array $array)
	{
		// 创建当前验证组的一个副本
		$copy = clone $this;
		// 替换为新的数据
		$copy->_data = $array;
		return $copy;
	}

	/**
	 * 返回要验证的数据数组
	 *
	 * @return array
	 */
	public function data()
	{
		return $this->_data;
	}

	/**
	 * 设置或重写域的标签名
	 *
	 * @param string 域名
	 * @param string 标签
	 * @return $this
	 */
	public function label($field, $label)
	{
		// 为该域设置标签
		$this->_labels[$field] = $label;
		return $this;
	}

	/**
	 * 使用数组设置标签
	 *
	 * @param array field => label names 列表
	 * @return $this
	 */
	public function labels(array $labels)
	{
		$this->_labels = $labels + $this->_labels;
		return $this;
	}

	/**
	 * 对一个域重写或追加规则。每条规则都将执行一次。
	 * 所有规则必须是类方法名的字符串，参数必须匹配回调函数的参数。
	 *
	 * 您可以在回调函数中使用的别名：
	 * 	:validation	验证对象
	 * 	:field		域名
	 * 	:value		域值
	 *
	 *     // "username" 不能为空，而且最小长度为 4
	 *     $validation->rule('username', 'not_empty')
	 * 				->rule('username', 'min_length', array(':value', 4));
	 *
	 *     // "password" 必须与 "password_repeat" 相匹配
	 *     $validation->rule('password', 'matches', array(':validation', 'password', 'password_repeat'));
	 *
	 * @param string 	域名
	 * @param callback	有效的 PHP 回调
	 * @param array 	规则的额外参数
	 * @return $this
	 */
	public function rule($field, $rule, array $params = NULL)
	{
		if ($params === NULL)
		{
			// 默认为 array(':value')
			$params = array(':value');
		}
		if ($field !== true && !isset($this->_labels[$field]))
		{
			// 对域名设置域标签
			$this->_labels[$field] = preg_replace('/[^\pL]+/u', ' ', $field);
		}
		// 存储规则与规则参数
		$this->_rules[$field][] = array($rule, $params);
		return $this;
	}

	/**
	 * 使用数据增加规则
	 *
	 * @param string 域名
	 * @param array 回调列表
	 * @return $this
	 */
	public function rules($field, array $rules)
	{
		foreach ($rules as $rule)
		{
			$this->rule($field, $rule[0], Arr::get($rule, 1));
		}
		return $this;
	}

	/**
	 * 给参数定义绑定一个值
	 *
	 *     // 在规则的参数定义中允许使用 :model
	 *     $validation->bind(':model', $model)
	 * 				->rule('status', 'valid_status', array(':model'));
	 *
	 * @param string 变量名或变量的数组
	 * @param mixed 值
	 * @return $this
	 */
	public function bind($key, $value = NULL)
	{
		if (is_array($key))
		{
			foreach ($key as $name => $value)
			{
				$this->_bound[$name] = $value;
			}
		}
		else
		{
			$this->_bound[$key] = $value;
		}
		return $this;
	}

	/**
	 * 执行所有的验证规则。通常在一个 if/else 块中调用。
	 *
	 * 	if ( $validation->check() )
	 * 	{
	 * 		// 数据有效，干一些事情
	 * 	}
	 *
	 * @return boolean
	 */
	public function check()
	{
		if (BootPHP::$profiling === true)
		{
			// 启动一个新的 benchmark
			$benchmark = Profiler::start('Validation', __FUNCTION__);
		}
		// 设置新数据
		$data = $this->_errors = array();
		// 保存原始数据，因为这个类不应该修改它的 post 验证
		$original = $this->_data;
		// 获得期望字段的列表
		$expected = Arr::merge(array_keys($original), array_keys($this->_labels));
		// 局部导入规则
		$rules = $this->_rules;
		foreach ($expected as $field)
		{
			// 使用提交的值，如果没有数据，使用 NULL
			$data[$field] = Arr::get($this, $field);
			if (isset($rules[true]))
			{
				if (!isset($rules[$field]))
				{
					// 为该域初始化规则
					$rules[$field] = array();
				}
				// 追加规则
				$rules[$field] = array_merge($rules[$field], $rules[true]);
			}
		}
		// 用新的数组重载当前数组
		$this->_data = $data;
		// 移除应用在每个字段上的规则
		unset($rules[true]);
		// 绑定验证对象到 :validation
		$this->bind(':validation', $this);
		// 绑定数据到 :data
		$this->bind(':data', $this->_data);
		// 执行规则
		foreach ($rules as $field => $set)
		{
			// 获得字段的值
			$value = $this[$field];
			// 分别绑定字段名称和值到 :field 和 :value
			$this->bind(array(
				':field' => $field,
				':value' => $value,
			));
			foreach ($set as $array)
			{
				// 规则定义于 array($rule, $params)
				list($rule, $params) = $array;
				foreach ($params as $key => $param)
				{
					if (is_string($param) && array_key_exists($param, $this->_bound))
					{
						// 用绑定的值替换
						$params[$key] = $this->_bound[$param];
					}
				}
				// 将规则作为错误名的默认值（除了数组和 lambda 规则）
				$error_name = $rule;
				if (is_array($rule))
				{
					// 允许 rule('field', array(':model', 'some_rule'));
					if (is_string($rule[0]) && array_key_exists($rule[0], $this->_bound))
					{
						// 用绑定的值替换
						$rule[0] = $this->_bound[$rule[0]];
					}
					// 这是一个数组回调，方法名就是错误名
					$error_name = $rule[1];
					$passed = call_user_func_array($rule, $params);
				}
				elseif (!is_string($rule))
				{
					// 这是一个 lambda 函数，没有错误名（错误名必须手动添加）
					$error_name = false;
					$passed = call_user_func_array($rule, $params);
				}
				elseif (method_exists('Valid', $rule))
				{
					// 使用这个对象的方法
					$method = new ReflectionMethod('Valid', $rule);
					// 用 Reflection 调用 static::$rule($this[$field], $param, ...)
					$passed = $method->invokeArgs(NULL, $params);
				}
				elseif (strpos($rule, '::') === false)
				{
					// 使用函数调用
					$function = new ReflectionFunction($rule);
					// 用 Reflection 调用 $function($this[$field], $param, ...)
					$passed = $function->invokeArgs($params);
				}
				else
				{
					// 分离规则中的类和方法
					list($class, $method) = explode('::', $rule, 2);
					// 使用静态方法调用
					$method = new ReflectionMethod($class, $method);
					// 用 Reflection 调用 $Class::$method($this[$field], $param, ...)
					$passed = $method->invokeArgs(NULL, $params);
				}
				// 当字段为空时，忽略返回值
				if (!in_array($rule, $this->_empty_rules) && !Valid::not_empty($value))
					continue;
				if ($passed === false && $error_name !== false)
				{
					// 将规则添加到错误信息中
					$this->error($field, $error_name, $params);
					// 该域有错误，停止执行规则
					break;
				}
			}
		}
		// 恢复数据的原始形式
		$this->_data = $original;
		if (isset($benchmark))
		{
			// 停止 benchmarking
			Profiler::stop($benchmark);
		}
		return empty($this->_errors);
	}

	/**
	 * 对某个域添加错误
	 *
	 * @param string 域名
	 * @param string 错误信息
	 * @return $this
	 */
	public function error($field, $error, array $params = NULL)
	{
		$this->_errors[$field] = array($error, $params);
		return $this;
	}

	/**
	 * 返回错误信息。如果没有指定文件，错误信息就是失败的规则名。
	 * 当指定文件时，这个信息将从 "field/rule" 加载，或者没有“规则指定”信息，就使用 "field/default"。
	 * 如果都没有设置，返回的信息就是 "file/field/rule"。
	 * 默认情况下，所有信息都用默认语言翻译。
	 * 可以使用一个字符串作为第二个参数，来指定写入消息的语言。
	 *
	 * 	// 从 messages/forms/login.php 取得错误
	 * 	$errors = $Validation->errors('forms/login');
	 *
	 * @uses BootPHP::message
	 * @param string 用来加载错误信息的文件
	 * @param mixed 翻译信息
	 * @return array
	 */
	public function errors($file = NULL, $translate = true)
	{
		if ($file === NULL)
		{
			// 返回错误列表
			return $this->_errors;
		}
		// 创建新的信息列表
		$messages = array();
		foreach ($this->_errors as $field => $set)
		{
			list($error, $params) = $set;
			// 取得域的标签
			$label = $this->_labels[$field];
			if ($translate)
			{
				if (is_string($translate))
				{
					// 使用指定语言翻译标签
					$label = __($label, NULL, $translate);
				}
				else
				{
					// 翻译标签
					$label = __($label);
				}
			}
			// 开始翻译值列表
			$values = array(
				':field' => $label,
				':value' => Arr::get($this, $field),
			);
			if (is_array($values[':value']))
			{
				// 所有的值必须是字符串
				$values[':value'] = implode(', ', Arr::flatten($values[':value']));
			}
			if ($params)
			{
				foreach ($params as $key => $value)
				{
					if (is_array($value))
					{
						// 所有的值必须是字符串
						$value = implode(', ', Arr::flatten($value));
					}
					elseif (is_object($value))
					{
						// 对象不能在信息文件中使用
						continue;
					}
					// 检查这个参数是否存在标签
					if (isset($this->_labels[$value]))
					{
						// 使用标签作为值
						$value = $this->_labels[$value];
						if ($translate)
						{
							if (is_string($translate))
							{
								// 用指定的语言翻译值
								$value = __($value, NULL, $translate);
							}
							else
							{
								// 翻译值
								$value = __($value);
							}
						}
					}
					// 每个参数做成带编号的值，从 1 开始
					$values[':param' . ($key + 1)] = $value;
				}
			}
			if ($message = BootPHP::message($file, "{$field}.{$error}"))
			{
				// 发现这个字段及错误的消息
			}
			elseif ($message = BootPHP::message($file, "{$field}.default"))
			{
				// 发现这个字段的默认消息
			}
			elseif ($message = BootPHP::message($file, $error))
			{
				// 发现这个错误的默认消息
			}
			elseif ($message = BootPHP::message('validation', $error))
			{
				// 发现这个错误的默认消息
			}
			else
			{
				// 没有消息，显示预期的路径
				$message = "{$file}.{$field}.{$error}";
			}
			if ($translate)
			{
				if (is_string($translate))
				{
					// 使用指定的语言翻译消息
					$message = __($message, $values, $translate);
				}
				else
				{
					// 使用默认语言翻译消息
					$message = __($message, $values);
				}
			}
			else
			{
				// 不翻译，只是替换值
				$message = strtr($message, $values);
			}
			// 为该域设置信息
			$messages[$field] = $message;
		}
		return $messages;
	}

}
