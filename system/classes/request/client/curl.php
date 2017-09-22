<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * [Request_Client_External] Curl 驱动使用 php-curl 扩展执行外部请求。
 * 这是所有外部请求的默认驱动。
 *
 * @package BootPHP
 * @category Base
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class Request_Client_Curl extends Request_Client_External {

	/**
	 * 发送 HTTP 消息 [Request] 给远程服务器并处理响应。
	 *
	 * @param Request 要发送的请求
	 * @return Response
	 */
	public function _send_message(Request $request)
	{
		// 响应头部
		$response_headers = array();
		// 设置请求方式
		$options[CURLOPT_CUSTOMREQUEST] = $request->method();
		// 设置请求主体。与 POST 不同，这在 cURL 中是完全合法的，即使是使用了请求。
		// PUT 不支持这个方法，并且不需要在 put 之前向磁盘中写数据。如果阅读了 PHP 文档，您可能会有这样的印象。
		$options[CURLOPT_POSTFIELDS] = $request->body();
		// 处理头部
		if ($headers = $request->headers())
		{
			$http_headers = array();
			foreach ($headers as $key => $value)
			{
				$http_headers[] = $key . ': ' . $value;
			}
			$options[CURLOPT_HTTPHEADER] = $http_headers;
		}
		// 处理 cookies
		if ($cookies = $request->cookie())
		{
			$options[CURLOPT_COOKIE] = http_build_query($cookies, NULL, '; ');
		}
		// 创建响应
		$response = $request->create_response();
		$response_header = $response->headers();
		// 实现标准的解析参数
		$options[CURLOPT_HEADERFUNCTION] = array($response_header, 'parse_header_string');
		$this->_options[CURLOPT_RETURNTRANSFER] = true;
		$this->_options[CURLOPT_HEADER] = false;
		// 应用其它选项设置
		$options += $this->_options;
		$uri = $request->uri();
		if ($query = $request->query())
		{
			$uri .= '?' . http_build_query($query, NULL, '&');
		}
		// 打开一个新的远程连接
		$curl = curl_init($uri);
		// 设置连接选项
		if (!curl_setopt_array($curl, $options))
		{
			throw new Request_Exception('Failed to set CURL options, check CURL documentation: :url', array(':url' => 'http://php.net/curl_setopt_array'));
		}
		// 获得响应主体
		$body = curl_exec($curl);
		// 获得响应信息
		$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if ($body === false)
		{
			$error = curl_error($curl);
		}
		// 关闭连接
		curl_close($curl);
		if (isset($error))
		{
			throw new Request_Exception('Error fetching remote :url [ status :code ] :error', array(':url' => $request->url(), ':code' => $code, ':error' => $error));
		}
		$response->status($code)
			->body($body);
		return $response;
	}

}
