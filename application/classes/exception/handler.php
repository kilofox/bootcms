<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 异常处理器。
 *
 * @package	BootCMS
 * @category	异常
 * @author		Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class Exception_Handler {

	/**
	 * 处理器
	 *
	 * @param Exception 异常
	 * @return mixed 处理结果
	 */
	public static function handle(Exception $e)
	{
		$global = BootPHP::$config->load('global');
		$templateViews = $global->get('views');
		$template = View::factory('template');
		// 设置合适的 CSS、脚本、页头和页脚
		foreach ($templateViews as $key => $view)
		{
			if (is_array($view))
				$template->$key = $view;
		}
		switch (get_class($e))
		{
			case 'HTTP_Exception_404':
				$response = new Response;
				$response->status(404);
				$template->homeUrl = Url::base();
				$template->slug = Request::current()->param('id');
				$template->head = View::factory('header');
				$template->body = View::factory('errors/404');
				$template->foot = View::factory('footer');
				echo $response->body($template)->send_headers()->body();
				return true;
				break;
			default:
				return BootPHP_Exception::handler($e);
				break;
		}
	}

}
