<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 后台管理控制器。包括论坛相关模块的管理。
 *
 * @package	BootCMS
 * @category	控制器
 * @author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Controller_Manage3 extends Controller_Template {

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
	 * 一般设置
	 *
	 * @return	void
	 */
	public function action_config()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('forum_edit_config');
			if ($this->user->role_id >= $this->accessLevel)
			{
				$oConfig = Model::factory('forum_config');
				$config = $oConfig->findAll();
				foreach ($config as $k => $node)
				{
					if ($node <> $this->request->post($k))
						$oConfig->setByName($k, Functions::text($this->request->post($k)));
				}
				// 删除缓存
				$cache = Cache::instance();
				$cache->delete('forum_config');
				$output->status = 1;
				$output->title = '配置已更新';
				$output->content = '配置已经更新成功。';
			}
			exit(json_encode($output));
		}

		$this->accessLevel = Admin::minimumLevel('forum_edit_config');
		if ($this->user->role_id >= $this->accessLevel)
		{
			$oConfig = Model::factory('forum_config');
			$config = $oConfig->findAll();
			$this->template->body = View::factory('manage/forum_config')
				->bind('config', $config);
		}
	}

	/**
	 * 显示订单
	 *
	 * @return	void
	 */
	public function action_list_categories()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('list_forum_categories');
			if ($this->user->role_id >= $this->accessLevel)
			{
				$categories = Model::factory('forum_category')->findAll();
				$data = '';
				foreach ($categories as $node)
				{
					$data .= '<tr data-cid="' . $node->id . '">
						<td>' . $node->name . '</td>
						<td><input type="text" name="sort" value="' . $node->sort_id . '" /></td>
						<td><a title="编辑" data-use="edit">编辑</a> <a title="删除" data-use="del">删除</a></td>
					</tr>';
				}
				$output->status = 1;
				$output->data = $data;
				exit(json_encode($output));
			}
		}

		$this->accessLevel = Admin::minimumLevel('list_forum_categories');
		if ($this->user->role_id >= $this->accessLevel)
		{
			$this->template->body = View::factory('manage/forum_category_list');
		}
	}

	/**
	 * 创建新分类
	 *
	 * @return	void
	 */
	public function action_create_category()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('create_forum_category');
			if ($this->user->role_id >= $this->accessLevel)
			{
				$oCategory = Model::factory('forum_category');
				$create = new stdClass();
				$create->name = Forumfunctions::unhtml(Functions::text($this->request->post('category_name')));
				$create->sort_id = 0;
				$category = $oCategory->loadByName($create->name);
				if ($category->id)
				{
					$output->status = 2;
					$output->title = '分类创建失败';
					$output->content = '分类名称已存在。';
					exit(json_encode($output));
				}
				try
				{
					if ($oCategory->create($create))
					{
						$output->status = 1;
						$output->title = '分类已创建';
						$output->content = '新的分类创建成功。';
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
	 * 编辑分类
	 *
	 * @return	void
	 */
	public function action_edit_category()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('edit_forum_category');
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
				$oCategory = Model::factory('forum_category');
				$category = $oCategory->load($categoryId);
				if (!$category->id)
				{
					$output->status = 3;
					$output->title = '操作失败';
					$output->content = '请求的分类不存在。';
					exit(json_encode($output));
				}
				try
				{
					$category->name = Forumfunctions::unhtml(Functions::text($this->request->post('cate_name')));
					if ($oCategory->update())
					{
						$output->status = 1;
						$output->title = '分类已更新';
						$output->content = '分类已经更新完毕。';
					}
					else
					{
						$output->status = 1;
						$output->title = '分类未更新';
						$output->content = '分类没有更新。';
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

		$this->accessLevel = Admin::minimumLevel('edit_forum_category');
		if ($this->user->role_id >= $this->accessLevel)
		{
			$oCategory = Model::factory('forum_category');
			$categoryId = (int) $this->request->param('id');
			$category = $oCategory->load($categoryId);
			Cookie::set('mid', $category->id);
			$this->template->body = View::factory('manage/forum_category_edit')
				->bind('node', $category);
		}
	}

	/**
	 * 删除分类
	 *
	 * @return	void
	 */
	public function action_delete_category()
	{
		$output = new stdClass();
		$output->status = 0;
		$output->title = '操作失败';
		$output->content = '您没有足够的权限进行此项操作。';
		if ($this->request->is_ajax())
		{
			$this->accessLevel = Admin::minimumLevel('delete_forum_category');
			if ($this->user->role_id >= $this->accessLevel)
			{
				if ($this->request->post('from') <> 'list_categories')
				{
					$output->status = 2;
					$output->title = '操作失败';
					$output->content = '非法操作。';
					exit(json_encode($output));
				}
				$oCategory = Model::factory('forum_category');
				$categoryId = (int) $this->request->post('cid');
				$category = $oCategory->load($categoryId);
				if (!$category->id)
				{
					$output->status = 3;
					$output->title = '操作失败';
					$output->content = '请求的分类不存在。';
					exit(json_encode($output));
				}
				if ($oCategory->delete())
				{
					$log = new stdClass();
					$log->type = 2;
					$log->user_id = $this->user->id;
					$log->node_id = $category->id;
					$log->content = '删除论坛分类【' . $category->name . '】';
					$log->ip = $_SERVER['REMOTE_ADDR'];
					$log->backup = serialize($category);
					$log->created = time();
					$log->status = 0;
					Model::factory('Log')->create($log);
					$output->status = 1;
					$output->title = '删除分类成功';
					$output->content = '分类 ' . $category->name . ' 已被删除。';
				}
				else
				{
					$output->status = 5;
					$output->title = '删除分类失败';
					$output->content = '分类 ' . $category->name . ' 未能删除。';
				}
			}
		}
		exit(json_encode($output));
	}

	/**
	 * 对分类排序
	 *
	 * @return	void
	 */
	public function action_sort_categories()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('sort_forum_categories');
			if ($this->user->role_id >= $this->accessLevel)
			{
				// 对分类排序
				$cateSort = explode(',', Functions::text($this->request->post('cate_sort')));
				Model::factory('forum_category')->sortCategories($cateSort);
				$output->status = 1;
				$output->title = '操作成功';
				$output->content = '分类排序已完成。';
			}
			exit(json_encode($output));
		}
	}

	/**
	 * 显示订单
	 *
	 * @return	void
	 */
	public function action_list_forums()
	{
		$this->accessLevel = Admin::minimumLevel('list_forum_forums');
		if ($this->user->role_id >= $this->accessLevel)
		{
			$forums = Model::factory('forum_forum')->findAllForumsWithCategory();
			$this->template->body = View::factory('manage/forum_forum_list')
				->bind('forums', $forums);
		}
	}

	/**
	 * 创建新版块
	 *
	 * @return	void
	 */
	public function action_create_forum()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('create_forum_forum');
			if ($this->user->role_id >= $this->accessLevel)
			{
				$oForum = Model::factory('forum_forum');
				$create = new stdClass();
				$create->name = Forumfunctions::unhtml(Functions::text($this->request->post('name')));
				$create->cat_id = (int) $this->request->post('cat_id');
				$create->descr = Forumfunctions::unhtml(Functions::text($this->request->post('descr')));
				$create->status = (int) $this->request->post('status');
				$create->moderators = Functions::text($this->request->post('moderators'));
				$create->hide_mods_list = $this->request->post('hide_mods_list') == '1' ? 1 : 0;
				$create->auth = intval($this->request->post('auth0'))
					. intval($this->request->post('auth1'))
					. intval($this->request->post('auth2'))
					. intval($this->request->post('auth3'))
					. intval($this->request->post('auth4'))
					. intval($this->request->post('auth5'))
					. intval($this->request->post('auth6'))
					. intval($this->request->post('auth7'))
					. intval($this->request->post('auth8'))
					. intval($this->request->post('auth9'));
				$forum = $oForum->loadByName($create->name);
				if ($forum)
				{
					$output->status = 2;
					$output->title = '版块创建失败';
					$output->content = '版块名称已存在。';
					exit(json_encode($output));
				}
				try
				{
					if ($oForum->create($create))
					{
						$output->status = 1;
						$output->title = '版块已创建';
						$output->content = '新的版块创建成功。';
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

		$this->accessLevel = Admin::minimumLevel('forum_create_forum');
		if ($this->user->role_id >= $this->accessLevel)
		{
			$categories = Model::factory('forum_category')->findAll();
			$userLevels = array(
				'0' => '游客',
				'1' => '会员',
				'2' => '版主',
				'3' => '管理员'
			);
			$this->template->body = View::factory('manage/forum_create_forum')
				->bind('userLevels', $userLevels)
				->bind('categories', $categories);
		}
	}

	/**
	 * 编辑版块
	 *
	 * @return	void
	 */
	public function action_edit_forum()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('edit_forum_forum');
			if ($this->user->role_id >= $this->accessLevel)
			{
				$forumId = (int) $this->request->post('cid');
				if ($forumId <> Cookie::get('mid'))
				{
					$output->status = 2;
					$output->title = '操作失败';
					$output->content = '非法操作。';
					exit(json_encode($output));
				}
				$oForum = Model::factory('forum_forum');
				$forum = $oForum->load($forumId);
				if (!$forum->id)
				{
					$output->status = 3;
					$output->title = '操作失败';
					$output->content = '请求的版块不存在。';
					exit(json_encode($output));
				}
				try
				{
					if ($moderators = Functions::text($this->request->post('moderators')))
					{
						$moderators = explode(',', $moderators);
						if (count($moderators) > 10)
						{
							$output->status = 3;
							$output->title = '操作失败';
							$output->content = '一个版块最多只能设 10 个版主。';
							exit(json_encode($output));
						}
						$moderatorIds = Model::factory('forum_member')->findIdsByNames($moderators);
					}
					$forum->name = Forumfunctions::unhtml(Functions::text($this->request->post('name')));
					$forum->cat_id = (int) $this->request->post('cat_id');
					$forum->descr = Forumfunctions::unhtml(Functions::text($this->request->post('descr')));
					$forum->status = (int) $this->request->post('status');
					$forum->moderators = Functions::text($this->request->post('moderators'));
					$forum->hide_mods_list = (int) $this->request->post('hide_mods_list');
					$forum->auth = intval($this->request->post('auth0'))
						. intval($this->request->post('auth1'))
						. intval($this->request->post('auth2'))
						. intval($this->request->post('auth3'))
						. intval($this->request->post('auth4'))
						. intval($this->request->post('auth5'))
						. intval($this->request->post('auth6'))
						. intval($this->request->post('auth7'))
						. intval($this->request->post('auth8'))
						. intval($this->request->post('auth9'));
					if ($oForum->update())
					{
						$output->status = 1;
						$output->title = '版块已更新';
						$output->content = '版块已经更新完毕。';
					}
					else
					{
						$output->status = 1;
						$output->title = '版块未更新';
						$output->content = '版块没有更新。';
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
		$this->accessLevel = Admin::minimumLevel('edit_forum_forum');
		if ($this->user->role_id >= $this->accessLevel)
		{
			$oForum = Model::factory('forum_forum');
			$forumId = (int) $this->request->param('id');
			$forum = $oForum->load($forumId);
			$categories = Model::factory('forum_category')->findAll();
			$userLevels = array(
				'0' => '游客',
				'1' => '会员',
				'2' => '版主',
				'3' => '管理员'
			);
			foreach (range(0, 9) as $authId)
			{
				$forum->{'auth' . $authId} = $forum->auth[$authId];
			}
			Cookie::set('mid', $forum->id);
			$this->template->body = View::factory('manage/forum_forum_edit')
				->bind('node', $forum)
				->bind('userLevels', $userLevels)
				->bind('categories', $categories);
		}
	}

	/**
	 * 删除版块
	 *
	 * @return	void
	 */
	public function action_delete_forum()
	{
		$output = new stdClass();
		$output->status = 0;
		$output->title = '操作失败';
		$output->content = '您没有足够的权限进行此项操作。';
		if ($this->request->is_ajax())
		{
			$this->accessLevel = Admin::minimumLevel('delete_forum_forum');
			if ($this->user->role_id >= $this->accessLevel)
			{
				if ($this->request->post('from') <> 'list_forums')
				{
					$output->status = 2;
					$output->title = '操作失败';
					$output->content = '非法操作。';
					exit(json_encode($output));
				}
				$oForum = Model::factory('forum_forum');
				$forumId = (int) $this->request->post('cid');
				$forum = $oForum->load($forumId);
				if (!$forum->id)
				{
					$output->status = 3;
					$output->title = '操作失败';
					$output->content = '请求的版块不存在。';
					exit(json_encode($output));
				}
				if ($oForum->delete())
				{
					$log = new stdClass();
					$log->type = 2;
					$log->user_id = $this->user->id;
					$log->node_id = $forum->id;
					$log->content = '删除论坛版块【' . $forum->name . '】';
					$log->ip = $_SERVER['REMOTE_ADDR'];
					$log->backup = serialize($forum);
					$log->created = time();
					$log->status = 0;
					Model::factory('Log')->create($log);
					$output->status = 1;
					$output->title = '删除版块成功';
					$output->content = '版块 ' . $forum->name . ' 已被删除。';
				}
				else
				{
					$output->status = 5;
					$output->title = '删除版块失败';
					$output->content = '版块 ' . $forum->name . ' 未能删除。';
				}
			}
		}
		exit(json_encode($output));
	}

	/**
	 * 对版块排序
	 *
	 * @return	void
	 */
	public function action_sort_forums()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '操作失败';
			$output->content = '您没有足够的权限进行此项操作。';
			$this->accessLevel = Admin::minimumLevel('sort_forum_forums');
			if ($this->user->role_id >= $this->accessLevel)
			{
				// 对版块排序
				$cateSort = explode(',', Functions::text($this->request->post('cate_sort')));
				Model::factory('forum_forum')->sortCategories($cateSort);
				$output->status = 1;
				$output->title = '操作成功';
				$output->content = '版块排序已完成。';
			}
			exit(json_encode($output));
		}
	}

}
