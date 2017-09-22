<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 订单控制器。
 *
 * @package	BootCMS
 * @category	控制器
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Controller_Order extends Controller_Template {

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
		$this->model = Model::factory('Order');
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
	 * 添加到订单
	 */
	public function action_create()
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
				$output->content = '您还没有登录，无法提交订单。';
				exit(json_encode($output));
			}
			$goodsIds = explode(',', Functions::text($this->request->post('gids')));
			$oCart = Model::factory('Cart');
			$goods = $oCart->findByIds($goodsIds);
			if (!$goods)
			{
				$output->status = 3;
				$output->title = '订单为空';
				$output->content = '订单中没有商品，无法提交订单。';
				exit(json_encode($output));
			}
			$shippingId = (int) $this->request->post('shipping');
			$oShipping = Model::factory('Shipping');
			$shipping = $oShipping->load($shippingId);
			if (!$shipping->id || $shipping->status != '0')
			{
				$output->status = 4;
				$output->title = '配送方式错误';
				$output->content = '该类配送方式不存在，无法提交订单。';
				exit(json_encode($output));
			}
			$amount = $freight = 0;
			foreach ($goods as $g)
			{
				if ($g->user_id <> $this->user->id)
				{
					$output->status = 5;
					$output->title = '非法操作';
					$output->content = '购物车中部分商品不属于您，无法结算。';
					exit(json_encode($output));
				}
				$amount += $g->price * $g->quantity;
			}
			if ($shipping->price_type == '1')
			{
				$areas = unserialize($shipping->areas);
				$areaId = '';
				$lids = array();
				foreach ($areas as $ak => $area)
				{
					$aids = explode(',', $area->area_id);
					foreach ($aids as $aid)
					{
						if ($aid == intval($this->request->post('addr_prov')))
						{
							$areaId = $aid;
							$freight = $areas[$ak]->base_price;
							break;
						}
					}
				}
				if (!$areaId)
				{
					$output->status = 3;
					$output->title = '配送方式错误';
					$output->content .= '所选配送方式无法将商品送达您指定的地址。';
					exit(json_encode($output));
				}
			}
			else
			{
				$freight = $shipping->base_price;
			}
			$amount += $freight;
			$oOrder = Model::factory('Order');
			$create = new stdClass();
			$create->order_no = date('ymdHi') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
			$create->user_id = $g->user_id;
			$create->freight = $freight;
			$create->amount = $amount;
			$create->consignee = Functions::text($this->request->post('consignee'));
			$create->phone = Functions::text($this->request->post('phone'));
			$create->addr_prov = (int) $this->request->post('addr_prov');
			$create->addr_city = (int) $this->request->post('addr_city');
			$create->addr_area = (int) $this->request->post('addr_area');
			$create->addr_detail = Functions::text($this->request->post('addr_detail'));
			$create->message = Functions::text($this->request->post('message'));
			$create->shipping = (int) $this->request->post('shipping');
			$create->status = 0;
			$create->created = time();
			try
			{
				if ($orderId = $oOrder->create($create))
				{
					$oOrderProduct = Model::factory('order_product');
					// 写入订单-商品表
					foreach ($goods as $g)
					{
						$oProduct = Model::factory('Product');
						$product = $oProduct->load($g->product_id);
						$op = new stdClass();
						$op->user_id = $g->user_id;
						$op->order_id = $orderId;
						$op->product_id = $g->product_id;
						$op->product = $g->product_name;
						$op->price = $g->price;
						$op->quantity = $g->quantity;
						$op->created = $create->created;
						$oOrderProduct->create($op);
						unset($product);
					}
					// 清理购物车
					$oCart->deleteByIds($this->user->id, $goodsIds);
					$output->status = 1;
					$output->title = '订单已创建';
					$output->content = '订单提交成功，请您尽快付款！';
					$output->order_id = $orderId;
				}
			}
			catch (Validation_Exception $e)
			{
				$errors = $e->errors('models');
				foreach ($errors as $ek => $ev)
				{
					$output->status = 6;
					$output->title = '操作失败';
					$output->content = $ev;
					break;
				}
			}
			exit(json_encode($output));
		}
	}

}
