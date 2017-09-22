<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * [Request_Client_External] 为所有外部请求处理提供了一个包装。这个类应该被所有处理外部请求的驱动继承。
 *
 * 支持即开即用：
 *  - Curl （默认）
 *  - PECL HTTP
 *  - Streams
 *
 * 要选择某个特定的外部驱动作为默认驱动，在应用引导中设置以下属性。也可以将客户端注入到请求对象。
 *
 * @example
 *
 * 	// 在应用引导中
 * 	Request_Client_External::$client = 'Request_Client_Stream';
 * 	// 将客户端添加到请求
 * 	$request = Request::factory('http://some.host.net/foo/bar')
 * 		->client(Request_Client_External::factory('Request_Client_HTTP));
 *
 * @package BootPHP
 * @category Base
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
abstract class Request_Client_External extends Request_Client {

	/**
	 * 使用：
	 *  - Request_Client_Curl （默认）
	 *  - Request_Client_HTTP
	 *  - Request_Client_Stream
	 *
	 * @var string 定义默认使用的外部客户端
	 */
	public static $client = 'Request_Client_Curl';

	/**
	 * 工厂方法，根据传递的客户端名，创建一个新的 Request_Client_External 对象，或在默认情况下默认为 Request_Client_External::$client。
	 *
	 * Request_Client_External::$client 可以在应用引导中设置。
	 *
	 * @param array 要传递给客户端的参数
	 * @param string 要使用的外部客户端
	 * @return Request_Client_External
	 * @throws Request_Exception
	 */
	public static function factory(array $params = array(), $client = NULL)
	{
		if ($client === NULL)
		{
			$client = Request_Client_External::$client;
		}
		$client = new $client($params);
		if (!$client instanceof Request_Client_External)
		{
			throw new Request_Exception('Selected client is not a Request_Client_External object.');
		}
		return $client;
	}

	/**
	 * @var	array	curl 选项
	 * @see	[http://www.php.net/manual/en/function.curl-setopt.php]
	 * @see	[http://www.php.net/manual/en/http.request.options.php]
	 */
	protected $_options = array();

	/**
	 * 处理请求，执行由 [Route] 确定的请求的控制器动作。
	 *
	 * 1. 在调用控制器动作之前，将调用 [Controller::before] 方法。
	 * 2. 然后调用控制器动作。
	 * 3. 在调用控制器动作之后，将调用 [Controller::after] 方法。
	 *
	 * 默认情况下，来自控制器的输出被捕获并返回，并不发送头。
	 *
	 * 	$request->execute();
	 *
	 * @param Request 请求对象
	 * @return Response
	 * @throws BootPHP_Exception
	 * @uses [BootPHP::$profiling]
	 * @uses [Profiler]
	 */
	public function execute_request(Request $request)
	{
		if (BootPHP::$profiling)
		{
			// 设置 benchmark 名称
			$benchmark = '"' . $request->uri() . '"';
			if ($request !== Request::$initial && Request::$current)
			{
				// 添加父请求 URI
				$benchmark .= ' ? "' . Request::$current->uri() . '"';
			}
			// 启动 benchmarking
			$benchmark = Profiler::start('Requests', $benchmark);
		}
		// 保存当前活动请求，并用新的请求替换当前的
		$previous = Request::$current;
		Request::$current = $request;
		// 解决 POST 字段
		if ($post = $request->post())
		{
			$request->body(http_build_query($post, NULL, '&'))
				->headers('content-type', 'application/x-www-form-urlencoded');
		}
		// 如果 BootPHP 暴露，设置 user-agent
		if (BootPHP::$expose)
		{
			$request->headers('user-agent', 'BootPHP Framework ' . BootPHP::VERSION);
		}
		try
		{
			$response = $this->_send_message($request);
		}
		catch (Exception $e)
		{
			// 恢复上一个请求
			Request::$current = $previous;
			if (isset($benchmark))
			{
				// 删除 benchmark，无效了
				Profiler::delete($benchmark);
			}
			// 重新抛出异常
			throw $e;
		}
		// 恢复上一个请求
		Request::$current = $previous;
		if (isset($benchmark))
		{
			// 停止 benchmark
			Profiler::stop($benchmark);
		}
		// 返回响应
		return $response;
	}

	/**
	 * 设置与获取请求的选项。
	 *
	 * @param mixed $key 选项名，或选项数组
	 * @param mixed $value 选项值
	 * @return mixed
	 * @return Request_Client_External
	 */
	public function options($key = NULL, $value = NULL)
	{
		if ($key === NULL)
			return $this->_options;
		if (is_array($key))
		{
			$this->_options = $key;
		}
		elseif ($value === NULL)
		{
			return Arr::get($this->_options, $key);
		}
		else
		{
			$this->_options[$key] = $value;
		}
		return $this;
	}

	/**
	 * 发送 HTTP 消息 [Request] 给远程服务器并处理响应。
	 *
	 * @param Request 要发送的请求
	 * @return Response
	 */
	abstract protected function _send_message(Request $request);
}
