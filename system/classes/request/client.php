<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 请求客户端。处理 [Request] 和 [HTTP_Caching] （如果可用）。
 * 通常会返回 [Response] 对象作为请求的结果，除非发生意外错误。
 *
 * @package BootPHP
 * @category Base
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
abstract class Request_Client {

	/**
	 * @var Cache 请求缓存的缓存库
	 */
	protected $_cache;

	/**
	 * 创建一个新的 `Request_Client` 对象，允许依赖注入。
	 *
	 * @param array 参数
	 */
	public function __construct(array $params = array())
	{
		foreach ($params as $key => $value)
		{
			if (method_exists($this, $key))
			{
				$this->$key($value);
			}
		}
	}

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
	 * @param Request $request
	 * @return Response
	 */
	public function execute(Request $request)
	{
		if ($this->_cache instanceof HTTP_Cache)
			return $this->_cache->execute($this, $request);
		return $this->execute_request($request);
	}

	/**
	 * 处理传递给它的请求，并返回来自 URI 源标识的响应。
	 *
	 * 该方法必须被所有客户端实现。
	 *
	 * @param Request 由客户端执行的请求
	 * @return Response

	 */
	abstract public function execute_request(Request $request);

	/**
	 * 内部缓存引擎的 Getter 与 setter，用来缓存响应（如果可用且有效）。
	 *
	 * @param [HTTP_Cache] 缓存引擎
	 * @return [HTTP_Cache]
	 * @return [Request_Client]
	 */
	public function cache(HTTP_Cache $cache = NULL)
	{
		if ($cache === NULL)
			return $this->_cache;
		$this->_cache = $cache;
		return $this;
	}

}
