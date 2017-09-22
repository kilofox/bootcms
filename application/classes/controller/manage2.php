<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 后台管理控制器。包括产品、订单、配送方式和联动菜单相关模块的管理。
 *
 * @package		BootCMS
 * @category	控制器
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Controller_Manage2 extends Controller_Template {

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
		!$this->user->id and $this->request->action('login');

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
	 * 显示订单
	 *
	 * @return	void
	 */
	public function action_list_orders()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('list_orders');
			if ($this->user->role_id >= $this->accessLevel)
			{
				// 分页
				$page = $this->request->post('page') > 0 ? (int) $this->request->post('page') : 1;
				$numPerPage = 20;
				$start = $numPerPage * ( $page - 1 );
				// 查询
				$sqlWhere = '';
				$status = $this->request->post('status') ? (int) $this->request->post('status') : 0;
				$sqlWhere.= " AND o.status = '$status'";
				if ($createdFrom = Functions::text($this->request->post('created_from')))
				{
					$createdFrom = explode('/', $createdFrom);
					if (count($createdFrom) == 3)
					{
						$timestamp = Functions::toTimestamp($createdFrom[0], $createdFrom[1], $createdFrom[2]);
						$sqlWhere.= " AND o.created > '$timestamp'";
					}
				}
				if ($createdTo = Functions::text($this->request->post('created_to')))
				{
					$createdTo = explode('/', $createdTo);
					if (count($createdTo) == 3)
					{
						$timestamp = Functions::toTimestamp($createdTo[0], $createdTo[1] + 1, $createdTo[2]);
						$sqlWhere.= " AND o.created < '$timestamp'";
					}
				}
				list($orders, $total) = Model::factory('Order')->findWithProducts($sqlWhere, $sqlOrderBy, $start, $numPerPage);
				$pagination = Functions::pageAdmin($page, $total, $numPerPage);
				$data = '';
				$orderId = '';
				foreach ($orders as $order)
				{
					if ($orderId == $order->id)
					{
						$order->id = $order->consignee = $order->phone = $order->address = $order->shipping = $order->amount = $order->message = $order->created = $order->status = $operation = '';
					}
					else
					{
						$order->created = Functions::makeDate($order->created, 'Y-m-d H:i:s');
						$operation = '-';
						switch ($order->status)
						{
							case '0':
								$order->status = '未付款';
								$operation = '<a title="标记为“已付款”" data-use="markpaid" data-val="' . $order->id . '">付款</a> <a title="标记为“已取消”" data-use="markcancelled" data-val="' . $order->id . '">取消</a>';
								break;
							case '1':
								$order->status = '已付款';
								$operation = '<a title="标记为“已发货”" data-use="markdelivered" data-val="' . $order->id . '">发货</a>';
								break;
							case '2':
								$order->status = '已发货';
								$operation = '<a title="标记为“已完成”" data-use="markcompleted" data-val="' . $order->id . '">完成</a>';
								break;
							case '3':
								$order->status = '已完成';
								break;
							case '4':
								$order->status = '已取消';
								break;
						}
					}
					$data .= '<tr>
						<td>' . $order->order_no . '</td>
						<td>' . $order->consignee . '</td>
						<td>' . $order->product . '</td>
						<td>' . $order->price . '</td>
						<td>' . $order->quantity . '</td>
						<td>' . $order->freight . '</td>
						<td>' . $order->amount . '</td>
						<td>' . $order->created . '</td>
						<td>' . $order->status . '</td>
						<td>' . $operation . '</td>
					</tr>';
					$orderId = $order->id;
				}
				$output->status = 1;
				$output->data = $data;
				$output->pagination = $pagination;
				exit(json_encode($output));
			}
		}
		$this->accessLevel = Admin::minimumLevel('list_orders');
		if ($this->user->role_id >= $this->accessLevel)
		{
			$this->template->body = View::factory('manage/order_list');
		}
	}

	/**
	 * 标记订单已发货
	 *
	 * @return	void
	 */
	public function action_mark_delivered()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '客户还没有对该订单付款。';
			$orderId = (int) $this->request->post('oid');
			$oOrder = Model::factory('Order');
			$order = $oOrder->load($orderId);
			if ($order->status == '1')
			{
				$order->status = 2;
				if ($oOrder->update())
				{
					$output->status = 1;
					$output->title = '操作成功';
					$output->content = '该订单已经标记为“已发货”状态。';
				}
			}
			exit(json_encode($output));
		}
	}

	/**
	 * 标记订单已完成
	 *
	 * @return	void
	 */
	public function action_mark_completed()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '商家还没有对该订单发货。';
			$orderId = (int) $this->request->post('oid');
			$oOrder = Model::factory('Order');
			$order = $oOrder->load($orderId);
			if ($order->status == '2')
			{
				$order->status = 3;
				if ($oOrder->update())
				{
					$output->status = 1;
					$output->title = '操作成功';
					$output->content = '该订单已经标记为“已完成”状态。';
				}
			}
			exit(json_encode($output));
		}
	}

	/**
	 * 标记订单已取消
	 *
	 * @return	void
	 */
	public function action_mark_cancelled()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '客户已付款，该订单不能取消。';
			$orderId = (int) $this->request->post('oid');
			$oOrder = Model::factory('Order');
			$order = $oOrder->load($orderId);
			if ($order->status == 0)
			{
				$order->status = 4;
				if ($oOrder->update())
				{
					$output->status = 1;
					$output->title = '操作成功';
					$output->content = '该订单已经标记为“已取消”状态。';
				}
			}
			exit(json_encode($output));
		}
	}

	/**
	 * 标记订单已付款
	 *
	 * @return	void
	 */
	public function action_mark_paid()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '该订单已付款，不能再次付款。';
			$orderId = (int) $this->request->post('oid');
			$oOrder = Model::factory('Order');
			$order = $oOrder->load($orderId);
			if ($order->status == '0')
			{
				$order->status = 1;
				if ($oOrder->update())
				{
					$output->status = 1;
					$output->title = '操作成功';
					$output->content = '该订单已经标记为“已付款”状态。';
				}
			}
			exit(json_encode($output));
		}
	}

	/**
	 * 创建产品分类
	 *
	 * @return	void
	 */
	public function action_create_product_category()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('create_product_category');
			if ($this->user->role_id >= $this->accessLevel)
			{
				$oCategory = Model::factory('product_category');
				$create = new stdClass();
				$create->name = Functions::text($this->request->post('category_name'));
				$create->list_order = 0;
				$category = $oCategory->loadByName($create->name);
				if ($category->id)
				{
					$output->status = 2;
					$output->title = '产品分类创建失败';
					$output->content = '产品分类名称已存在。';
					exit(json_encode($output));
				}
				try
				{
					if ($oCategory->create($create))
					{
						$output->status = 1;
						$output->title = '产品分类已创建';
						$output->content = '新的产品分类创建成功。';
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
		$this->accessLevel = Admin::minimumLevel('create_product_category');
		if ($this->user->role_id >= $this->accessLevel)
		{
			$cates = Model::factory('product_category')->findAll();
			$this->template->body = View::factory('manage/product_create_category')
				->bind('cates', $cates);
		}
	}

	/**
	 * 显示所有产品
	 *
	 * @return	void
	 */
	public function action_list_products()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('list_product_categories');
			if ($this->user->role_id >= $this->accessLevel)
			{
				// 分页
				$page = $this->request->post('page') > 0 ? (int) $this->request->post('page') : 1;
				$numPerPage = 20;
				$start = $numPerPage * ( $page - 1 );
				// 查询
				$sqlWhere = '';
				$sqlOrderBy = 'p.list_order';
				$oProduct = Model::factory('Product');
				list($products, $total) = $oProduct->findByPage($sqlWhere, $sqlOrderBy, $start, $numPerPage);
				$pagination = Functions::pageAdmin($page, $total, $numPerPage);
				$data = '';
				foreach ($products as $p)
				{
					$data .= '<tr>
						<td>' . $p->product_name . '</td>
						<td><a data-cate="' . $p->category . '">' . $p->name . '</a></td>
						<td><input type="text" data-sortp="' . $p->id . '" value="' . $p->list_order . '"/></td>
						<td>' . Functions::makeDate($p->created, 'Y-m-d H:i') . '</td>
						<td><input type="image" src="' . $this->homeUrl . 'assets_manage/images/icn_edit.png" title="编辑" data-edit="' . $p->id . '"/><input type="image" src="' . $this->homeUrl . 'assets_manage/images/icn_trash.png" title="删除" data-delete="' . $p->id . '"/></td>
					</tr>';
				}
				$output->status = 1;
				$output->data = $data;
				$output->pagination = $pagination;
			}
			exit(json_encode($output));
		}
		$this->accessLevel = Admin::minimumLevel('list_products');
		if ($this->user->role_id >= $this->accessLevel)
		{
			$this->template->body = View::factory('manage/product_list');
		}
	}

	/**
	 * 显示所有产品分类
	 *
	 * @return	void
	 */
	public function action_list_product_categories()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('list_product_categories');
			if ($this->user->role_id >= $this->accessLevel)
			{
				$oCategory = Model::factory('product_category');
				$cates = $oCategory->findByOrder();
				$data = '';
				foreach ($cates as $cate)
				{
					$data .= '<tr>
						<td>' . $cate->name . '</td>
						<td><input type="text" data-sort="' . $cate->id . '" value="' . $cate->list_order . '"/></td>
						<td><input type="image" src="' . $this->homeUrl . 'assets_manage/images/icn_edit.png" title="编辑" data-edit="' . $cate->id . '"/><input type="image" src="' . $this->homeUrl . 'assets_manage/images/icn_trash.png" title="删除" data-delete="' . $cate->id . '"/></td>
					</tr>';
				}
				$output->status = 1;
				$output->data = $data;
			}
			exit(json_encode($output));
		}
		$this->accessLevel = Admin::minimumLevel('list_product_categories');
		if ($this->user->role_id >= $this->accessLevel)
		{
			$this->template->body = View::factory('manage/product_category_list');
		}
	}

	/**
	 * 编辑产品分类
	 *
	 * @return	void
	 */
	public function action_edit_product_category()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('edit_product_category');
			if ($this->user->role_id >= $this->accessLevel)
			{
				$categoryId = (int) $this->request->post('cid');
				if ($categoryId <> Cookie::get('mid'))
				{
					$output->status = 2;
					$output->title = '操作失败';
					$output->content = '非法操作。';
					exit(json_encode($output));
				}
				$oCategory = Model::factory('product_category');
				$category = $oCategory->load($categoryId);
				if (!$category->id)
				{
					$output->status = 3;
					$output->title = '操作失败';
					$output->content = '请求的产品分类不存在。';
					exit(json_encode($output));
				}
				try
				{
					$category->name = Functions::text($this->request->post('cate_name'));
					$category->list_order = (int) $this->request->post('cate_order');
					if ($oCategory->update())
					{
						$output->status = 1;
						$output->title = '分类已更新';
						$output->content = '产品分类已经更新完毕。';
					}
					else
					{
						$output->status = 1;
						$output->title = '分类未更新';
						$output->content = '产品分类没有更新。';
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
		$this->accessLevel = Admin::minimumLevel('edit_product_category');
		if ($this->user->role_id >= $this->accessLevel)
		{
			$oCategory = Model::factory('product_category');
			$categoryId = (int) $this->request->param('id');
			$category = $oCategory->load($categoryId);
			Cookie::set('mid', $category->id);
			$this->template->body = View::factory('manage/product_category_edit')
				->bind('node', $category);
		}
	}

	/**
	 * 删除产品分类
	 *
	 * @return	void
	 */
	public function action_delete_product_category()
	{
		$output = new stdClass();
		$output->status = 0;
		$output->title = '操作失败';
		$output->content = '您没有足够的权限进行此项操作。';
		if ($this->request->is_ajax())
		{
			$this->accessLevel = Admin::minimumLevel('delete_product_category');
			if ($this->user->role_id >= $this->accessLevel)
			{
				if ($this->request->post('from') <> 'list_product_categories')
				{
					$output->status = 2;
					$output->title = '操作失败';
					$output->content = '非法操作。';
					exit(json_encode($output));
				}
				$oCategory = Model::factory('product_category');
				$categoryId = (int) $this->request->post('cid');
				$category = $oCategory->load($categoryId);
				if (!$category->id)
				{
					$output->status = 3;
					$output->title = '操作失败';
					$output->content = '请求的产品分类不存在。';
					exit(json_encode($output));
				}
				if ($oCategory->delete())
				{
					$log = new stdClass();
					$log->type = 2;
					$log->user_id = $this->user->id;
					$log->node_id = $category->id;
					$log->content = '删除配送方式【' . $category->name . '】';
					$log->ip = $_SERVER['REMOTE_ADDR'];
					$log->backup = serialize($category);
					$log->created = time();
					$log->status = 0;
					Model::factory('Log')->create($log);
					$output->status = 1;
					$output->title = '删除产品分类成功';
					$output->content = '产品分类 ' . $category->name . ' 已被删除。';
				}
				else
				{
					$output->status = 5;
					$output->title = '删除产品分类失败';
					$output->content = '产品分类 ' . $category->name . ' 未能删除。';
				}
			}
		}
		exit(json_encode($output));
	}

	/**
	 * 对产品分类排序
	 *
	 * @return	void
	 */
	public function action_sort_product_categories()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('sort_product_categories');
			if ($this->user->role_id >= $this->accessLevel)
			{
				// 对分类排序
				$cateSort = array_filter(explode(',', Functions::text($this->request->post('cate_sort'))));
				if ($cateSort)
				{
					Model::factory('product_category')->sortCategories($cateSort);
				}
				$output->status = 1;
				$output->title = '操作成功';
				$output->content = '产品分类排序已完成。';
			}
			exit(json_encode($output));
		}
	}

	/**
	 * 对产品排序
	 *
	 * @return	void
	 */
	public function action_sort_products()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('sort_products');
			if ($this->user->role_id >= $this->accessLevel)
			{
				$prodSort = array_filter(explode(',', Functions::text($this->request->post('prod_sort'))));
				if ($prodSort)
				{
					Model::factory('Product')->sortProducts($prodSort);
				}
				$output->status = 1;
				$output->title = '操作成功';
				$output->content = '产品排序已完成。';
			}
			exit(json_encode($output));
		}
	}

	/**
	 * 创建一个新的产品
	 *
	 * @return	void
	 */
	public function action_create_product()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('create_shipping');
			if ($this->user->role_id >= $this->accessLevel)
			{
				for ($i = 1; $i < 11; $i++)
				{
					${'item' . $i} = $this->request->post('item' . $i . '_name') && $this->request->post('item' . $i . '_value') ? array(Functions::text($this->request->post('item' . $i . '_name')), Functions::text($this->request->post('item' . $i . '_value'))) : array();
				}
				$pictures = explode(',', Functions::text($this->request->post('pictures')));
				$arrPics = array();
				foreach ($pictures as $pic)
				{
					if (is_numeric($pic))
						$arrPics[] = $pic;
				}
				$oProduct = Model::factory('Product');
				$create = new stdClass();
				$create->category = Functions::text($this->request->post('category'));
				$create->product_name = Functions::text($this->request->post('product_name'));
				$create->introduce = Functions::text($this->request->post('introduce'));
				$create->item1 = addslashes(serialize($item1));
				$create->item2 = addslashes(serialize($item2));
				$create->item3 = addslashes(serialize($item3));
				$create->item4 = addslashes(serialize($item4));
				$create->item5 = addslashes(serialize($item5));
				$create->item6 = addslashes(serialize($item6));
				$create->item7 = addslashes(serialize($item7));
				$create->item8 = addslashes(serialize($item8));
				$create->item9 = addslashes(serialize($item9));
				$create->item10 = addslashes(serialize($item10));
				$create->commodity_price = Functions::text($this->request->post('commodity_price'));
				$create->promotion_price = Functions::text($this->request->post('promotion_price'));
				$create->promote = Functions::text($this->request->post('promote'));
				$create->pictures = serialize($arrPics);
				$create->created = time();
				$product = $oProduct->loadByName($create->product_name);
				if ($product->id)
				{
					$output->status = 2;
					$output->title = '用户创建失败';
					$output->content = '产品名称已存在。';
					exit(json_encode($output));
				}
				try
				{
					if ($oProduct->create($create))
					{
						$output->status = 1;
						$output->title = '产品已创建';
						$output->content = '新的产品创建成功。';
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
		$this->accessLevel = Admin::minimumLevel('create_product');
		if ($this->user->role_id >= $this->accessLevel)
		{
			$cates = Model::factory('product_category')->findAll();
			$shippings = Model::factory('Shipping')->findAll();
			$mediaGroups = Model::factory('media_group')->findAll();
			$this->template->body = View::factory('manage/product_create')
				->bind('cates', $cates)
				->bind('mediaGroups', $mediaGroups)
				->bind('shippings', $shippings);
		}
	}

	/**
	 * 编辑配送方式信息
	 *
	 * @return	void
	 */
	public function action_edit_product()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('edit_product');
			if ($this->user->role_id >= $this->accessLevel)
			{
				$productId = (int) $this->request->post('pid');
				$oProduct = Model::factory('Product');
				$product = $oProduct->load($productId);
				if (!$product->id)
				{
					$output->status = 3;
					$output->title = '操作失败';
					$output->content = '请求的产品不存在。';
					exit(json_encode($output));
				}
				try
				{
					for ($i = 1; $i < 11; $i++)
					{
						${'item' . $i} = $this->request->post('item' . $i . '_name') && $this->request->post('item' . $i . '_value') ? array(Functions::text($this->request->post('item' . $i . '_name')), Functions::text($this->request->post('item' . $i . '_value'))) : array();
					}
					$pictures = explode(',', Functions::text($this->request->post('pictures')));
					$arrPics = array();
					foreach ($pictures as $pic)
					{
						if (is_numeric($pic))
							$arrPics[] = $pic;
					}
					$product->category = (int) $this->request->post('category');
					$product->product_name = Functions::text($this->request->post('product_name'));
					$product->introduce = Functions::text($this->request->post('introduce'));
					$product->item1 = addslashes(serialize($item1));
					$product->item2 = addslashes(serialize($item2));
					$product->item3 = addslashes(serialize($item3));
					$product->item4 = addslashes(serialize($item4));
					$product->item5 = addslashes(serialize($item5));
					$product->item6 = addslashes(serialize($item6));
					$product->item7 = addslashes(serialize($item7));
					$product->item8 = addslashes(serialize($item8));
					$product->item9 = addslashes(serialize($item9));
					$product->item10 = addslashes(serialize($item10));
					$product->commodity_price = Functions::text($this->request->post('commodity_price'));
					$product->promotion_price = Functions::text($this->request->post('promotion_price'));
					$product->promote = Functions::text($this->request->post('promote'));
					$product->pictures = serialize($arrPics);
					if ($oProduct->update())
					{
						$output->status = 1;
						$output->title = '产品已更新';
						$output->content = '产品已经更新完毕。';
					}
					else
					{
						$output->status = 5;
						$output->title = '产品未更新';
						$output->content = '产品信息没有更新。';
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
		$this->accessLevel = Admin::minimumLevel('edit_product');
		if ($this->user->role_id >= $this->accessLevel)
		{
			$oProduct = Model::factory('Product');
			$productId = (int) $this->request->param('id');
			$product = $oProduct->load($productId);
			for ($i = 1; $i < 11; $i++)
			{
				$item = 'item' . $i;
				$product->$item = unserialize($product->$item);
			}
			// 分类列表
			$categories = Model::factory('product_category')->findAll();
			// 配送方式列表
			$shippings = Model::factory('Shipping')->findAll();
			$product->pictures = unserialize($product->pictures);
			$media = Model::factory('Media')->findByIds($product->pictures);
			$mediaGroups = Model::factory('media_group')->findAll();
			$groups = array();
			foreach ($mediaGroups as $node)
			{
				$groups[$node->id] = $node;
			}
			Cookie::set('mid', $product->id);
			$this->template->body = View::factory('manage/product_edit')
				->bind('node', $product)
				->bind('cates', $categories)
				->bind('media', $media)
				->bind('mediaGroups', $groups)
				->bind('shippings', $shippings);
		}
	}

	/**
	 * 删除产品
	 *
	 * @return	void
	 */
	public function action_delete_product()
	{
		$output = new stdClass();
		$output->status = 0;
		$output->title = '操作失败';
		$output->content = '您没有足够的权限进行此项操作。';
		if ($this->request->is_ajax())
		{
			$this->accessLevel = Admin::minimumLevel('delete_product');
			if ($this->user->role_id >= $this->accessLevel)
			{
				if ($this->request->post('from') <> 'list_product_categories')
				{
					$output->status = 2;
					$output->title = '操作失败';
					$output->content = '非法操作。';
					exit(json_encode($output));
				}
				$oProduct = Model::factory('Product');
				$productId = (int) $this->request->post('pid');
				$product = $oProduct->load($productId);
				if (!$product->id)
				{
					$output->status = 3;
					$output->title = '操作失败';
					$output->content = '请求的产品不存在。';
					exit(json_encode($output));
				}
				if ($oProduct->delete())
				{
					$log = new stdClass();
					$log->type = 2;
					$log->user_id = $this->user->id;
					$log->node_id = $product->id;
					$log->content = '删除产品【' . $product->product_name . '】';
					$log->ip = $_SERVER['REMOTE_ADDR'];
					$log->backup = serialize($product);
					$log->created = time();
					$log->status = 0;
					Model::factory('Log')->create($log);
					$output->status = 1;
					$output->title = '删除产品成功';
					$output->content = '产品 ' . $product->product_name . ' 已被删除。';
				}
				else
				{
					$output->status = 5;
					$output->title = '删除产品失败';
					$output->content = '产品 ' . $product->product_name . ' 未能删除。';
				}
			}
		}
		exit(json_encode($output));
	}

	/**
	 * 显示所有配送方式
	 *
	 * @return	void
	 */
	public function action_list_shippings()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('list_shippings');
			if ($this->user->role_id >= $this->accessLevel)
			{
				$shippings = Model::factory('Shipping')->findAll();
				$data = '';
				foreach ($shippings as $ex)
				{
					$ex->support_cod = $ex->support_cod == 1 ? '是' : '否';
					$ex->status = $ex->status == 0 ? '开启' : '关闭';
					$data .= '<tr>
						<td>' . $ex->id . '</td>
						<td>' . $ex->shipping_name . '</td>
						<td>' . $ex->insurance . '</td>
						<td>' . $ex->support_cod . '</td>
						<td>' . $ex->list_order . '</td>
						<td>' . $ex->status . '</td>
						<td><input type="image" src="' . $this->homeUrl . 'assets_manage/images/icn_edit.png" title="编辑" data-use="edit" data-val="' . $ex->id . '"/><input type="image" src="' . $this->homeUrl . 'assets_manage/images/icn_trash.png" title="删除" data-use="delete" data-val="' . $ex->id . '" /></td>
					</tr>';
				}
				$output->status = 1;
				$output->data = $data;
			}
			exit(json_encode($output));
		}
		$this->accessLevel = Admin::minimumLevel('list_shippings');
		if ($this->user->role_id >= $this->accessLevel)
		{
			$this->template->body = View::factory('manage/shipping_list');
		}
	}

	/**
	 * 创建一个新的配送方式
	 *
	 * @return	void
	 */
	public function action_create_shipping()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('create_shipping');
			if ($this->user->role_id >= $this->accessLevel)
			{
				$oShipping = Model::factory('Shipping');
				$create = new stdClass();
				$create->shipping_name = Functions::text($this->request->post('shipping_name'));
				$create->shipping_desc = Functions::text($this->request->post('shipping_desc'));
				$create->base_weight = (int) $this->request->post('base_weight');
				$create->step_weight = (int) $this->request->post('step_weight');
				$create->base_price = (float) $this->request->post('base_price');
				$create->step_price = (float) $this->request->post('step_price');
				$create->price_type = (int) $this->request->post('price_type');
				$create->insurance = (float) $this->request->post('insurance');
				$create->support_cod = $this->request->post('surrpot_cod') ? 1 : 0;
				$create->list_order = (int) $this->request->post('list_order');
				$create->status = (int) $this->request->post('status');
				$shipping = $oShipping->loadByName($create->name);
				if ($shipping->id)
				{
					$output->status = 2;
					$output->title = '用户创建失败';
					$output->content = '配送方式名称已存在。';
					exit(json_encode($output));
				}
				try
				{
					if ($oShipping->create($create))
					{
						$output->status = 1;
						$output->title = '配送方式已创建';
						$output->content = '新的配送方式创建成功。';
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
		$this->accessLevel = Admin::minimumLevel('create_shipping');
		if ($this->user->role_id >= $this->accessLevel)
		{
			$arrWeights = array(
				array('0', '请选择'),
				array('500', '500克'),
				array('1000', '1千克'),
				array('1500', '1.5千克'),
				array('2000', '2千克'),
				array('5000', '5千克'),
				array('10000', '10千克'),
				array('20000', '20千克'),
				array('50000', '50千克')
			);
			$this->template->body = View::factory('manage/shipping_create')
				->bind('arrWeights', $arrWeights);
		}
	}

	/**
	 * 编辑配送方式信息
	 *
	 * @return	void
	 */
	public function action_edit_shipping()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('edit_shipping');
			if ($this->user->role_id >= $this->accessLevel)
			{
				$shippingId = (int) $this->request->post('fid');
				$oShipping = Model::factory('Shipping');
				$shipping = $oShipping->load($shippingId);
				if (!$shipping->id)
				{
					$output->status = 3;
					$output->title = '操作失败';
					$output->content = '请求的配送方式不存在。';
					exit(json_encode($output));
				}
				try
				{
					if ($this->request->post('price_type') == 1)
					{
						$areaIds = (array) $this->request->post('area_id');
						$areaNames = (array) $this->request->post('area_name');
						$areaBasePrices = (array) $this->request->post('base_price');
						$areaStepPrices = (array) $this->request->post('step_price');
						$areas = array();
						foreach ($areaIds as $k => $id)
						{
							if ($id)
							{
								$areaObj = new stdClass();
								$areaObj->area_id = $id;
								$areaObj->area_name = $areaNames[$k];
								$areaObj->base_price = $areaBasePrices[$k];
								$areaObj->step_price = $areaStepPrices[$k];
								$areas[$k] = $areaObj;
							}
						}
						$shipping->areas = serialize($areas);
					}
					else
					{
						$shipping->base_price = (float) $this->request->post('base_price');
						$shipping->step_price = (float) $this->request->post('step_price');
					}
					$shipping->shipping_name = Functions::text($this->request->post('shipping_name'));
					$shipping->shipping_desc = Functions::text($this->request->post('shipping_desc'));
					$shipping->base_weight = (int) $this->request->post('base_weight');
					$shipping->step_weight = (int) $this->request->post('step_weight');
					$shipping->price_type = (int) $this->request->post('price_type');
					$shipping->insurance = (float) $this->request->post('insurance');
					$shipping->support_cod = $this->request->post('support_cod') ? 1 : 0;
					$shipping->list_order = (int) $this->request->post('list_order');
					$shipping->status = (int) $this->request->post('status');
					if ($oShipping->update())
					{
						$output->status = 1;
						$output->title = '配送方式已更新';
						$output->content = '配送方式信息已经更新完毕。';
					}
					else
					{
						$output->status = 4;
						$output->title = '配送方式未更新';
						$output->content = '配送方式信息没有更新。';
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
			exit(json_encode($output));
		}
		$this->accessLevel = Admin::minimumLevel('edit_shipping');
		if ($this->user->role_id >= $this->accessLevel)
		{
			$oShipping = Model::factory('Shipping');
			$shippingId = (int) $this->request->param('id');
			$shipping = $oShipping->load($shippingId);
			$arrWeights = array(
				array('500', '500克'),
				array('1000', '1千克'),
				array('1500', '1.5千克'),
				array('2000', '2千克'),
				array('5000', '5千克'),
				array('10000', '10千克'),
				array('20000', '20千克'),
				array('50000', '50千克')
			);
			$this->template->body = View::factory('manage/shipping_edit')
				->bind('node', $shipping)
				->bind('arrWeights', $arrWeights);
		}
	}

	/**
	 * 删除配送方式
	 *
	 * @return	void
	 */
	public function action_delete_shipping()
	{
		$output = new stdClass();
		$output->status = 0;
		$output->title = '操作失败';
		$output->content = '您没有足够的权限进行此项操作。';
		if ($this->request->is_ajax())
		{
			$this->accessLevel = Admin::minimumLevel('delete_shipping');
			if ($this->user->role_id >= $this->accessLevel)
			{
				if ($this->request->post('from') <> 'list_shippings')
				{
					$output->status = 2;
					$output->title = '操作失败';
					$output->content = '非法操作。';
					exit(json_encode($output));
				}
				$oShipping = Model::factory('Shipping');
				$shippingId = (int) $this->request->post('eid');
				$shipping = $oShipping->load($shippingId);
				if (!$shipping->id)
				{
					$output->status = 3;
					$output->title = '操作失败';
					$output->content = '请求的配送方式不存在。';
					exit(json_encode($output));
				}
				if ($shipping->id < 4)
				{
					$output->status = 3;
					$output->title = '删除配送方式失败';
					$output->content = '系统自带配送方式不可删除。';
					exit(json_encode($output));
				}
				if ($oShipping->delete())
				{
					$log = new stdClass();
					$log->type = 2;
					$log->user_id = $this->user->id;
					$log->node_id = $shippingId;
					$log->content = '删除配送方式【' . $shipping->shipping_name . '】';
					$log->ip = $_SERVER['REMOTE_ADDR'];
					$log->backup = serialize($shipping);
					$log->created = time();
					$log->status = 0;
					Model::factory('Log')->create($log);
					$output->status = 1;
					$output->title = '删除配送方式成功';
					$output->content = '配送方式 ' . $shipping->shipping_name . ' 已被删除。';
				}
				else
				{
					$output->status = 5;
					$output->title = '删除配送方式失败';
					$output->content = '配送方式 ' . $shipping->shipping_name . ' 未能删除。';
				}
			}
		}
		exit(json_encode($output));
	}

	/**
	 * 显示联动菜单
	 *
	 * @return	void
	 */
	public function action_list_linkages()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('list_linkages');
			if ($this->user->role_id >= $this->accessLevel)
			{
				$nodeId = (int) $this->request->post('node_id');
				$linkages = Model::factory('Linkage')->findByParent($nodeId);
				$data = '';
				foreach ($linkages as $node)
				{
					$subView = $node->has_child == 1 ? '<input type="image" src="' . $this->homeUrl . 'assets_manage/images/icn_categories.png" title="管理子菜单" data-list="' . $node->id . '" />' : '';
					$data .= '<tr>
						<td>' . $node->id . '</td>
						<td>' . $node->name . '</td>
						<td>' . $subView . '<input type="image" src="' . $this->homeUrl . 'assets_manage/images/icn_edit.png" title="编辑" data-edit="' . $node->id . '"/><input type="image" src="' . $this->homeUrl . 'assets_manage/images/icn_trash.png" title="删除" data-delete="' . $node->id . '" /></td>
					</tr>';
				}
				$output->status = 1;
				$output->data = $data;
			}
			exit(json_encode($output));
		}
		$this->accessLevel = Admin::minimumLevel('list_linkages');
		if ($this->user->role_id >= $this->accessLevel)
		{
			$this->template->body = View::factory('manage/linkage_list');
		}
	}

	/**
	 * 显示联动菜单
	 *
	 * @return	void
	 */
	public function action_get_linkages()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('list_linkages');
			if ($this->user->role_id >= $this->accessLevel)
			{
				$nodeId = (int) $this->request->post('node_id');
				$linkages = Model::factory('Linkage')->findByParent($nodeId);
				$data = '';
				$data = array();
				foreach ($linkages as $node)
				{
					$selected = explode(',', Functions::text($this->request->post('selected')));
					$checked = in_array($node->id, $selected) ? ' checked="checked"' : '';
					$node->checked = in_array($node->id, $selected);
					$button = $node->has_child == '1' ? '<span><input type="image" data-nid="' . $node->id . '" src="' . $this->homeUrl . 'assets_manage/images/icn_edit.png" title="展开" />' : '<span>';
					$data .= '<li>' . $button . '<input type="checkbox" data-area="' . $node->id . '"' . $checked . ' />' . $node->name . '</span></li>';
				}
				$output->status = 1;
				$output->data = $linkages;
			}
		}
		exit(json_encode($output));
	}

}
