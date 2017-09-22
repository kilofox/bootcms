<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 首页控制器。
 *
 * @package	BootCMS
 * @category	控制器
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Controller_Homepage extends Controller_Template {

	/**
	 * Before 方法
	 */
	public function before()
	{
		parent::before();
		$cache = Cache::instance();
		if (!($views = $cache->get('views-homepage', false)))
		{
			$global = BootPHP::$config->load('global');
			$views = $global->get('homepage');
			$cache->set('views-homepage', $views);
		}
		foreach ($views as $key => $view)
		{
			if (!is_array($view))
				$this->template->$key = View::factory($view);
			else
				$this->template->$key = $view;
		}
		$this->user = Auth::instance()->get_user();
		if ($this->user)
		{
			$this->template->user = $this->user;
		}
		if (!($this->node = $cache->get('homepage', false)))
		{
			$this->node = Model::factory('Node')->findByType(2);
			if (isset($this->node->id))
			{
				$cache->set('homepage', $this->node);
			}
		}
		// 设置模板变量
		$this->homeUrl = Url::base();
		$this->template->homeUrl = $this->homeUrl;
		$this->template->slug = '';
		$this->template->keywords = $this->node->keywords;
		$this->template->description = $this->node->descript;
	}

	/**
	 * After 方法
	 */
	public function after()
	{
		parent::after();
	}

	/*
	 * 默认方法
	 * 该方法将节点加载到一个页面中。
	 */

	public function action_index()
	{
		$sidebar = '';
		if ($this->node->sidebar)
		{
			$sidebar = Setup::makeSidebar($this->node->sidebar);
		}
		$this->template->body = View::factory('homepage')
			->bind('node', $this->node)
			->bind('sidebar', $sidebar);
	}

	/**
	 * 搜索
	 */
	public function action_search()
	{
		$page = $this->request->query('page') > 0 ? $this->request->query('page') : 1;
		$numPerPage = 20;
		$start = $numPerPage * ( $page - 1 );
		$query = HTML::chars($this->request->query('q'));
		list($nodes, $total) = Model::factory('Node')->search($query, $start, $numPerPage);
		$pagination = Functions::page($page, $total, $numPerPage, $this->homeUrl . 'homepage/search/?q=' . $query . '&page=');
		$this->template->body = View::factory('search')
			->bind('nodes', $nodes)
			->bind('query', $query)
			->bind('pagination', $pagination);
	}

}
