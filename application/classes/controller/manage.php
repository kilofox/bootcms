<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 后台管理控制器。
 *
 * @package	BootCMS
 * @category	控制器
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Controller_Manage extends Controller_Template {

	/**
	 * Before 方法
	 *
	 * @return	void
	 */
	public function before()
	{
		parent::before();
		$this->homeUrl = Url::base();
		$global = BootPHP::$config->load('global_manage');
		$this->template = new View('template_manage');
		$this->template->homeUrl = $this->homeUrl;
		$this->user = Auth::instance()->get_user();
		!$this->user and $this->request->action('login');
		// 加载视图
		$global = BootPHP::$config->load('global_manage');
		// 检验用户是否已访问
		if (isset($this->accessLevel))
			$defaultViews = Functions::login_level_check($this->user, $this->accessLevel);
		else
			$defaultViews = Functions::login_level_check($this->user);
		if (isset($defaultViews[1]))
		{
			// 用户尚未登录
			$this->template->body = View::factory($defaultViews[1]);
		}
		else if ($this->user)
		{
			// 用户已经登录
			$this->template->user = $this->user;
		}
		// 设置相应的 CSS、脚本、页头和页脚
		foreach ($global->get($defaultViews[0]) as $key => $view)
		{
			if (!is_array($view))
				$this->template->$key = View::factory($view);
			else
				$this->template->$key = $view;
		}
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
	 * 后台首页（控制面板）
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$this->accessLevel = Admin::minimumLevel('index');
		if ($this->user->role_id >= $this->accessLevel)
		{
			$role = Model::factory('Role')->load($this->user->role_id);
			$this->user->role = $role->name;
			$this->template->body = View::factory('manage/dashboard')
				->bind('user', $this->user);
		}
	}

	/**
	 * 拒绝访问页
	 *
	 * @return	void
	 */
	public function action_denied()
	{
		$this->accessLevel = 0;
		$this->template->body = View::factory('manage/denied')
			->bind('user', $this->user);
	}

	/**
	 * 用户登录
	 *
	 * @return	void
	 */
	public function action_login()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			Auth::instance()->logout();
			// 尝试登录
			$this->user = Auth::instance()->login(strtolower(Functions::text($this->request->post('username'))), Functions::text($this->request->post('password')), false);
			$this->user = Auth::instance()->get_user();
			if ($this->user)
			{
				$output->status = 1;
				$output->title = '登录成功';
				$output->content = '您已经成功登录。';
			}
			else
			{
				$output->status = 2;
				$output->title = '登录失败';
				$output->content = '您输入的密码或用户名有误。';
			}
			exit(json_encode($output));
		}
		Auth::instance()->logout();
		$this->template->body = View::factory('manage/login');
	}

	/**
	 * 用户退出
	 *
	 * @return	void
	 */
	public function action_logout()
	{
		// 注销用户
		Auth::instance()->logout();
		// 重定向到登录页
		$this->request->redirect('manage/login');
	}

	/**
	 * 一般设置
	 *
	 * @return	void
	 */
	public function action_general_setting()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('setting_general');
			if ($this->user->role_id >= $this->accessLevel)
			{
				$siteId = (int) $this->request->post('sid');
				if ($siteId <> Cookie::get('mid'))
				{
					$output->status = 2;
					$output->title = '操作失败';
					$output->content = '非法操作。';
					exit(json_encode($output));
				}
				$oSite = Model::factory('Site');
				$site = $oSite->load($siteId);
				if (!$site->id)
				{
					$output->status = 3;
					$output->title = '操作失败';
					$output->content = '请求的站点不存在。';
					exit(json_encode($output));
				}
				try
				{
					$site->site_title = addslashes(HTML::chars($this->request->post('site_title')));
					$site->site_description = addslashes(HTML::chars($this->request->post('site_description')));
					$site->meta_keywords = Functions::text($this->request->post('meta_keywords'));
					$site->meta_description = Functions::text($this->request->post('meta_description'));
					$site->admin_email = Functions::text($this->request->post('admin_email'));
					$site->company = Functions::text($this->request->post('company'));
					$site->phone = Functions::text($this->request->post('phone'));
					$site->address = Functions::text($this->request->post('address'));
					$site->date_format = Functions::text($this->request->post('date_format'));
					$site->timezone = Functions::text($this->request->post('timezone'));
					if ($oSite->update())
					{
						$output->status = 1;
						$output->title = '网站信息已更新';
						$output->content = '网站信息已经更新成功。';
					}
					else
					{
						$output->status = 5;
						$output->title = '网站信息未更新';
						$output->content = '网站信息没有更新。';
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
		$this->accessLevel = Admin::minimumLevel('setting_general');
		if ($this->user->role_id >= $this->accessLevel)
		{
			$oSite = Model::factory('Site');
			$site = $oSite->findAll();
			$site = $site[0];
			Cookie::set('mid', $site->id);
			$this->template->body = View::factory('manage/setting_general')
				->bind('site', $site);
		}
	}

	/**
	 * 支付方式设置
	 *
	 * @return	void
	 */
	public function action_payment_setting()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('setting_payment');
			if ($this->user->role_id >= $this->accessLevel)
			{
				$paymentId = (int) $this->request->post('pid');
				$oPayment = Model::factory('Payment');
				$payment = $oPayment->load($paymentId);
				if (!$payment->id)
				{
					$output->status = 2;
					$output->title = '操作失败';
					$output->content = '请求的支付方式不存在。';
					exit(json_encode($output));
				}
				try
				{
					$config = new stdClass();
					$config->service_type = Functions::text($this->request->post('service_type'));
					$config->account = Functions::text($this->request->post('account'));
					$config->partner = Functions::text($this->request->post('partner'));
					$config->key = Functions::text($this->request->post('key'));
					$payment->pay_desc = addslashes($this->request->post('pay_desc'));
					$payment->list_order = (int) $this->request->post('list_order');
					$payment->config = serialize($config);
					if ($oPayment->update())
					{
						$file = fopen('application/config/payment.php', 'w');
						$content = '<?php defined(\'SYSPATH\') || exit(\'Access Denied.\');
return array
(
	\'alipay\' => array(
		\'driver\'		=> \'alipay\',
		\'service_type\'	=> ' . $config->service_type . ',
		\'account\'		=> \'' . $config->account . '\',
		\'partner\'		=> \'' . $config->partner . '\',
		\'key\'			=> \'' . $config->key . '\',
		\'sign_type\'		=> \'MD5\',
		\'input_charset\'	=> \'utf-8\',
		\'transport\'		=> \'http\',
		\'cacert\'		=> getcwd().DIRECTORY_SEPARATOR.\'assets\'.DIRECTORY_SEPARATOR.\'cacert.pem\'
	)
);';
						fwrite($file, $content);
						$output->status = 1;
						$output->title = '支付方式已更新';
						$output->content = '支付方式已经更新成功。';
					}
					else
					{
						$output->status = 3;
						$output->title = '支付方式未更新';
						$output->content = '支付方式没有更新。';
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
		$this->accessLevel = Admin::minimumLevel('setting_payment');
		if ($this->user->role_id >= $this->accessLevel)
		{
			$payments = Model::factory('Payment')->findAll();
			$alipay = $payments[0];
			$alipay->config = unserialize($alipay->config);
			$this->template->body = View::factory('manage/setting_payment')
				->bind('alipay', $alipay);
		}
	}

	/**
	 * 清除网站缓存
	 *
	 * @return	void
	 */
	public function action_cache()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('cache');
			if ($this->user->role_id >= $this->accessLevel)
			{
				Cache::instance()->deleteAll();
				$output->status = 1;
				$output->title = '网站缓存清理';
				$output->content = '网站缓存已被清理。';
			}
			exit(json_encode($output));
		}
		$this->accessLevel = Admin::minimumLevel('cache');
		if ($this->user->role_id >= $this->accessLevel)
		{
			$this->template->body = View::factory('manage/cache');
		}
	}

	/**
	 * 显示管理日志
	 *
	 * @return	void
	 */
	public function action_list_logs()
	{
		$output = new stdClass();
		$output->status = 0;
		$output->title = '操作失败';
		$output->content = '您没有足够的权限进行此项操作。';
		$this->accessLevel = Admin::minimumLevel('list_logs');
		if ($this->user->role_id >= $this->accessLevel)
		{
			if ($this->request->is_ajax())
			{
				// 分页
				$page = $this->request->post('page') > 0 ? (int) $this->request->post('page') : 1;
				$numPerPage = 20;
				$start = $numPerPage * ( $page - 1 );
				// 查询
				$sqlWhere = '';
				$sort = HTML::chars($this->request->post('sort'));
				switch ($sort)
				{
					case 'id-asc':
						$sqlOrderBy = 'l.`id` ASC';
						break;
					case 'id-desc':
					default:
						$sqlOrderBy = 'l.`id` DESC';
						break;
					case 'type-asc':
						$sqlOrderBy = "l.`type` ASC";
						break;
					case 'type-desc':
						$sqlOrderBy = "l.`type` DESC";
						break;
					case 'user-asc':
						$sqlOrderBy = "u.`username` ASC";
						break;
					case 'user-desc':
						$sqlOrderBy = "u.`username` DESC";
						break;
					case 'cont-asc':
						$sqlOrderBy = "l.`content` ASC";
						break;
					case 'cont-desc':
						$sqlOrderBy = "l.`content` DESC";
						break;
					case 'time-asc':
						$sqlOrderBy = "l.`created` ASC";
						break;
					case 'time-desc':
						$sqlOrderBy = "l.`created` DESC";
						break;
				}
				list($logs, $total) = Model::factory('Log')->findByPage($sqlWhere, $sqlOrderBy, $start, $numPerPage);
				$pagination = Functions::pageAdmin($page, $total, $numPerPage);
				$data = '';
				foreach ($logs as $log)
				{
					switch ($log->type)
					{
						case 1:
							$log->type = '创建';
							break;
						case 2:
							$log->type = '删除';
							break;
						case 3:
							$log->type = '修改';
							break;
						case 4:
							$log->type = '查询';
							break;
						default:
							$log->type = '未知';
							break;
					}
					$data .= '<tr>
						<td>' . $log->id . '</td>
						<td>' . $log->type . '</td>
						<td>' . $log->username . '</td>
						<td>' . $log->content . '</td>
						<td>' . Functions::makeDate($log->created, 'Y-m-d H:i') . '</td>
					</tr>';
				}
				$output->status = 1;
				$output->data = $data;
				$output->pagination = $pagination;
				exit(json_encode($output));
			}
			$this->template->body = View::factory('manage/log_list');
		}
	}

	/**
	 * 删除日志
	 *
	 * @return	void
	 */
	public function action_delete_logs()
	{
		$output = new stdClass();
		$output->status = 0;
		$output->title = '操作失败';
		$output->content = '您没有足够的权限进行此项操作。';
		if ($this->request->is_ajax())
		{
			$this->accessLevel = Admin::minimumLevel('delete_logs');
			if ($this->user->role_id >= $this->accessLevel)
			{
				if ($this->request->post('from') <> 'list_logs')
				{
					$output->status = 2;
					$output->title = '操作失败';
					$output->content = '非法操作。';
					exit(json_encode($output));
				}
				$oLog = Model::factory('Log');
				if ($num = $oLog->clearLogs())
				{
					$log = new stdClass();
					$log->type = 2;
					$log->user_id = $this->user->id;
					$log->node_id = 0;
					$log->content = '删除日志 ' . $num . ' 条';
					$log->ip = $_SERVER['REMOTE_ADDR'];
					$log->backup = serialize(NULL);
					$log->created = time();
					$log->status = 0;
					Model::factory('Log')->create($log);
					$output->status = 1;
					$output->title = '日志已删除';
					$output->content = '成功删除 ' . $num . ' 条日志。';
				}
				else
				{
					$output->status = 3;
					$output->title = '日志未删除';
					$output->content = '成功删除 0 条日志。';
				}
			}
		}
		exit(json_encode($output));
	}

	/**
	 * 创建一个新的单页
	 *
	 * @return	void
	 */
	public function action_create_page()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('create_page');
			if ($this->user->role_id >= $this->accessLevel)
			{
				$oNode = Model::factory('Node');
				$create = new stdClass();
				$create->node_title = HTML::chars(trim($this->request->post('node_title')));
				$create->slug = $this->request->post('node_slug') ? HTML::chars(trim($this->request->post('node_slug'))) : $this->request->post('node_title');
				$create->slug = $oNode->createSlug($create->slug);
				$create->node_intro = addslashes(trim($this->request->post('node_intro')));
				$create->node_content = addslashes(trim($this->request->post('node_content')));
				$create->author_id = $this->user->id;
				$create->commenting = $this->request->post('commenting') ? (int) $this->request->post('commenting') : 0;
				$create->submenu = (int) $this->request->post('submenu');
				$create->sidebar = (int) $this->request->post('sidebar');
				$create->status = (int) $this->request->post('status');
				$create->type = 1;
				$create->created = Functions::toTimestamp($this->request->post('time_m'), $this->request->post('time_d'), $this->request->post('time_y'), $this->request->post('time_hours'), $this->request->post('time_mins'));
				$create->last_edited = $create->created;
				try
				{
					if ($pageId = $oNode->create($create))
					{
						$page = $oNode->load($pageId);
						// 写入缓存
						$cache = Cache::instance();
						if (!$cache->get('slug-' . $page->slug, false))
						{
							$cache->set('slug-' . $page->slug, $page);
						}
						$output->status = 1;
						$output->title = '单页已创建';
						$output->content = '您的单页创建成功。';
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
		$this->accessLevel = Admin::minimumLevel('create_page');
		if ($this->user->role_id >= $this->accessLevel)
		{
			$blocks = Model::factory('region_block')->findAll();
			$this->template->body = View::factory('manage/page_create')
				->bind('blocks', $blocks)
				->bind('level', $this->user->role_id);
		}
	}

	/**
	 * 单页列表
	 *
	 * @return	void
	 */
	public function action_list_pages()
	{
		$this->accessLevel = Admin::minimumLevel('list_pages');
		if ($this->user->role_id >= $this->accessLevel)
		{
			if ($this->request->is_ajax())
			{
				// 分页
				$page = $this->request->post('page') > 0 ? (int) $this->request->post('page') : 1;
				$numPerPage = 20;
				$start = $numPerPage * ( $page - 1 );
				// 查询
				$sqlWhere = $this->request->post('trashed') == '1' ? ' AND n.status = 0' : ' AND n.status <> 0';
				if ($nickname = Functions::text($this->request->post('nickname')))
				{
					$sqlWhere.= " AND u.nickname LIKE '%$nickname%'";
				}
				if ($createdFrom = Functions::text($this->request->post('created_from')))
				{
					$createdFrom = explode('/', $createdFrom);
					if (count($createdFrom) == 3)
					{
						$timestamp = Functions::toTimestamp($createdFrom[0], $createdFrom[1], $createdFrom[2]);
						$sqlWhere.= " AND n.created > '$timestamp'";
					}
				}
				if ($createdTo = Functions::text($this->request->post('created_to')))
				{
					$createdTo = explode('/', $createdTo);
					if (count($createdTo) == 3)
					{
						$timestamp = Functions::toTimestamp($createdTo[0], $createdTo[1] + 1, $createdTo[2]);
						$sqlWhere.= " AND n.created < '$timestamp'";
					}
				}
				list($nodes, $total) = Model::factory('Node')->getPagesByPage($sqlWhere, $sqlOrderBy, $start, $numPerPage);
				$pagination = Functions::pageAdmin($page, $total, $numPerPage);
				$data = '';
				foreach ($nodes as $node)
				{
					$node->type = $node->type == '2' ? '首页' : '单页';
					switch ($node->status)
					{
						case '0':
							$node->status = '垃圾筒';
							break;
						case '1':
							$node->status = '已发布';
							break;
						case '2':
							$node->status = '草稿';
							break;
						case '3':
							$node->status = '待审核';
							break;
					}
					$data .= '<tr>
						<td>' . $node->id . '</td>
						<td>' . $node->type . '</td>
						<td>' . $node->node_title . '</td>
						<td>' . $node->username . '</td>
						<td>' . Functions::makeDate($node->created, 'Y-m-d H:i') . '</td>
						<td>' . $node->status . '</td>
						<td>
							<input type="image" src="' . $this->homeUrl . 'assets_manage/images/icn_edit.png" title="编辑" data-edit="' . $node->id . '" />
							<input type="image" src="' . $this->homeUrl . 'assets_manage/images/icn_tags.png" title="设为首页" data-home="' . $node->id . '" />
							<input type="image" src="' . $this->homeUrl . 'assets_manage/images/icn_jump_back.png" title="放入菜单" data-menu="' . $node->id . '" />
							<input type="image" src="' . $this->homeUrl . 'assets_manage/images/icn_categories.png" title="查看评论" data-comment="' . $node->id . '" />
							<input type="image" src="' . $this->homeUrl . 'assets_manage/images/icn_trash.png" title="删除" data-trash="' . $node->id . '" />
						</td>
					</tr>';
				}
				$output = new stdClass();
				$output->status = 1;
				$output->data = $data;
				$output->pagination = $pagination;
				exit(json_encode($output));
			}
			$this->template->body = View::factory('manage/page_list');
		}
	}

	/**
	 * 将单页扔进垃圾筒
	 *
	 * @return	void
	 */
	public function action_trash_page()
	{
		$output = new stdClass();
		$output->status = 0;
		$output->title = '操作失败';
		$output->content = '您没有足够的权限进行此项操作。';
		if ($this->request->is_ajax())
		{
			$this->accessLevel = Admin::minimumLevel('trash_page');
			if ($this->user->role_id >= $this->accessLevel)
			{
				if ($this->request->post('from') <> 'list_pages')
				{
					$output->status = 2;
					$output->title = '操作失败';
					$output->content = '非法操作。';
					exit(json_encode($output));
				}
				$oNode = Model::factory('Node');
				$nodeId = (int) $this->request->post('node_id');
				$node = $oNode->load($nodeId);
				if (!$node->id)
				{
					$output->status = 2;
					$output->title = '操作失败';
					$output->content = '您请求的单页不存在。';
					exit(json_encode($output));
				}
				$node->status = 0;
				if ($oNode->update())
				{
					$log = new stdClass();
					$log->type = 2;
					$log->user_id = $this->user->id;
					$log->node_id = $nodeId;
					$log->content = '将单页【' . $node->node_title . '】扔进垃圾筒';
					$log->ip = $_SERVER['REMOTE_ADDR'];
					$log->backup = serialize($node);
					$log->created = time();
					$log->status = 0;
					Model::factory('Log')->create($log);
					$output->status = 1;
					$output->title = '删除单页成功';
					$output->content = '单页 ' . $node->node_title . ' 已被扔进垃圾筒。';
				}
				else
				{
					$output->status = 4;
					$output->title = '删除单页失败';
					$output->content = '单页 ' . $node->node_title . ' 未能扔进垃圾筒。';
				}
			}
		}
		exit(json_encode($output));
	}

	/**
	 * 编辑单页
	 *
	 * @return	void
	 */
	public function action_edit_page()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('edit_page');
			if ($check->author_id != $this->user->id)
				$this->accessLevel = 7;
			if ($this->user->role_id >= $this->accessLevel)
			{
				$nodeId = (int) $this->request->post('node_id');
				$oNode = Model::factory('Node');
				$node = $oNode->load($nodeId);
				if (!$node->id)
				{
					$output->status = 2;
					$output->title = '操作失败';
					$output->content = '请求的单页不存在。';
					exit(json_encode($output));
				}

				$node->node_title = Functions::text($this->request->post('node_title'));
				$oldSlug = $node->slug;
				if ($slug = Functions::text($this->request->post('slug')))
				{
					if ($oldSlug <> $slug)
						$node->slug = $oNode->createSlug($slug);
				}
				else
				{
					$node->slug = $oNode->createSlug($node->node_title);
				}
				$node->keywords = Functions::text($this->request->post('keywords'));
				$node->descript = Functions::text($this->request->post('descript'));
				$node->node_intro = Functions::text($this->request->post('node_intro'));
				$node->node_content = addslashes($this->request->post('node_content'));
				$node->commenting = $this->request->post('commenting') ? (int) $this->request->post('commenting') : 0;
				$node->submenu = (int) $this->request->post('submenu');
				$node->sidebar = (int) $this->request->post('sidebar');
				$node->status = (int) $this->request->post('status');
				$node->last_edited = time();
				$node->created = Functions::toTimestamp($this->request->post('time_m'), $this->request->post('time_d'), $this->request->post('time_y'), $this->request->post('time_hours'), $this->request->post('time_mins'));
				try
				{
					if ($oNode->update())
					{
						foreach ($node as $k => $v)
						{
							if (in_array($k, array('node_title', 'node_intro', 'node_content')))
							{
								$node->$k = stripslashes($v);
							}
						}
						// 更新缓存
						$cache = Cache::instance();
						if ($oldSlug != $node->slug)
							$cache->delete('slug-' . $oldSlug);
						$cache->set('slug-' . $node->slug, $node);
						if ($node->type == '2')
							$cache->set('homepage', $node);
						$output->status = 1;
						$output->title = '单页已更新';
						$output->content = '该单页更新成功。';
						$output->slug = $node->slug;
					}
					else
					{
						$output->status = 3;
						$output->title = '单页未更新';
						$output->content = '该单页更新失败。';
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
		$this->accessLevel = Admin::minimumLevel('edit_page');
		$nodeId = (int) $this->request->param('id');
		$oNode = Model::factory('Node');
		$node = $oNode->load($nodeId);
		if ($node->author_id != $this->user->id)
			$this->accessLevel = 7;
		if ($this->user->role_id >= $this->accessLevel)
		{
			!$node->id and $this->request->redirect('manage/list_pages');
			$blocks = Model::factory('region_block')->findAll();
			$this->template->body = View::factory('manage/page_edit')
				->bind('node', $node)
				->bind('blocks', $blocks)
				->bind('level', $this->user->role_id);
		}
	}

	/**
	 * 将单页设置为首页
	 *
	 * @return	void
	 */
	public function action_set_homepage()
	{
		$output = new stdClass();
		$output->status = 0;
		$output->title = '操作失败';
		$output->content = '您没有足够的权限进行此项操作。';
		if ($this->request->is_ajax())
		{
			$this->accessLevel = Admin::minimumLevel('set_homepage');
			if ($this->user->role_id >= $this->accessLevel)
			{
				$oNode = Model::factory('Node');
				$nodeId = (int) $this->request->post('node_id');
				$node = $oNode->load($nodeId);
				if (!$node->id)
				{
					$output->status = 2;
					$output->title = '操作失败';
					$output->content = '您请求的单页不存在。';
					exit(json_encode($output));
				}
				if ($node->type == '2')
				{
					$output->status = 2;
					$output->title = '操作失败';
					$output->content = '您请求的单页已经是首页了，无需重复设置。';
					exit(json_encode($output));
				}
				if ($oNode->setHomepage($node->id))
				{
					$cache = Cache::instance();
					$cache->set('homepage', $node);
					$output->status = 1;
					$output->title = '设置成功';
					$output->content = '单页 ' . $node->node_title . ' 已被设置为首页。';
				}
				else
				{
					$output->status = 3;
					$output->title = '设置失败';
					$output->content = '单页 ' . $node->node_title . ' 未能设置为首页。';
				}
			}
		}
		exit(json_encode($output));
	}

	/**
	 * 将单页插入到菜单中
	 *
	 * @return	void
	 */
	public function action_insert_menu()
	{
		$output = new stdClass();
		$output->status = 0;
		$output->title = '操作失败';
		$output->content = '您没有足够的权限进行此项操作。';
		if ($this->request->is_ajax())
		{
			$this->accessLevel = Admin::minimumLevel('insert_menu');
			if ($this->user->role_id >= $this->accessLevel)
			{
				$oNode = Model::factory('Node');
				$nodeId = (int) $this->request->post('node_id');
				$node = $oNode->load($nodeId);
				if (!$node->id)
				{
					$output->status = 2;
					$output->title = '操作失败';
					$output->content = '您请求的单页不存在。';
					exit(json_encode($output));
				}
				$oBlock = Model::factory('region_block');
				$exist = $oBlock->isMenuExist(addslashes($node->node_title), $node->slug);
				if ($exist)
				{
					$output->status = 3;
					$output->title = '操作失败';
					$output->content = '您请求的单页已经存在于菜单中了，无需重复插入。';
					exit(json_encode($output));
				}
				$create = new stdClass();
				$create->region_id = 1;
				$create->block_title = Functions::text($node->node_title);
				$create->block_content = $node->slug;
				$create->status = 0;
				try
				{
					if ($oBlock->create($create))
					{
						// 删除菜单缓存
						$cache = Cache::instance();
						$cache->delete('menus');
						$output->status = 1;
						$output->title = '菜单已创建';
						$output->content = '您的菜单创建成功。';
					}
				}
				catch (Validation_Exception $e)
				{
					$errors = $e->errors('models');
					foreach ($errors as $ev)
					{
						$output->status = 3;
						$output->title = '操作失败';
						$output->content = $ev;
						break;
					}
				}
			}
		}
		exit(json_encode($output));
	}

	/**
	 * 评论列表
	 *
	 * @return	void
	 */
	public function action_list_comments()
	{
		$this->accessLevel = Admin::minimumLevel('list_comments');
		if ($this->user->role_id >= $this->accessLevel)
		{
			if ($this->request->is_ajax())
			{
				// 分页
				$page = $this->request->post('page') > 0 ? (int) $this->request->post('page') : 1;
				$numPerPage = 20;
				$start = $numPerPage * ( $page - 1 );
				// 查询
				$pageId = $this->request->post('pid') ? (int) $this->request->post('pid') : 0;
				$sqlWhere = ' AND c.node_id = ' . $pageId;
				if ($nickname = Functions::text($this->request->post('nickname')))
				{
					$sqlWhere.= " AND u.`nickname` LIKE '%$nickname%'";
				}
				if ($createdFrom = Functions::text($this->request->post('created_from')))
				{
					$createdFrom = explode('/', $createdFrom);
					if (count($createdFrom) == 3)
					{
						$timestamp = Functions::toTimestamp($createdFrom[0], $createdFrom[1], $createdFrom[2]);
						$sqlWhere.= " AND n.`created` > $timestamp";
					}
				}
				if ($createdTo = Functions::text($this->request->post('created_to')))
				{
					$createdTo = explode('/', $createdTo);
					if (count($createdTo) == 3)
					{
						$timestamp = Functions::toTimestamp($createdTo[0], $createdTo[1] + 1, $createdTo[2]);
						$sqlWhere.= " AND n.`created` < $timestamp";
					}
				}
				$pageNode = Model::factory('node')->load($pageId);
				$pageNode->node_title = '<a href="' . $this->homeUrl . '' . $pageNode->slug . '" target="_blank">' . $pageNode->node_title . '</a>';
				list($nodes, $total) = Model::factory('Node_Comment')->findByPage($sqlWhere, $sqlOrderBy, $start, $numPerPage);
				$pagination = Functions::pageAdmin($page, $total, $numPerPage);
				$data = '';
				foreach ($nodes as $node)
				{
					$node->type = $node->type == '2' ? '首页' : '单页';
					$data .= '<tr>
						<td>' . $node->id . '</td>
						<td>' . $node->nickname . '</td>
						<td>' . $pageNode->node_title . '</td>
						<td>' . $node->comment . '</td>
						<td>' . Functions::makeDate($node->created, 'Y-m-d H:i') . '</td>
						<td><input type="image" src="' . $this->homeUrl . 'assets_manage/images/icn_trash.png" title="删除" data-trash="' . $node->id . '"></td>
					</tr>';
				}
				$output = new stdClass();
				$output->status = 1;
				$output->data = $data;
				$output->pagination = $pagination;
				exit(json_encode($output));
			}
			$pageId = (int) $this->request->param('id');
			$this->template->body = View::factory('manage/comment_list')
				->bind('pageId', $pageId);
		}
	}

	/**
	 * 删除一条评论
	 *
	 * @return	void
	 */
	public function action_delete_comment()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('delete_comment');
			if ($this->user->role_id >= $this->accessLevel)
			{
				if ($this->request->post('from') <> 'list_comments')
				{
					$output->status = 2;
					$output->title = '操作失败';
					$output->content = '非法操作。';
					exit(json_encode($output));
				}
				$oNode = Model::factory('node_comment');
				$nodeId = (int) $this->request->post('node_id');
				$node = $oNode->load($nodeId);
				if (!$node->id)
				{
					$output->status = 2;
					$output->title = '操作失败';
					$output->content = '您请求的评论不存在。';
					exit(json_encode($output));
				}
				if ($oNode->delete())
				{
					$log = new stdClass();
					$log->type = 2;
					$log->user_id = $this->user->id;
					$log->node_id = $nodeId;
					$log->content = '删除评论【' . mb_substr($node->comment, 0, 80) . '】';
					$log->ip = $_SERVER['REMOTE_ADDR'];
					$log->backup = serialize($node);
					$log->created = time();
					$log->status = 0;
					Model::factory('Log')->create($log);
					$output->status = 1;
					$output->title = '删除评论成功';
					$output->content = '评论 ' . mb_substr($node->comment, 0, 80) . ' 已被删除。';
				}
				else
				{
					$output->status = 4;
					$output->title = '删除单页失败';
					$output->content = '评论 ' . mb_substr($node->comment, 0, 80) . ' 未能删除。';
				}
			}
			exit(json_encode($output));
		}
	}

	/**
	 * 显示所有区域与块
	 *
	 * @return	void
	 */
	public function action_list_regions()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('list_regions');
			if ($this->user->role_id >= $this->accessLevel)
			{
				// 分页
				$page = $this->request->post('page') > 0 ? (int) $this->request->post('page') : 1;
				$numPerPage = 20;
				$start = $numPerPage * ( $page - 1 );
				// 查询
				$sqlWhere = '';
				$sqlOrderBy = 'b.list_order';
				list($blocks, $total) = Model::factory('region_block')->findWithRegionByPage($sqlWhere, $sqlOrderBy, $start, $numPerPage);
				$pagination = Functions::pageAdmin($page, $total, $numPerPage);
				$data = '';
				$newRegion = 0;
				foreach ($blocks as $block)
				{
					$block->type = $block->type == '0' ? '菜单' : '碎片';
					$editRegion = in_array($block->region_id, array('1', '2')) ? $block->region_title : '<a data-edit-region="' . $block->region_id . '" title="编辑区域">' . $block->region_title . '</a>';
					$data .= '<tr>
						<td>' . $block->block_title . '</td>
						<td>' . $block->list_order . '</td>
						<td>' . $block->type . '</td>
						<td>' . $editRegion . '</td>
						<td><input type="image" src="' . $this->homeUrl . 'assets_manage/images/icn_edit.png" title="编辑块" data-edit-block="' . $block->id . '"/><input type="image" src="' . $this->homeUrl . 'assets_manage/images/icn_trash.png" title="删除块" data-del-block="' . $block->id . '"></td>
					</tr>';
					$newRegion = $block->region_id;
				}
				$output = new stdClass();
				$output->status = 1;
				$output->data = $data;
				$output->pagination = $pagination;
			}
			exit(json_encode($output));
		}
		$this->accessLevel = Admin::minimumLevel('list_regions');
		if ($this->user->role_id >= $this->accessLevel)
		{
			$regions = Model::factory('Region')->findAll();
			$this->template->body = View::factory('manage/region_list')
				->bind('regions', $regions);
		}
	}

	/**
	 * 创建一个新的区域
	 *
	 * @return	void
	 */
	public function action_create_region()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('create_region');
			if ($this->user->role_id >= $this->accessLevel)
			{
				if ($this->request->post('from') <> 'list_regions')
				{
					$output->status = 2;
					exit(json_encode($output));
				}
				$oRegion = Model::factory('Region');
				$create = new stdClass();
				$create->type = $this->request->post('region_type') ? (int) $this->request->post('region_type') : 0;
				$create->region_title = Functions::text($this->request->post('region_title'));
				try
				{
					if ($oRegion->create($create))
					{
						$output->status = 1;
						$output->title = '区域已创建';
						$output->content = '您的区域创建成功。';
					}
				}
				catch (Validation_Exception $e)
				{
					$errors = $e->errors('models');
					foreach ($errors as $ev)
					{
						$output->status = 3;
						$output->title = '操作失败';
						$output->content = $ev;
						break;
					}
				}
			}
			exit(json_encode($output));
		}
	}

	/**
	 * 编辑区域
	 *
	 * @return	void
	 */
	public function action_edit_region()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('edit_region');
			if ($check->author_id != $this->user->id)
				$this->accessLevel = 7;
			if ($this->user->role_id >= $this->accessLevel)
			{
				if ($this->request->post('from') <> 'list_regions')
				{
					$output->status = 2;
					exit(json_encode($output));
				}
				$regionId = (int) $this->request->post('region_id');
				$oRegion = Model::factory('Region');
				$region = $oRegion->load($regionId);
				if (!$region->id)
				{
					$output->status = 2;
					$output->title = '操作失败';
					$output->content = '请求的区域不存在。';
					exit(json_encode($output));
				}
				if ($this->request->post('action') == 'get_info')
				{
					$output->status = 1;
					$output->data = $region;
					exit(json_encode($output));
				}

				if (in_array($region->id, array('1', '2')))
				{
					$output->status = 2;
					$output->title = '操作失败';
					$output->content = '系统自带区域不能编辑。';
					exit(json_encode($output));
				}
				$region->region_title = Functions::text($this->request->post('region_title'));
				try
				{
					if ($oRegion->update())
					{
						$output->status = 1;
						$output->title = '区域已更新';
						$output->content = '该区域更新成功。';
					}
					else
					{
						$output->status = 3;
						$output->title = '区域未更新';
						$output->content = '该区域更新失败。';
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
	}

	/**
	 * 删除区域
	 *
	 * @return	void
	 */
	public function action_delete_region()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('delete_region');
			if ($this->user->role_id >= $this->accessLevel)
			{
				$regionId = (int) $this->request->post('region_id');
				$oRegion = Model::factory('Region');
				$region = $oRegion->load($regionId);
				if (!$region->id)
				{
					$output->status = 2;
					$output->title = '删除失败';
					$output->content = '您请求的区域不存在。';
					exit(json_encode($output));
				}
				if (in_array($region->id, array('1', '2')))
				{
					$output->status = 3;
					$output->title = '操作失败';
					$output->content = '系统自带区域不能删除。';
					exit(json_encode($output));
				}
				$blocks = Model::factory('region_block')->findByRegion($regionId);
				if ($blocks)
				{
					$output->status = 4;
					$output->title = '删除失败';
					$output->content = '您请求的区域已被 ' . $blocks[0]->block_title . ' 等块使用，不能删除。';
					exit(json_encode($output));
				}
				if ($oRegion->delete())
				{
					$output->status = 1;
					$output->title = '删除成功';
					$output->content = '您请求的区域已经删除。';
				}
				else
				{
					$output->status = 4;
					$output->title = '删除失败';
					$output->content = '您请求的区域删除失败。';
				}
			}
			exit(json_encode($output));
		}
	}

	/**
	 * 创建一个新的块
	 *
	 * @return	void
	 */
	public function action_create_block()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('create_region');
			if ($this->user->role_id >= $this->accessLevel)
			{
				if ($this->request->post('from') <> 'list_regions')
				{
					$output->status = 2;
					exit(json_encode($output));
				}
				$oBlock = Model::factory('region_block');
				$create = new stdClass();
				$create->region_id = (int) $this->request->post('block_region');
				$create->block_title = Functions::text($this->request->post('block_title'));
				$create->block_content = Functions::text($this->request->post('block_content'));
				$create->list_order = (int) $this->request->post('block_order');
				$create->status = (int) $this->request->post('block_status');
				try
				{
					if ($oBlock->create($create))
					{
						// 删除菜单缓存
						$cache = Cache::instance();
						$cache->delete('menus');
						$output->status = 1;
						$output->title = '块已创建';
						$output->content = '您的块创建成功。';
					}
				}
				catch (Validation_Exception $e)
				{
					$errors = $e->errors('models');
					foreach ($errors as $ev)
					{
						$output->status = 3;
						$output->title = '操作失败';
						$output->content = $ev;
						break;
					}
				}
			}
			exit(json_encode($output));
		}
	}

	/**
	 * 编辑块
	 *
	 * @return	void
	 */
	public function action_edit_block()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('edit_block');
			if ($check->author_id != $this->user->id)
				$this->accessLevel = 7;
			if ($this->user->role_id >= $this->accessLevel)
			{
				if ($this->request->post('from') <> 'list_regions')
				{
					$output->status = 2;
					exit(json_encode($output));
				}
				$blockId = (int) $this->request->post('block_id');
				$oBlock = Model::factory('region_block');
				$block = $oBlock->load($blockId);
				if (!$block->id)
				{
					$output->status = 2;
					$output->title = '操作失败';
					$output->content = '请求的块不存在。';
					exit(json_encode($output));
				}
				if ($this->request->post('action') == 'get_info')
				{
					$output->status = 1;
					$output->data = $block;
					exit(json_encode($output));
				}

				$block->region_id = (int) $this->request->post('block_region');
				$block->block_title = Functions::text($this->request->post('block_title'));
				$block->block_content = addslashes(trim($this->request->post('block_content')));
				$block->list_order = (int) $this->request->post('block_order');
				$block->status = (int) $this->request->post('block_status');
				try
				{
					if ($oBlock->update())
					{
						if ($block->region_id == 1)
						{
							// 删除菜单缓存
							$cache = Cache::instance();
							$cache->delete('menus');
						}
						else if ($block->region_id == 2)
						{
							// 删除菜单缓存
							$cache = Cache::instance();
							$cache->delete('sidebar-' . $blockId);
						}
						$output->status = 1;
						$output->title = '块已更新';
						$output->content = '该块更新成功。';
					}
					else
					{
						$output->status = 3;
						$output->title = '块未更新';
						$output->content = '该块没有更新。';
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
	}

	/**
	 * 删除块
	 *
	 * @return	void
	 */
	public function action_delete_block()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('delete_block');
			if ($this->user->role_id >= $this->accessLevel)
			{
				$blockId = (int) $this->request->post('block_id');
				$oBlock = Model::factory('region_block');
				$block = $oBlock->load($blockId);
				if (!$block->id)
				{
					$output->status = 2;
					$output->title = '删除失败';
					$output->content = '您请求的块不存在。';
					exit(json_encode($output));
				}
				if ($oBlock->delete())
				{
					// 删除菜单缓存
					$cache = Cache::instance();
					$cache->delete('menus');
					$output->status = 1;
					$output->title = '删除成功';
					$output->content = '您请求的块已经删除。';
				}
				else
				{
					$output->status = 3;
					$output->title = '删除失败';
					$output->content = '您请求的块删除失败。';
				}
			}
			exit(json_encode($output));
		}
	}

	/**
	 * 创建一个新的用户
	 *
	 * @return	void
	 */
	public function action_create_user()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('create_user');
			if ($this->user->role_id >= $this->accessLevel)
			{
				$oUser = Model::factory('User');
				$create = new stdClass();
				$create->username = strtolower(Functions::text($this->request->post('username')));
				$create->password = Functions::text($this->request->post('password'));
				$create->password_confirm = Functions::text($this->request->post('password_confirm'));
				$create->nickname = Functions::text($this->request->post('nickname'));
				$create->first_name = Functions::text($this->request->post('first_name'));
				$create->company = Functions::text($this->request->post('company'));
				$create->email = strtolower(Functions::text($this->request->post('email')));
				$create->secondary_email = strtolower(Functions::text($this->request->post('secondary_email')));
				$create->phone = Functions::text($this->request->post('phone'));
				$create->address = Functions::text($this->request->post('address'));
				$create->created = time();
				$user = $oUser->loadByUsername($create->username);
				if ($user->id)
				{
					$output->status = 2;
					$output->title = '用户创建失败';
					$output->content = '用户名已存在。';
					exit(json_encode($output));
				}
				$user = $oUser->loadByEmail($create->email);
				if ($user->id)
				{
					$output->status = 2;
					$output->title = '用户创建失败';
					$output->content = 'E-mail 已存在。';
					exit(json_encode($output));
				}
				try
				{
					if ($oUser->create($create))
					{
						$output->status = 1;
						$output->title = '用户已创建';
						$output->content = '新用户创建成功。';
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
		$this->accessLevel = Admin::minimumLevel('create_user');
		if ($this->user->role_id >= $this->accessLevel)
		{
			$this->template->body = View::factory('manage/user_create');
		}
	}

	/**
	 * 显示所有用户
	 *
	 * @return	void
	 */
	public function action_list_users()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('list_users');
			if ($this->user->role_id >= $this->accessLevel)
			{
				// 分页
				$page = $this->request->post('page') > 0 ? (int) $this->request->post('page') : 1;
				$numPerPage = 20;
				$start = $numPerPage * ( $page - 1 );
				// 查询
				$sqlWhere = '';
				if ($nickname = Functions::text($this->request->post('nickname')))
				{
					$sqlWhere.= " AND `nickname` LIKE '%$nickname%'";
				}
				if ($createdFrom = Functions::text($this->request->post('created_from')))
				{
					$createdFrom = explode('/', $createdFrom);
					if (count($createdFrom) == 3)
					{
						$timestamp = Functions::toTimestamp($createdFrom[0], $createdFrom[1], $createdFrom[2]);
						$sqlWhere.= " AND `created` > '$timestamp'";
					}
				}
				if ($createdTo = Functions::text($this->request->post('created_to')))
				{
					$createdTo = explode('/', $createdTo);
					if (count($createdTo) == 3)
					{
						$timestamp = Functions::toTimestamp($createdTo[0], $createdTo[1] + 1, $createdTo[2]);
						$sqlWhere.= " AND `created` < '$timestamp'";
					}
				}
				list($users, $total) = Model::factory('User')->getUsersByPage($sqlWhere, $sqlOrderBy, $start, $numPerPage);
				$pagination = Functions::pageAdmin($page, $total, $numPerPage);
				$data = '';
				foreach ($users as $user)
				{
					$data .= '<tr>
						<td>' . $user->id . '</td>
						<td>' . $user->username . '</td>
						<td>' . $user->nickname . '</td>
						<td>' . $user->email . '</td>
						<td>' . $user->phone . '</td>
						<td>' . Functions::makeDate($user->created, 'Y-m-d H:i') . '</td>
						<td><input type="image" src="' . $this->homeUrl . 'assets_manage/images/icn_edit.png" title="编辑" data-use="edit" data-val="' . $user->id . '"/><input type="image" src="' . $this->homeUrl . 'assets_manage/images/icn_trash.png" title="删除" data-use="delete" data-val="' . $user->id . '"></td>
					</tr>';
				}
				$output->status = 1;
				$output->data = $data;
				$output->pagination = $pagination;
			}
			exit(json_encode($output));
		}

		$this->accessLevel = Admin::minimumLevel('list_users');
		if ($this->user->role_id >= $this->accessLevel)
		{
			$this->template->body = View::factory('manage/user_list');
		}
	}

	/**
	 * 用户个人资料
	 *
	 * @return	void
	 */
	public function action_edit_user()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('edit_user');
			if ($this->user->role_id >= $this->accessLevel)
			{
				$userId = (int) $this->request->post('uid');
				if ($userId <> Cookie::get('mid'))
				{
					$output->status = 2;
					$output->title = '操作失败';
					$output->content = '非法操作。';
					exit(json_encode($output));
				}
				$oUser = Model::factory('User');
				$user = $oUser->load($userId);
				if (!$user->id)
				{
					$output->status = 3;
					$output->title = '操作失败';
					$output->content = '请求的用户不存在。';
					exit(json_encode($output));
				}
				try
				{
					unset($user->password);
					$user->company = Functions::text($this->request->post('company'));
					$user->email = Functions::text($this->request->post('email'));
					$user->secondary_email = Functions::text($this->request->post('secondary_email'));
					$user->phone = Functions::text($this->request->post('phone'));
					$user->address = Functions::text($this->request->post('address'));
					if ($this->request->post('password'))
					{
						$user->password = Functions::text($this->request->post('password'));
						$user->password_confirm = Functions::text($this->request->post('password_confirm'));
					}
					if ($this->request->post('nickname'))
					{
						$user->nickname = Functions::text($this->request->post('nickname'));
					}
					if ($oUser->update())
					{
						$output->status = 1;
						$output->title = '用户已更新';
						$output->content = '用户信息已经更新完毕。';
					}
					else
					{
						$output->status = 1;
						$output->title = '用户未更新';
						$output->content = '用户信息没有更新。';
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

		$this->accessLevel = Admin::minimumLevel('edit_user');
		if ($this->user->role_id >= $this->accessLevel)
		{
			$oUser = Model::factory('User');
			$userId = $this->request->param('id') ? (int) $this->request->param('id') : $this->user->id;
			$user = $oUser->load($userId);
			Cookie::set('mid', $user->id);
			$this->template->body = View::factory('manage/user_edit')
				->bind('user', $user);
		}
	}

	/**
	 * 删除用户
	 *
	 * @return	void
	 */
	public function action_delete_user()
	{
		$output = new stdClass();
		$output->status = 0;
		$output->title = '操作失败';
		$output->content = '您没有足够的权限进行此项操作。';
		if ($this->request->is_ajax())
		{
			$this->accessLevel = Admin::minimumLevel('delete_user');
			if ($this->user->role_id >= $this->accessLevel)
			{
				if ($this->request->post('from') <> 'list_users')
				{
					$output->status = 2;
					$output->title = '操作失败';
					$output->content = '非法操作。';
					exit(json_encode($output));
				}
				$oUser = Model::factory('User');
				$userId = (int) $this->request->post('user_id');
				$user = $oUser->load($userId);
				if (!$user->id)
				{
					$output->status = 3;
					$output->title = '操作失败';
					$output->content = '请求的用户不存在。';
					exit(json_encode($output));
				}
				if ($user->id == 1)
				{
					$output->status = 4;
					$output->title = '删除用户失败';
					$output->content = '顶级管理员不可删除。';
					exit(json_encode($output));
				}
				if ($oUser->delete())
				{
					$log = new stdClass();
					$log->type = 2;
					$log->user_id = $this->user->id;
					$log->node_id = $userId;
					$log->content = '删除用户【' . $user->username . '】';
					$log->ip = $_SERVER['REMOTE_ADDR'];
					$log->backup = serialize($user);
					$log->created = time();
					$log->status = 0;
					Model::factory('Log')->create($log);
					$output->status = 1;
					$output->title = '删除用户成功';
					$output->content = '用户 ' . $user->username . ' 已被删除。';
				}
				else
				{
					$output->status = 5;
					$output->title = '删除用户失败';
					$output->content = '用户 ' . $user->username . ' 未能删除。';
				}
			}
		}
		exit(json_encode($output));
	}

	/**
	 * 媒体库列表
	 *
	 * @return	void
	 */
	public function action_list_media_groups()
	{
		$this->accessLevel = Admin::minimumLevel('list_media_groups');
		if ($this->user->role_id >= $this->accessLevel)
		{
			$oGroup = Model::factory('media_group');
			$groups = $oGroup->findAll();
			$this->template->body = View::factory('manage/media_group_list')
				->bind('groups', $groups);
		}
	}

	/**
	 * 创建媒体分组
	 *
	 * @return	void
	 */
	public function action_create_media_group()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('create_media_group');
			if ($this->user->role_id >= $this->accessLevel)
			{
				if ($this->request->post('from') <> 'list_media_groups')
				{
					$output->status = 2;
					exit(json_encode($output));
				}
				$slug = Functions::toLink($this->request->post('slug'));
				$pathName = getcwd() . '/assets/uploads/' . $slug;
				if (!file_exists($pathName) && !mkdir($pathName, 0755, true))
				{
					$output->title = '媒体分组创建失败';
					$output->content = '无法创建目录：' . $pathName;
					exit(json_encode($output));
				}
				chmod($pathName, 0755);
				$oGroup = Model::factory('media_group');
				$create = new stdClass();
				$create->slug = $slug;
				$create->group_name = Functions::text($this->request->post('group_name'));
				$create->rs_width = (int) $this->request->post('rs_width');
				$create->rs_height = (int) $this->request->post('rs_height');
				$create->tn_width = (int) $this->request->post('tn_width');
				$create->tn_height = (int) $this->request->post('tn_height');
				$create->created = time();
				try
				{
					if ($oGroup->create($create))
					{
						$output->status = 1;
						$output->title = '媒体分组已创建';
						$output->content = '您的媒体分组创建成功。';
					}
				}
				catch (Validation_Exception $e)
				{
					$errors = $e->errors('models');
					foreach ($errors as $ev)
					{
						$output->status = 3;
						$output->title = '操作失败';
						$output->content = $ev;
						break;
					}
				}
			}
			exit(json_encode($output));
		}
	}

	/**
	 * 编辑媒体分组
	 *
	 * @return	void
	 */
	public function action_edit_media_group()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('edit_media_group');
			if ($this->user->role_id >= $this->accessLevel)
			{
				$groupId = (int) $this->request->post('group_id');
				$oMediaGroup = Model::factory('Media_Group');
				$group = $oMediaGroup->load($groupId);
				if (!$group->id)
				{
					$output->status = 3;
					$output->title = '操作失败';
					$output->content = '请求的媒体分组不存在。';
					exit(json_encode($output));
				}
				if ($this->request->post('action') == 'get_info')
				{
					$output->status = 1;
					$output->data = $group;
					exit(json_encode($output));
				}

				try
				{
					$group->group_name = Functions::text($this->request->post('group_name'));
					$group->rs_width = (int) $this->request->post('rs_width');
					$group->rs_height = (int) $this->request->post('rs_height');
					$group->tn_width = (int) $this->request->post('tn_width');
					$group->tn_height = (int) $this->request->post('tn_height');
					if ($oMediaGroup->update())
					{
						$output->status = 1;
						$output->title = '媒体分组已更新';
						$output->content = '媒体分组信息已经更新完毕。';
					}
					else
					{
						$output->status = 1;
						$output->title = '媒体分组未更新';
						$output->content = '媒体分组信息没有更新。';
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
	}

	/**
	 * 删除媒体分组
	 *
	 * @return	void
	 */
	public function action_delete_media_group()
	{
		$output = new stdClass();
		$output->status = 0;
		$output->title = '操作失败';
		$output->content = '您没有足够的权限进行此项操作。';
		if ($this->request->is_ajax())
		{
			$this->accessLevel = Admin::minimumLevel('delete_media_group');
			if ($this->user->role_id >= $this->accessLevel)
			{
				if ($this->request->post('from') <> 'list_media_groups')
				{
					$output->status = 2;
					$output->title = '操作失败';
					$output->content = '非法操作。';
					exit(json_encode($output));
				}
				$oGroup = Model::factory('Media_Group');
				$groupId = (int) $this->request->post('group_id');
				$group = $oGroup->load($groupId);
				if (!$group->id)
				{
					$output->status = 3;
					$output->title = '删除媒体分组失败';
					$output->content = '请求的媒体分组不存在。';
					exit(json_encode($output));
				}
				$media = Model::factory('media')->findByGroup($group->id);
				if ($media)
				{
					$output->status = 4;
					$output->title = '删除媒体分组失败';
					$output->content = '请求的媒体分组中存在媒体，不能删除。';
					exit(json_encode($output));
				}
				if ($oGroup->delete())
				{
					$output->status = 1;
					$output->title = '删除媒体分组成功';
					$output->content = '媒体分组 ' . $group->group_name . ' 已被删除。';
				}
				else
				{
					$output->status = 5;
					$output->title = '删除媒体分组失败';
					$output->content = '媒体分组 ' . $media->id . ' 未能删除。';
				}
			}
		}
		exit(json_encode($output));
	}

	/**
	 * 媒体列表
	 *
	 * @return	void
	 */
	public function action_list_media()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('list_media');
			if ($this->user->role_id >= $this->accessLevel)
			{
				// 分页
				$groupId = is_numeric($this->request->post('group')) ? (int) $this->request->post('group') : 1;
				$page = $this->request->post('page') > 0 ? (int) $this->request->post('page') : 1;
				$numPerPage = 10;
				$start = $numPerPage * ( $page - 1 );
				$where = '`group` = ' . $groupId;
				list($media, $total) = Model::factory('Media')->findByPage($where, '', $start, $numPerPage);
				$pagination = Functions::pageAdmin($page, $total, $numPerPage);
				$data = '';
				foreach ($media as $img)
				{
					$data .= '<tr>
						<td><img src="' . $this->homeUrl . 'assets/uploads/' . $img->slug . '/' . $img->thumb_name . '"/></td>
						<td>' . $img->group_name . '</td>
						<td>' . Functions::makeDate($img->created, 'Y-m-d H:i') . '</td>
						<td><input type="image" src="' . $this->homeUrl . 'assets_manage/images/icn_trash.png" title="删除" data-delete="' . $img->id . '"></td>
					</tr>';
				}
				$output->status = 1;
				$output->data = $data;
				$output->pagination = $pagination;
			}
			exit(json_encode($output));
		}
		$this->accessLevel = Admin::minimumLevel('list_media');
		if ($this->user->role_id >= $this->accessLevel)
		{
			$groupId = (int) $this->request->param('id');
			$group = Model::factory('media_group')->load($groupId);
			if ($group->id)
			{
				$this->template->body = View::factory('manage/media_list')
					->bind('group', $group);
			}
			else
			{
				$this->request->redirect('manage/list_media_groups');
			}
		}
	}

	/**
	 * 创建媒体
	 *
	 * @return	void
	 */
	public function action_create_media()
	{
		if ($this->request->method() == 'POST')
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('create_media');
			if ($this->user->role_id == $this->accessLevel)
			{
				$groupId = (int) $this->request->post('gid');
				$group = Model::factory('media_group')->load($groupId);
				if (!$group->id)
				{
					$output->status = 2;
					$output->title = '操作失败';
					$output->content = '请选择媒体分组。';
					exit(json_encode($output));
				}
				if (isset($_FILES['qqfile']) && $_FILES['qqfile']['name'])
				{
					$file = Validation::factory($_FILES);
					$file->rule('qqfile', 'Upload::not_empty');
					$file->rule('qqfile', 'Upload::valid');
					$file->rule('qqfile', 'Upload::image');
					$file->rule('qqfile', 'Upload::size', array(':value', '2M'));
					if (!$file->check())
					{
						$output->status = 3;
						$output->title = '无效的文件';
						$output->content = '这个文件是无效的。';
						exit(json_encode($output));
					}
					$extension = Functions::nameEnd($file['qqfile']['name'], '.');
					$fileName = date('ymdHis') . mt_rand(10, 99) . $extension;
					$directory = getcwd() . '/assets/uploads/' . $group->slug;
					$filePath = $directory . DIRECTORY_SEPARATOR . $fileName;
					if (!file_exists($filePath))
					{
						$filePath = Upload::save($file['qqfile'], $fileName, $directory);
					}
					else
					{
						$filePath = Upload::save($file['qqfile'], NULL, $directory);
					}
					if ($filePath === false)
					{
						$output->status = 4;
						$output->title = '文件上传失败';
						$output->content = '无法保存上传的文件！';
						exit(json_encode($output));
					}
					// 调整图片
					$image = Image::factory($filePath);
					$image->resize($group->rs_width, NULL);
					$image->crop($group->rs_width, $group->rs_height);
					$image->save();
					// 生成缩略图
					$image = Image::factory($filePath);
					$image->resize($group->tn_width, NULL);
					$image->crop($group->tn_width, $group->tn_height);
					$fileName = Functions::nameEnd($filePath, '/', 1);
					$extension = Functions::nameEnd($fileName, '.');
					$thumbPath = str_replace($extension, '-t' . $extension, $filePath);
					$thumbName = Functions::nameEnd($thumbPath, '/', 1);
					if ($image->save($thumbPath))
					{
						try
						{
							$create = new stdClass();
							$create->file_name = $fileName;
							$create->thumb_name = $thumbName;
							$create->group = $groupId;
							$create->created = time();
							$oMedia = Model::factory('Media');
							if ($mediaId = $oMedia->create($create))
							{
								$output->status = 1;
								$output->title = '上传成功';
								$output->thumbName = $thumbName;
								$output->success = true;
							}
						}
						catch (Validation_Exception $e)
						{
							$errors = $e->errors('models');
							foreach ($errors as $ev)
							{
								$output->status = 5;
								$output->title = '操作失败';
								$output->content = $ev;
								break;
							}
						}
					}
				}
				exit(json_encode($output));
			}
		}
		$this->accessLevel = Admin::minimumLevel('create_media');
		if ($this->user->role_id >= $this->accessLevel)
		{
			$groupId = (int) $this->request->param('id');
			$group = Model::factory('media_group')->load($groupId);
			if ($group->id)
			{
				$this->template->body = View::factory('manage/media_create')
					->bind('group', $group);
			}
			else
			{
				$this->request->redirect('manage/list_media_groups');
			}
		}
	}

	/**
	 * 删除媒体
	 *
	 * @return	void
	 */
	public function action_delete_media()
	{
		$output = new stdClass();
		$output->status = 0;
		$output->title = '操作失败';
		$output->content = '您没有足够的权限进行此项操作。';
		if ($this->request->is_ajax())
		{
			$this->accessLevel = Admin::minimumLevel('delete_media');
			if ($this->user->role_id >= $this->accessLevel)
			{
				if ($this->request->post('from') <> 'list_media')
				{
					$output->status = 2;
					$output->title = '操作失败';
					$output->content = '非法操作。';
					exit(json_encode($output));
				}
				$oMedia = Model::factory('Media');
				$mediaId = (int) $this->request->post('mid');
				$media = $oMedia->load($mediaId);
				if (!$media->id)
				{
					$output->status = 3;
					$output->title = '删除媒体失败';
					$output->content = '请求的媒体不存在。';
					exit(json_encode($output));
				}
				if ($media->node_id)
				{
					$output->status = 3;
					$output->title = '删除媒体失败';
					$output->content = '请求的媒体已被使用，不能删除。';
					exit(json_encode($output));
				}
				if ($oMedia->delete())
				{
					$group = Model::factory('media_group')->load($media->group);
					$directory = getcwd() . '/assets/uploads/' . $group->slug;
					$fileName = realpath($directory . DIRECTORY_SEPARATOR . $media->file_name);
					$thumbName = realpath($directory . DIRECTORY_SEPARATOR . $media->thumb_name);
					if (file_exists($fileName))
						unlink($fileName);
					if (file_exists($thumbName))
						unlink($thumbName);
					$output->status = 1;
					$output->title = '删除媒体成功';
					$output->content = '媒体 ' . $media->id . ' 已被删除。';
				}
				else
				{
					$output->status = 5;
					$output->title = '删除媒体失败';
					$output->content = '媒体 ' . $media->id . ' 未能删除。';
				}
			}
		}
		exit(json_encode($output));
	}

	/**
	 * 根据分组显示媒体文件
	 *
	 * @return	void
	 */
	public function action_load_media_by_group()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('list_media');
			if ($this->user->role_id >= $this->accessLevel)
			{
				$groupId = (int) $this->request->post('group');
				$group = Model::factory('media_group')->load($groupId);
				if ($group->id)
				{
					$media = Model::factory('Media')->findByGroup($group->id);
					$data = '';
					foreach ($media as $img)
					{
						$data .= '<img src="' . $this->homeUrl . 'assets/uploads/' . $group->slug . '/' . $img->thumb_name . '" class="picture" data-id="' . $img->id . '"/>';
					}
					$output->status = 1;
					$output->data = $data;
				}
				else
				{
					$output->status = 2;
					$output->title = '查询错误';
					$output->content = '该组图片不存在';
				}
			}
			exit(json_encode($output));
		}
	}

}
