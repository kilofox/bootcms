<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 单页控制器。
 *
 * @package	BootCMS
 * @category	控制器
 * @Author		Tinsh
 * @copyright	(C) 2005-2016 Kilofox Studio
 */
class Controller_Page extends Controller_Template {

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
		$this->model = Model::factory('Node');
		// 通用使用给定的别名，查找该页节点
		$this->slug = HTML::chars($this->request->param('id'));
		// 为该节点设置缓存
		if (!($this->node = $cache->get('slug-' . $this->slug, false)))
		{
			$this->node = $this->model->loadBySlug($this->slug);
			if (isset($this->node->id))
			{
				$cache->set('slug-' . $this->slug, $this->node);
			}
		}
		if (!$this->node || $this->node->status <> 1)
			throw new HTTP_Exception_404();

		// 设置模板变量
		$this->homeUrl = Url::base();
		$this->template->homeUrl = $this->homeUrl;
		$this->template->slug = $this->slug;
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

	/**
	 * 单页默认方法，加载节点并添加到单页中
	 */
	public function action_index()
	{
		$this->template->title = $this->node->node_title;
		$sidebar = $comments = $pagination = '';
		if ($this->node->sidebar)
		{
			$sidebar = Setup::makeSidebar($this->node->sidebar);
		}
		if ($this->node->commenting)
		{
			// 评论分页
			$page = $this->request->query('page') > 0 ? (int) $this->request->query('page') : 1;
			$numPerPage = 10;
			$start = $numPerPage * ( $page - 1 );
			// 查询评论
			$sqlWhere = " AND c.node_id = {$this->node->id}";
			$sqlOrderBy = '';
			list($comments, $total) = Model::factory('node_comment')->findByPage($sqlWhere, $sqlOrderBy, $start, $numPerPage);
			$pagination = Functions::page($page, $total, $numPerPage, $this->homeUrl . $this->slug . '/?page=');
		}
		$this->template->title = $this->node->node_title;
		$this->template->body = View::factory('page')
			->bind('node', $this->node)
			->bind('sidebar', $sidebar)
			->bind('comments', $comments)
			->bind('pagination', $pagination);
	}

	/**
	 * 喜欢单页
	 */
	public function action_like()
	{
		$output = new stdClass();
		$output->status = 0;
		$output->title = '操作失败';
		$output->content = '您没有足够的权限进行此项操作。';
		if ($this->request->is_ajax())
		{
			$nodeId = (int) $this->request->post('node_id');
			$picture = $this->model->load($nodeId);
			// 文章不存在，或已标记为删除
			if (!$picture->id || $picture->status == 1)
			{
				$output->status = 2;
				$output->title = '文章不存在';
				$output->content = '文章不存在，或者已被删除。';
				exit(json_encode($output));
			}
			// 用户已经评论过该文章
			$oLike = Model::factory('picture_like');
			if ($oLike->isLiked($_SERVER['REMOTE_ADDR'], $picture->id))
			{
				$output->status = 3;
				$output->title = '文章已喜欢';
				$output->content = '您已经喜欢过该文章，请勿重复操作。';
				exit(json_encode($output));
			}
			// 创建评论
			$create = new stdClass();
			$create->node_id = $picture->id;
			$create->like_time = time();
			$create->ip = $_SERVER['REMOTE_ADDR'];
			$oLike->create($create);
			// 文章被评论次数
			$picture->likes++;
			foreach ($picture as $k => $v)
			{
				if (!in_array($k, array('id', 'likes')))
					unset($picture->$k);
			}
			if ($this->model->update())
			{
				$output = new stdClass();
				$output->status = 1;
				$output->title = '喜欢文章';
				$output->content = '喜欢文章成功。';
			}
		}
		exit(json_encode($output));
	}

	/**
	 *  发表评论
	 */
	public function action_comment()
	{
		$output = new stdClass();
		$output->status = 0;
		$output->title = '操作失败';
		$output->content = '您没有足够的权限进行此项操作。';
		if ($this->request->is_ajax())
		{
			// 用户未登录，不能发表评论
			if (!$this->user->id)
			{
				$output->status = 2;
				$output->title = '用户未登录';
				$output->content = '您还没有登录，不能发表评论。';
				exit(json_encode($output));
			}
			$nodeId = (int) $this->request->post('node_id');
			$node = $this->model->load($nodeId);
			// 文章不存在，或已标记为删除
			if (!$node->id || $node->status != 1)
			{
				$output->status = 3;
				$output->title = '文章不存在';
				$output->content = '文章不存在，或者已被删除。';
				exit(json_encode($output));
			}
			// 文章不允许评论
			if ($node->commenting != 1)
			{
				$output->status = 3;
				$output->title = '禁止评论';
				$output->content = '该文章已关闭评论功能。';
				exit(json_encode($output));
			}
			$oComment = Model::factory('node_comment');
			// 用户已经评论过该文章
			if ($oComment->isCommented($this->user->id, $node->id, 10))
			{
				$output->status = 4;
				$output->title = '发表失败';
				$output->content = '您对该文章的评论过于频繁，请稍候再试。';
				exit(json_encode($output));
			}
			// 创建评论
			try
			{
				$content = addslashes(HTML::chars($this->request->post('content')));
				$create = new stdClass();
				$create->user_id = $this->user->id;
				$create->node_id = $node->id;
				$create->reply_to = 0;
				$create->comment = $content;
				$create->created = time();
				$create->status = 0;
				if ($oComment->create($create))
				{
					$output->status = 1;
					$output->title = '评论已发布';
					$output->content = '您的评论发布成功，请等待管理员的审核。';
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

}
