<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 购物车控制器。
 *
 * @package	BootCMS
 * @category	控制器
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Controller_Cart extends Controller_Template {

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
		$this->model = Model::factory('Cart');
		$this->homeUrl = Url::base();
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
	 * 我的购物车
	 */
	public function action_index()
	{
		if ($this->user)
		{
			$oProduct = Model::factory('Product');
			$goods = $this->model->findByUser($this->user->id);
			foreach ($goods as $k => $g)
			{
				// 移除已下架的商品
				$product = $oProduct->load($g->product_id);
				if (!$product->id)
				{
					$this->model->load($g->id);
					$this->model->delete();
					unset($goods[$k]);
				}
			}
		}
		$this->template->title = '我的购物车';
		$this->template->body = View::factory('cart/cart')
			->bind('products', $goods)
			->bind('user', $this->user);
	}

	/**
	 * 添加到购物车
	 */
	public function action_addto()
	{
		$output = new stdClass();
		$output->status = 0;
		$output->title = '操作失败';
		$output->content = '您没有足够的权限进行此项操作。';
		if ($this->request->is_ajax())
		{
			if (!$this->user)
			{
				$output->status = 2;
				$output->title = '用户未登录';
				exit(json_encode($output));
			}
			$productId = (int) $this->request->post('pid');
			$product = Model::factory('Product')->load($productId);
			if (!$product->id)
			{
				$output->status = 3;
				$output->title = '商品不存在';
				$output->content = '商品不存在，无法放入购物车。';
				exit(json_encode($output));
			}
			$cart = $this->model->loadByUserAndProduct($this->user->id, $product->id);
			if ($cart->id)
			{
				$cart->quantity = ++$cart->quantity;
				$this->model->update();
				$output->status = 1;
				$output->title = '放入购物车';
				$output->content = '购物车中商品数量已更新。';
			}
			else
			{
				$cart = new stdClass();
				$cart->user_id = $this->user->id;
				$cart->product_id = $product->id;
				$cart->price = $product->promote && $product->promotion_price ? $product->promotion_price : $product->commodity_price;
				$cart->quantity = 1;
				$cart->created = time();
				if ($this->model->create($cart))
				{
					$output->status = 1;
					$output->title = '放入购物车';
					$output->content = '商品已成功放入购物车。';
				}
				else
				{
					$output->status = 4;
					$output->title = '放入购物车';
					$output->content = '商品放入购物车失败。';
				}
			}
			exit(json_encode($output));
		}
	}

	/**
	 * 结算，以生成订单
	 */
	public function action_settle_accounts()
	{
		!$this->user and $this->request->redirect();
		if ($this->request->method() == 'POST')
		{
			if (!$this->request->post('cid'))
			{
				$this->request->redirect('cart');
			}
			$oProduct = Model::factory('Product');
			$pids = substr(Functions::text($this->request->post('pids')), 0, -1);
			$goodsForSettle = explode(',', $pids);
			$goods = $this->model->findByUser($this->user->id);
			foreach ($goods as $k => $p)
			{
				// 移除已下架的商品
				$product = $oProduct->load($p->product_id);
				if (!$product)
					unset($goods[$k]);
				// 移除用户未选定的商品
				if (!in_array($p->product_id, $goodsForSettle))
					unset($goods[$k]);
			}
		}
		if (count($goods) == 0)
		{
			$this->request->redirect('cart');
		}
		$selProvs = Model::factory('Linkage')->findByParent(1);
		$shippings = Model::factory('Shipping')->findByStatus(0);
		$this->template->title = '商品结算';
		$this->template->body = View::factory('cart/settle')
			->bind('goods', $goods)
			->bind('selProvs', $selProvs)
			->bind('shippings', $shippings);
	}

	/**
	 * 改变购物车中商品数量
	 */
	public function action_change_num()
	{
		$output = new stdClass();
		$output->status = 0;
		$output->title = '操作失败';
		$output->content = '您没有足够的权限进行此项操作。';
		if ($this->request->is_ajax())
		{
			if (!$this->user)
			{
				$output->status = 2;
				$output->title = '用户未登录';
				$output->content = '请先登录后再来购物。';
				exit(json_encode($output));
			}
			$cartId = (int) $this->request->post('cid');
			$cart = $this->model->load($cartId);
			if (!$cart->id)
			{
				$output->status = 3;
				$output->title = '购物车不存在';
				$output->content = '您请求的购物车不存在。';
				exit(json_encode($output));
			}
			if ($cart->user_id <> $this->user->id)
			{
				$output->status = 4;
				$output->title = '操作失败';
				$output->content = '这不是您的购物车。';
				exit(json_encode($output));
			}
			$productId = (int) $this->request->post('pid');
			if ($cart->product_id <> $productId)
			{
				$output->status = 5;
				$output->title = '操作失败';
				$output->content = '请求的商品不在此购物车中。';
				exit(json_encode($output));
			}
			$product = Model::factory('Product')->load($cart->product_id);
			if (!$product->id)
			{
				$output->status = 6;
				$output->title = '商品不存在';
				$output->content = '商品不存在，或已下架，无法购买。';
				exit(json_encode($output));
			}
			$direction = Functions::text($this->request->post('direction'));
			if ($direction == 'dec' && $cart->quantity > 1)
			{
				$cart->quantity--;
			}
			else if ($direction == 'inc' && $cart->quantity < 1000)
			{
				$cart->quantity++;
			}
			else
			{
				$output->status = 0;
				$output->title = '非法操作';
				$output->content = '非法操作';
				exit(json_encode($output));
			}
			$this->model->update();
			$output->status = 1;
			$output->title = '商品数量更新成功';
			$output->data = $cart->quantity;
		}
		exit(json_encode($output));
	}

	/**
	 * 删除购物车中的商品
	 */
	public function action_remove()
	{
		$output = new stdClass();
		$output->status = 0;
		$output->title = '操作失败';
		if ($this->request->is_ajax())
		{
			if (!$this->user)
			{
				$output->status = 2;
				$output->title = '用户未登录';
				$output->content = '请先登录后再来购物。';
				exit(json_encode($output));
			}
			$cartId = (int) $this->request->post('cid');
			$cart = $this->model->load($cartId);
			if (!$cart->id)
			{
				$output->status = 3;
				$output->title = '购物车不存在';
				$output->content = '您请求的购物车不存在。';
				exit(json_encode($output));
			}
			if ($cart->user_id <> $this->user->id)
			{
				$output->status = 4;
				$output->title = '操作失败';
				$output->content = '这不是您的购物车。';
				exit(json_encode($output));
			}
			$productId = (int) $this->request->post('pid');
			if ($cart->product_id <> $productId)
			{
				$output->status = 5;
				$output->title = '操作失败';
				$output->content = '请求的商品不在此购物车中。';
				exit(json_encode($output));
			}
			if ($this->model->delete())
			{
				$output->status = 1;
				$output->title = '商品已删除';
				$output->content = '购物车中的商品删除成功。';
			}
			else
			{
				$output->status = 6;
				$output->title = '商品未删除';
				$output->content = '购物车中的商品删除失败。';
			}
		}
		exit(json_encode($output));
	}

	/**
	 * 联动菜单
	 */
	public function action_linkage()
	{
		$output = new stdClass();
		$output->status = 0;
		$output->title = '操作失败';
		if ($this->request->is_ajax())
		{
			$from = $this->request->post('from');
			if ($from != 'settle_account')
			{
				$output->status = 2;
				$output->title = '操作失败';
				$output->content = '无效的调用。';
			}
			$menuId = (int) $this->request->post('mid');
			$menus = array();
			if ($menuId != 0)
			{
				$menus = Model::factory('Linkage')->findByParent($menuId, true);
			}
			$output->status = 1;
			$output->title = '联动菜单';
			$output->data = $menus;
		}
		exit(json_encode($output));
	}

	/**
	 * 配送方式
	 */
	public function action_shippings()
	{
		$output = new stdClass();
		$output->status = 0;
		$output->title = '操作失败';
		if ($this->request->is_ajax())
		{
			$from = $this->request->post('from');
			if ($from != 'settle_account')
			{
				$output->status = 2;
				$output->title = '操作失败';
				$output->content = '无效的调用。';
			}
			$shippings = Model::factory('Shipping')->findByStatus(0);
			foreach ($shippings as $node)
			{
				$node->areas = unserialize($node->areas);
			}
			$output->status = 1;
			$output->title = '配送方式';
			$output->data = $shippings;
		}
		exit(json_encode($output));
	}

}
