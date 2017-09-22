<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 产品控制器。
 *
 * @package	BootCMS
 * @category	控制器
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Controller_Product extends Controller_Template {

	/**
	 * Before 方法
	 */
	public function before()
	{
		parent::before();
		$cache = Cache::instance();
		if (!($views = $cache->get('views', false)))
		{
			$global = BootPHP::$config->load('global');
			$views = $global->get('views');
			$cache->set('views', $views);
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
		$this->model = Model::factory('Product');
		$this->homeUrl = Url::base();
		$this->template->title = '产品';
		$this->template->homeUrl = $this->homeUrl;
	}

	/**
	 * After 方法
	 */
	public function after()
	{
		parent::after();
	}

	/**
	 * 所有产品的页面
	 */
	public function action_index()
	{
		$cateId = $this->request->param('id') ? (int) $this->request->param('id') : 1;
		$oMedia = Model::factory('Media');
		$categories = Model::factory('product_category')->findByOrder();
		$products = $this->model->findByCategory($cateId);
		foreach ($products as $k => $p)
		{
			$pics = unserialize($p->pictures);
			$p->thumb = '';
			if (is_array($pics) && count($pics))
			{
				$media = $oMedia->load($pics[0]);
				$media->thumb_name && $p->thumb = $media->thumb_name;
			}
		}
		$this->template->body = View::factory('product/archives')
			->bind('categories', $categories)
			->bind('products', $products);
	}

	/**
	 * 加载产品节点
	 */
	public function action_entry()
	{
		$productId = (int) $this->request->param('id');
		$product = $this->model->load($productId);
		for ($i = 1; $i < 11; $i++)
		{
			$item = 'item' . $i;
			$product->$item = unserialize($product->$item);
		}
		// 解析图片
		$oMedia = Model::factory('Media');
		$product->pictures = unserialize($product->pictures);
		$pics = array();
		if (is_array($product->pictures) && count($product->pictures))
		{
			foreach ($product->pictures as $pic)
			{
				$media = $oMedia->load($pic);
				$pics[] = array($media->file_name, $media->thumb_name);
			}
		}
		$product->pictures = $pics;
		$categories = Model::factory('product_category')->findByOrder();
		$this->template->title = $product->product_name . ' - ' . $this->template->title;
		$this->template->body = View::factory('product/entry')
			->bind('categories', $categories)
			->bind('node', $product);
	}

	/**
	 * 搜索
	 */
	public function action_search()
	{
		$page = $this->request->query('page') > 0 ? (int) $this->request->query('page') : 1;
		$numPerPage = 20;
		$start = $numPerPage * ( $page - 1 );
		$query = HTML::chars($this->request->query('q'));
		list($nodes, $total) = Model::factory('Product')->search($query, $start, $numPerPage);
		$pagination = Functions::page($page, $total, $numPerPage, $this->homeUrl . 'product/search/?q=' . $query . '&page=');
		$this->template->body = View::factory('search')
			->bind('nodes', $nodes)
			->bind('query', $query)
			->bind('pagination', $pagination);
	}

}
