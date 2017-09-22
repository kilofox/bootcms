<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 用于自动模板化的抽象控制器类。
 *
 * @package BootPHP
 * @category 控制器
 * @author Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
abstract class Controller_Template extends Controller {

	/**
	 * @var View 页面模板
	 */
	public $template = 'template';

	/**
	 * @var boolean 自动渲染模板
	 * */
	public $auto_render = true;

	/**
	 * 加载模板 [View] 对象。
	 */
	public function before()
	{
		parent::before();
		if ($this->auto_render === true)
		{
			// 加载模板
			$this->template = View::factory($this->template);
		}
	}

	/**
	 * 分配模板 [View] 作为请求的响应。
	 */
	public function after()
	{
		if ($this->auto_render === true)
		{
			$this->response->body($this->template->render());
		}
		parent::after();
	}

}
