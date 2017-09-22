<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 支付控制器。
 *
 * @package	BootCMS
 * @category	控制器
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Controller_Pay extends Controller_Template {

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
		$this->model = Model::factory('Payment');
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
	 * 选择支付方式
	 */
	public function action_payments()
	{
		// 订单是否存在
		$orderId = (int) $this->request->param('id');
		$order = Model::factory('Order')->load($orderId);
		!$order->id || $order->user_id <> $this->user->id and $this->request->redirect();
		switch ($order->status)
		{
			case '0':
				$order->status = '未付款';
				break;
			case '1':
				$order->status = '等待发货';
				break;
			case '2':
				$order->status = '等待收货';
				break;
			case '3':
				$order->status = '已完成';
				break;
			case '4':
				$order->status = '已取消';
				break;
		}
		// 支付方式列表
		$payments = $this->model->findEnabledPayments();
		$this->template->title = '选择支付方式';
		$this->template->body = View::factory('pay/payments')
			->bind('order', $order)
			->bind('payments', $payments);
	}

	/**
	 * 支付
	 */
	public function action_pay()
	{
		// 订单是否存在
		$orderId = (int) $this->request->post('oid');
		$order = Model::factory('Order')->load($orderId);
		!$order->id || $order->user_id <> $this->user->id and $this->request->redirect();
		// 支付方式是否存在
		$paymentId = (int) $this->request->post('pid');
		$payment = $this->model->load($paymentId);
		if (!$payment)
		{
			$this->request->redirect('pay/payments/' . $orderId);
		}
		$payment = Payment::instance();
		$notifyUrl = 'http://' . $_SERVER['SERVER_NAME'] . $this->homeUrl . 'pay/respond_notify/' . $payment->config['driver'] . '/';
		$returnUrl = 'http://' . $_SERVER['SERVER_NAME'] . $this->homeUrl . 'pay/respond_return/' . $payment->config['driver'] . '/';
		$parameter = array(
			'payment_type' => '1',
			'notify_url' => $notifyUrl,
			'return_url' => $returnUrl,
			'out_trade_no' => $order->order_no,
			'subject' => '在线支付',
			'price' => $order->amount,
			'quantity' => '1',
			'logistics_fee' => $order->shipping,
			'logistics_type' => 'EXPRESS',
			'logistics_payment' => 'SELLER_PAY',
			'body' => 'Slowood Order No.: ' . $order->order_no,
			'show_url' => $_SERVER['SERVER_NAME'] . $this->homeUrl . 'member/order_view/' . $order->id . '/',
			'receive_name' => $order->consignee,
			'receive_address' => $order->address,
			'receive_zip' => '',
			'receive_phone' => '',
			'receive_mobile' => $order->phone,
		);
		$htmlText = $payment->buildRequestForm($parameter, 'post', '付款到支付宝');
		$this->template->title = '支付';
		$this->template->body = View::factory('pay/pay')
			->bind('order', $order)
			->bind('html', $htmlText);
	}

	/**
	 * 支付服务器端异步通知页面
	 * 如果没有收到该页面返回的 success 信息，支付宝会在24小时内按一定的时间策略重发通知
	 */
	public function action_respond_notify()
	{
		// 加入日志
		$log = Log::instance();
		$log->add(8, 'post data: ' . json_encode($_POST));
		if ($this->request->method() == 'POST')
		{
			$payment = Payment::instance();
			$verifyResult = $payment->verifyNotify();
			if ($verifyResult)
			{
				// 商户订单号
				$orderNo = HTML::chars($this->request->post('out_trade_no'));
				$oOrder = Model::factory('Order');
				$order = $oOrder->loadByOrderNo($orderNo);
				// 支付宝交易号
				$tradeNo = HTML::chars($this->request->post('trade_no'));
				// 交易状态
				if ($this->request->post('trade_status') == 'WAIT_SELLER_SEND_GOODS')
				{
					// 买家已付款
					if ($order->status == '0')
					{
						$order->status = '1';
						$order->trade_no = $tradeNo;
						if ($oOrder->update())
							echo 'success';
					}
				}
				else
				{
					$output = new stdClass();
					$output->trade_status = HTML::chars($this->request->post('trade_status'));
					$output->out_trade_no = HTML::chars($this->request->post('out_trade_no'));
					$output->trade_no = HTML::chars($this->request->post('trade_no'));
					$output->order_status = HTML::chars($order->status);
					exit(json_encode($output));
				}
			}
		}
		exit;
	}

	/**
	 * 支付服务器端页面跳转同步通知页面
	 */
	public function action_respond_return()
	{
		$payment = Payment::instance();
		$verifyResult = $payment->verifyReturn();
		$message = '支付服务器返回异常。';
		if ($verifyResult)
		{
			// 商户订单号
			$orderNo = HTML::chars($this->request->query('out_trade_no'));
			$oOrder = Model::factory('Order');
			$order = $oOrder->loadByOrderNo($orderNo);
			// 支付宝交易号
			$tradeNo = HTML::chars($this->request->query('trade_no'));
			// 交易状态
			if ($this->request->query('trade_status') == 'WAIT_SELLER_SEND_GOODS')
			{
				// 买家已付款
				if ($order->status == '0')
				{
					$order->status = '1';
					$order->trade_no = $tradeNo;
					$oOrder->update();
				}
				$message = '付款成功。请等待发货。/ Order has been paid. Please waiting for delivery.';
			}
			else
			{
				$message = '支付状态：' . HTML::chars($this->request->query('trade_status')) . '<br />支付宝交易号：' . $tradeNo;
			}
		}
		$this->template->title = '完成支付';
		$this->template->body = View::factory('pay/pay_complete')
			->bind('message', $message);
	}

}
