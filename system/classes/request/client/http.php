<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * [Request_Client_External] HTTP 驱动使用 php-http 扩展处理外部请求。
 * 要使用该驱动，在执行外部请求之前确保以下内容已完成。最好是在应用引导中。
 *
 * @example
 *
 * 	// 在应用引导中
 * 	Request_Client_External::$client = 'Request_Client_HTTP';
 *
 * @package BootPHP
 * @category Base
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class Request_Client_HTTP extends Request_Client_External {

	/**
	 * 创建一个新的 `Request_Client` 对象，允许依赖注入。
	 *
	 * @param array 参数
	 * @throws Request_Exception
	 */
	public function __construct(array $params = array())
	{
		// 检查 PECL HTTP 支持请求
		if (!http_support(HTTP_SUPPORT_REQUESTS))
		{
			throw new Request_Exception('Need HTTP request support!');
		}
		// 继续
		parent::__construct($params);
	}

	/**
	 * @var	array	curl 选项
	 * @see	[http://www.php.net/manual/en/function.curl-setopt.php]
	 */
	protected $_options = array();

	/**
	 * 发送 HTTP 消息 [Request] 给远程服务器并处理响应。
	 *
	 * @param Request 要发送的请求
	 * @return Response
	 */
	public function _send_message(Request $request)
	{
		$http_method_mapping = array(
			HTTP_Request::GET => HTTPRequest::METH_GET,
			HTTP_Request::HEAD => HTTPRequest::METH_HEAD,
			HTTP_Request::POST => HTTPRequest::METH_POST,
			HTTP_Request::PUT => HTTPRequest::METH_PUT,
			HTTP_Request::DELETE => HTTPRequest::METH_DELETE,
			HTTP_Request::OPTIONS => HTTPRequest::METH_OPTIONS,
			HTTP_Request::TRACE => HTTPRequest::METH_TRACE,
			HTTP_Request::CONNECT => HTTPRequest::METH_CONNECT,
		);
		// 创建 http 请求对象
		$http_request = new HTTPRequest($request->uri(), $http_method_mapping[$request->method()]);
		if ($this->_options)
		{
			// 设置自定义选项
			$http_request->setOptions($this->_options);
		}
		// 设置头部
		$http_request->setHeaders($request->headers()->getArrayCopy());
		// 设置 cookies
		$http_request->setCookies($request->cookie());
		// 设置查询数据（?foo=bar&bar=foo）
		$http_request->setQueryData($request->query());
		// 设置主体
		if ($request->method() == HTTP_Request::PUT)
		{
			$http_request->addPutData($request->body());
		}
		else
		{
			$http_request->setBody($request->body());
		}
		try
		{
			$http_request->send();
		}
		catch (HTTPRequestException $e)
		{
			throw new Request_Exception($e->getMessage());
		}
		catch (HTTPMalformedHeaderException $e)
		{
			throw new Request_Exception($e->getMessage());
		}
		catch (HTTPEncodingException $e)
		{
			throw new Request_Exception($e->getMessage());
		}
		// 创建响应
		$response = $request->create_response();
		// 构建响应
		$response->status($http_request->getResponseCode())
			->headers($http_request->getResponseHeader())
			->cookie($http_request->getResponseCookies())
			->body($http_request->getResponseBody());
		return $response;
	}

}
