<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 内部执行的请求客户端。
 * @package BootPHP
 * @category Base
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class Request_Client_Internal extends Request_Client {

	/**
	 * @var	array
	 */
	protected $_previous_environment;

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
	 * @throws BootPHP_Exception
	 * @uses [BootPHP::$profiling]
	 * @uses [Profiler]
	 */
	public function execute_request(Request $request)
	{
		// 创建类前缀
		$prefix = 'controller_';
		// 目录
		$directory = $request->directory();
		// 控制器
		$controller = $request->controller();
		if ($directory)
		{
			// 将目录名添加到类前缀中
			$prefix .= str_replace(array('\\', '/'), '_', trim($directory, '/')) . '_';
		}
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
		// 保存当前活动请求
		$previous = Request::$current;
		// 改变当前请求
		Request::$current = $request;
		// 它是初始请求吗？
		$initial_request = ($request === Request::$initial);
		try
		{
			if (!class_exists($prefix . $controller))
			{
				throw new HTTP_Exception_404('The requested URL :uri was not found on this server.', array(':uri' => $request->uri()));
			}
			// 使用反射加载控制器
			$class = new ReflectionClass($prefix . $controller);
			if ($class->isAbstract())
			{
				throw new BootPHP_Exception('Cannot create instances of abstract :controller', array(':controller' => $prefix . $controller));
			}
			// 创建一个新的控制器实例
			$controller = $class->newInstance($request, $request->response() ? $request->response() : $request->create_response());
			$class->getMethod('before')->invoke($controller);
			// 确定要使用的动作
			$action = $request->action();
			$params = $request->param();
			// 如果动作不存在，那就是 404
			if (!$class->hasMethod('action_' . $action))
			{
				throw new HTTP_Exception_404('The requested URL :uri was not found on this server.', array(':uri' => $request->uri()));
			}
			$method = $class->getMethod('action_' . $action);
			$method->invoke($controller);
			// 执行“after action”方法
			$class->getMethod('after')->invoke($controller);
		}
		catch (Exception $e)
		{
			// 恢复上一个请求
			if ($previous instanceof Request)
			{
				Request::$current = $previous;
			}
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
		return $request->response();
	}

}
