<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 抽象控制器类。控制器应该只能使用 [Request] 来创建。
 * 控制器方法会按下列顺序由请求自动调用：
 *
 * 	$controller = new Controller_Foo($request);
 * 	$controller->before();
 * 	$controller->action_bar();
 * 	$controller->after();
 *
 * 控制器动作通常在动作执行期间以 [View] 的形式将创建的输出添加到 `$this->response->body($output)`。
 *
 * @package BootPHP
 * @category 控制器
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
abstract class Controller {

	/**
	 * @var	Request		创建控制器的请求
	 */
	public $request;

	/**
	 * @var	Response	从控制器返回的响应
	 */
	public $response;

	/**
	 * 创建一个新的控制器实例。每个控制器必须由创建它的请求对象来构造。
	 *
	 * @param Request		$request	创建控制器的请求
	 * @param Response	$response	请求的响应
	 * @return void
	 */
	public function __construct(Request $request, Response $response)
	{
		// 将请求分配给控制器
		$this->request = $request;
		// 将响应分配给控制器
		$this->response = $response;
	}

	/**
	 * 在控制器动作之前自动执行。可以用来设置类的属性，执行授权检查，执行其它自定义代码。
	 *
	 * @return void
	 */
	public function before()
	{
		// 默认情况下什么都没有
	}

	/**
	 * 在控制器动作之后自动执行。可以用来对请求的响应实施转换，添加额外输出，执行其它自定义代码。
	 *
	 * @return void
	 */
	public function after()
	{
		// 默认情况下什么都没有
	}

}
