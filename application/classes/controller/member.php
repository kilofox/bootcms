<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 会员控制器。
 *
 * @package	BootCMS
 * @category	控制器
 * @Author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Controller_Member extends Controller_Template {

	/**
	 * Before 方法
	 *
	 * @return	void
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
		$this->model = Model::factory('User');
		$this->homeUrl = Url::base();
		$this->template->homeUrl = $this->homeUrl;
	}

	/**
	 * After 方法
	 *
	 * @return	void
	 */
	public function after()
	{
		parent::after();
	}

	/**
	 * 会员首页
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$this->request->redirect('member/profile');
	}

	/**
	 * 会员资料
	 *
	 * @return	void
	 */
	public function action_profile()
	{
		$uid = (int) $this->request->param('id');
		$uid <= 0 and $this->request->redirect();
		//$ownProfile = $uid > 0 && $uid == $this->user->id ? true : false;
		$member = Model::factory('forum_member')->loadWithUser($uid);
		if (!$member)
			$this->action_logout();
		switch ($member->level)
		{
			case 3:
				$member->level = '管理员';
				break;
			case 2:
				$member->level = '版主';
				break;
			case 1:
				$member->level = '会员';
				break;
		}
		$member->pp = round($member->posts / ((time() - $member->regdate) / 86400), 2);
		$member->created = Functions::makeDate($member->created);
		$member->regdate = Functions::makeDate($member->regdate);
		$member->last_login = Functions::makeDate($member->last_login);
		!$member->signature && $member->signature = '-';
		$this->template->body = View::factory('member/profile')
			->bind('member', $member);
	}

	/**
	 * 会员面板
	 *
	 * @return	void
	 */
	public function action_panel()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			if ($this->user)
			{
				$userId = (int) $this->request->post('uid');
				if ($userId <> $this->user->id)
				{
					$output->status = 2;
					$output->title = '操作失败';
					$output->content = '非法操作。';
					exit(json_encode($output));
				}
				try
				{
					$user = $this->model->load($userId);
					if ($this->request->post('password'))
					{
						$user->password = Functions::text($this->request->post('password'));
						$user->password_confirm = Functions::text($this->request->post('password_confirm'));
					}
					if ($this->request->post('nickname'))
					{
						$user->nickname = Functions::text($this->request->post('nickname'));
					}
					if ($this->model->update())
					{
						$output->status = 1;
						$output->title = '账户已更新';
						$output->content = '谢谢，您的账户信息已经更新完毕。';
					}
					else
					{
						$output->status = 2;
						$output->title = '账户未更新';
						$output->content = '您的账户信息没有更新。';
					}
				}
				catch (Validation_Exception $e)
				{
					$errors = $e->errors('models');
					foreach ($errors as $ev)
					{
						$output->status = 4;
						$output->title = '操作失败';
						$output->content = $ev;
						break;
					}
				}
			}
			exit(json_encode($output));
		}
		if (!$this->user)
		{
			$this->request->redirect();
		}
		$this->template->title = '用户中心';
		$this->template->body = View::factory('member/panel')
			->bind('user', $this->user);
	}

	/**
	 * 会员登录
	 *
	 * @return	void
	 */
	public function action_login()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			// 尝试登录
			Auth::instance()->login(strtolower(Functions::text($this->request->post('username'))), Functions::text($this->request->post('password')), false);
			$this->user = Auth::instance()->get_user();
			if ($this->user)
			{
				$output->status = 1;
				$output->title = '登录成功';
				$output->content = '';
			}
			else
			{
				$output->status = 2;
				$output->title = '登录失败';
				$output->content = '您输入的密码或用户名有误。';
			}
			exit(json_encode($output));
		}
		if ($this->user)
		{
			$this->request->redirect();
		}
		$refererTo = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		$this->template->title = '会员登录';
		$this->template->body = View::factory('member/login')
			->bind('refererTo', $refererTo);
	}

	/**
	 * 会员退出
	 *
	 * @return	void
	 */
	public function action_logout()
	{
		Auth::instance()->logout();
		unset($_SESSION['member']);
		$refererTo = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		$this->request->redirect($refererTo);
	}

	/**
	 * 注册新会员
	 *
	 * @return	void
	 */
	public function action_register()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->access_level = Admin::minimumLevel('create_user');
			$create = new stdClass();
			$create->username = strtolower(Functions::text($this->request->post('username')));
			$create->nickname = ucfirst(Functions::text($create->username));
			$create->password = Functions::text($this->request->post('password'));
			$create->password_confirm = Functions::text($this->request->post('password_confirm'));
			$create->email = strtolower(Functions::text($this->request->post('email')));
			$create->created = time();
			$user = $this->model->loadByUsername($create->username);
			if ($user)
			{
				$output->status = 2;
				$output->title = '注册失败';
				$output->content = '用户名已存在。';
				exit(json_encode($output));
			}
			$user = $this->model->loadByEmail($create->email);
			if ($user)
			{
				$output->status = 3;
				$output->title = '注册失败';
				$output->content = 'E-mail 已存在。';
				exit(json_encode($output));
			}
			try
			{
				if ($this->model->create($create))
				{
					$remember = false;
					$this->user = Auth::instance()->login($create->username, $create->password, $remember);
					$this->user = Auth::instance()->get_user();
					$website = Setup::siteInfo();
					$output->status = 1;
					$output->title = '注册成功';
					$output->content = $create->username . '，恭喜您注册成为 ' . $website->site_title . ' 会员！';
				}
			}
			catch (Validation_Exception $e)
			{
				$errors = $e->errors('models');
				foreach ($errors as $ek => $ev)
				{
					$output->status = 4;
					$output->title = '操作失败';
					$output->content = $ev;
					break;
				}
			}
			exit(json_encode($output));
		}
		$message = array();
		$template = 'member/register';
		if ($this->user)
		{
			$message['title'] = '重复注册';
			$message['message'] = '您已经是注册会员了，无需重复注册！';
			$template = 'errors/message';
		}
		$this->template->title = '会员注册';
		$this->template->body = View::factory($template)
			->bind('message', $message);
	}

	/**
	 * 我的订单
	 *
	 * @return	void
	 */
	public function action_orders()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			if (!$this->user)
			{
				$output->status = 2;
				$output->title = '尚未登录';
				$output->content = '您还没有登录，无法查看订单。';
				exit(json_encode($output));
			}
			// 分页
			$page = $this->request->post('page') > 0 ? (int) $this->request->post('page') : 1;
			$numPerPage = 10;
			$start = $numPerPage * ( $page - 1 );
			// 查询
			$sqlWhere = " AND user_id = {$this->user->id}";
			$sqlOrderBy = "created DESC";
			list($orders, $total) = Model::factory('Order')->findByPage($sqlWhere, $sqlOrderBy, $start, $numPerPage);
			$pagination = Functions::page($page, $total, $numPerPage);
			$data = '';
			foreach ($orders as $order)
			{
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
				$data .= '<tr>
					<td>' . $order->order_no . '</td>
					<td>' . $order->consignee . '</td>
					<td>¥' . $order->amount . '</td>
					<td>' . Functions::makeDate($order->created, 'Y-m-d H:i:s') . '</td>
					<td>' . $order->status . '</td>
					<td><a href="' . $this->homeUrl . 'member/order_view/' . $order->id . '/">查看订单</a></td>
				</tr>';
			}
			$output->status = 1;
			$output->data = $data;
			$output->pagination = $pagination;
			exit(json_encode($output));
		}
		!$this->user and $this->request->redirect('member/login');
		$this->template->title = '我的订单';
		$this->template->body = View::factory('member/order_list')
			->bind('user', $user);
	}

	/**
	 * 查看我的订单
	 *
	 * @return	void
	 */
	public function action_order_view()
	{
		!$this->user and $this->request->redirect('member/login');
		$orderId = (int) $this->request->param('id');
		$order = Model::factory('Order')->load($orderId);
		!$order->id || $order->user_id <> $this->user->id and $this->request->redirect();
		switch ($order->status)
		{
			case '0':
				$order->status = '等待付款';
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
		$oLinkage = Model::factory('Linkage');
		$addrProv = $oLinkage->load($order->addr_prov)->name;
		$addrCity = $oLinkage->load($order->addr_city)->name;
		$addrArea = $order->addr_area ? $oLinkage->load($order->addr_area)->name : '';
		$order->address = $addrProv . $addrCity . $addrArea . $order->addr_detail;
		$products = Model::factory('order_product')->findByOrder($orderId);
		$this->template->title = '订单信息';
		$this->template->body = View::factory('member/order_view')
			->bind('user', $user)
			->bind('order', $order)
			->bind('products', $products);
	}

}
