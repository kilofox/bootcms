<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 查看论坛及主题列表。
 *
 * @package	BootCMS
 * @category	控制器
 * @author		Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class Controller_Forum extends Controller_Template {

	/**
	 * Before 方法
	 */
	public function before()
	{
		parent::before();
		$this->message = array();
		$cache = Cache::instance();
		if (!($views = $cache->get('views', false)))
		{
			$this->config = BootPHP::$config->load('global');
			$views = $this->config->get('views');
			$cache->set('views', $views);
		}
		foreach ($views as $key => $view)
		{
			if (!is_array($view))
				$this->template->$key = View::factory($view);
			else
				$this->template->$key = $view;
		}
		// 论坛设置
		if (!$this->config = $cache->get('forum_config', false))
		{
			$this->config = Model::factory('forum_config')->findAll();
			$cache->set('forum_config', $this->config);
		}
		$this->oMember = Model::factory('forum_member');
		$this->user = Auth::instance()->get_user();
		$this->member = NULL;
		if ($this->user)
		{
			if (!empty($_SESSION['member']))
			{
				$this->member = $_SESSION['member'];
			}
			else
			{
				$this->member = $this->oMember->loadByUser($this->user->id);
				if (!$this->member)
				{
					$member = new stdClass();
					$member->user_id = $this->user->id;
					$member->username = $this->user->username;
					$member->nickname = $this->user->nickname;
					$member->regdate = time();
					$member->show_email = 0;
					$member->level = $this->user->role_id == '9' ? 3 : 1;
					$this->oMember->create($member);
					Cache::instance()->delete('latest_member');
					Model::factory('forum_stat')->setByName('members', 1, true);
				}
				$this->member = $this->oMember->loadByUser($this->user->id);
				$this->member = (object) array_merge((array) $this->member, (array) $this->user);
				$_SESSION['member'] = $this->member;
			}
			$this->template->user = $this->member;
		}
		else
		{
			unset($_SESSION['member']);
		}
		$this->homeUrl = Url::base();
		$this->template->title = $this->config['board_name'];
		$this->template->homeUrl = $this->homeUrl;
	}

	/**
	 * After 方法
	 */
	public function after()
	{
		if ($this->message)
		{
			$this->template->body = View::factory('errors/message')
				->bind('message', $this->message);
		}
		parent::after();
	}

	/**
	 * 论坛板块
	 */
	public function action_index()
	{
		// 定义应该显示哪些分类
		$viewCat = (int) $this->request->param('id');
		// 取得版块与分类等信息
		$forumData = Model::factory('forum_forum')->getForumsAndCategories();
		if (!$forumData)
		{
			$this->message = array(
				'title' => '注意',
				'content' => '该论坛没有内容。管理员还没有创建任何版块。'
			);
			return;
		}
		$categories = array();
		$forums = 0;
		// 生成分类及其下的版块
		foreach ($forumData as $node)
		{
			if (Forumfunctions::auth($this->member, $node->auth, 'view', $node->id))
			{
				if (!array_key_exists($node->cat_id, $categories))
				{
					$categories[$node->cat_id] = array(
						'cat_name' => $node->cat_name,
						'cat_url' => $this->homeUrl . 'forum/index/' . $node->cat_id . '/#cat' . $node->cat_id,
						'cat_anchor' => 'cat' . $node->cat_id
					);
				}
				if (!$viewCat || $node->cat_id == $viewCat)
				{
					$node->icon = 'old.gif';
					$node->status = '没有新回复';
					if ($node->status == '0')
					{
						$node->icon = 'lock.gif';
						$node->status = '已锁定';
					}
					else
					{
						if ($node->last_post_time > time() - $this->config['new_post_minutes'] * 60)
						{
							$node->icon = 'new.gif';
							$node->status = '新回复';
						}
					}
					$node->name = '<a href="' . $this->homeUrl . 'forum/forum/' . $node->id . '/">' . $node->name . '</a>';
					$node->last_post_time = $node->last_post_time ? '<a href="' . $this->homeUrl . 'forum/topic/' . $node->last_topic_id . '/?page=-1#lastpost">' . Functions::makeDate($node->last_post_time) . '</a>' : '暂无回复';
					$categories[$node->cat_id]['forums'][] = $node;
					$forums++;
				}
			}
		}
		if (!$forums)
		{
			// 没有可以显示的版块
			$this->message = array(
				'title' => '注意',
				'content' => '您当前的用户级别无法查看任何版块。如果没有登录，请登录后再查看。如果您已经登录，可能您无权查看该内容。'
			);
			return;
		}
		if ($this->config['enable_forum_stats_box'])
		{
			$oStat = Model::factory('forum_stat');
			$arrStats = $oStat->findByName(array('posts', 'topics', 'members', 'latest_member'));
			$stats = array();
			foreach ($arrStats as $node)
			{
				$stats[$node->name] = $node->content;
			}
			$posts = isset($stats['posts']) ? $stats['posts'] : 0;
			$topics = isset($stats['topics']) ? $stats['topics'] : 0;
			$members = isset($stats['members']) ? $stats['members'] : 0;
			// 新会员
			if (!$latestMember = Cache::instance()->get('latest_member', false))
			{
				$latestMember = $this->oMember->loadLatest();
				Cache::instance()->set('latest_member', $latestMember);
			}
			$latestMember = $latestMember ? '欢迎新会员：<a href="' . $this->homeUrl . 'member/profile/' . $latestMember->user_id . '/">' . $latestMember->nickname . '</a>。' : '';
			$stats = array(
				'posts' => $posts,
				'topics' => $topics,
				'members' => $members,
				'latest_member' => $latestMember
			);
		}
		$this->template->title = '论坛首页 - ' . $this->template->title;
		$this->template->body = View::factory('forum/index')
			->bind('categories', $categories)
			->bind('config', $this->config)
			->bind('stats', $stats);
	}

	/**
	 * 论坛板块
	 */
	public function action_forum()
	{
		$forumId = (int) $this->request->param('id');
		// 无论坛ID。转到首页。
		$forumId <= 0 and $this->request->redirect('forum');
		// 获取论坛信息
		$forum = Model::factory('forum_forum')->findWithCategory($forumId);
		if (!$forum->id)
		{
			$this->message = array(
				'title' => '错误',
				'content' => '请求的版块 ' . $forumId . ' 不存在！'
			);
			return;
		}
		// 允许用户浏览该论坛
		if (!Forumfunctions::auth($this->member, $forum->auth, 'view', $forum->id))
		{
			$this->message = array(
				'title' => '错误',
				'content' => '您当前的用户级别无法查看任何版块。如果没有登录，请登录后再查看。如果您已经登录，可能您无权查看该内容。'
			);
			return;
		}
		$moderators = Forumfunctions::get_mods_list($forum->id);
		$forum->moderators = !$forum->hide_mods_list && $moderators ? '版主：' . $moderators : '';
		// 分页
		$page = $this->request->query('page') > 0 ? (int) $this->request->query('page') : 1;
		$numPerPage = (int) $this->config['topics_per_page'];
		$start = ( $page - 1 ) * $numPerPage;
		$forum->pageLinks = Functions::page($page, $forum->topics, $numPerPage, $this->homeUrl . 'forum/forum/' . $forum->id . '/?page=');
		// 主题列表
		if ($forum->topics)
		{
			// 取得主题列表信息
			$topics = Model::factory('forum_topic')->getTopicsByForum($forum->id, $start, $this->config['topics_per_page']);
			foreach ($topics as $node)
			{
				$node->topic_name = '<a href="' . $this->homeUrl . 'forum/topic/' . $node->id . '/">' . Forumfunctions::replace_badwords($node->topic_title) . '</a>';
				if ($node->status_sticky)
					$node->topic_name = '置顶: ' . $node->topic_name;
				$node->topic_icon = 'old.gif';
				$node->topic_status = '没有新回复';
				if ($node->status_locked)
				{
					$node->topic_icon = 'lock.gif';
					$node->topic_status = '已锁定';
				}
				else
				{
					if (isset($_SESSION['viewed_topics']['t' . $node->id]) && $_SESSION['viewed_topics']['t' . $node->id] < $node->last_post_time)
					{
						$node->topic_icon = 'new.gif';
						$node->topic_status = '新回复';
					}
				}
				// 模板变量
				$node->author = Forumfunctions::make_profile_link($this->homeUrl, $node->user_id, $node->nickname, $node->level);
				if ($node->last_post_id)
				{
					$lastPostAuthor = '<a href="' . $this->homeUrl . 'member/profile/' . $node->last_poster_id . '/">' . stripslashes($node->last_poster_name) . '</a>';
					$lastPostLink = '<a href="' . $this->homeUrl . 'forum/topic/' . $node->id . '/?page=-1#lastpost" rel="nofollow">&gt;&gt;</a>';
					$node->last_post = '作者：' . $lastPostAuthor . ' ' . $lastPostLink . '<br />发表于：' . Functions::makeDate($node->last_post_time);
				}
				else
				{
					$node->last_post = '-';
				}
			}
		}
		$this->template->title = $forum->name . ' - ' . $this->template->title;
		$this->template->body = View::factory('forum/topic_list')
			->bind('forum', $forum)
			->bind('topics', $topics);
	}

	/**
	 * 查看主题
	 */
	public function action_topic()
	{
		$topicId = (int) $this->request->param('id');
		$topicId <= 0 and $this->request->redirect('forum');
		$oTopic = Model::factory('forum_topic');
		$topic = $oTopic->findWithForum($topicId);
		if (!$topic)
		{
			// 帖子不存在，显示错误
			$this->message = array(
				'title' => '错误',
				'content' => '主题 ' . $topicId . ' 不存在，或者已被删除。'
			);
			return;
		}
		if (!Forumfunctions::auth($this->member, $topic->auth, 'read', $topic->forum_id))
		{
			// 用户无权浏览这个主题
			$this->message = array(
				'title' => '无权限',
				'content' => '您没有浏览该主题的权限。'
			);
			return;
		}
		$viewedTopics = isset($_SESSION['viewed_topics']) ? $_SESSION['viewed_topics'] : array();
		// 更新浏览数量
		if (!array_key_exists('t' . $topic->id, $viewedTopics))
		{
			$oTopic->updateTopic($topic->id, array('views' => ++$topic->views));
		}
		// 订阅用户的主题
		if (!empty($_SESSION['subscribe_msg']) && in_array($_SESSION['subscribe_msg'], array('subscribed', 'unsubscribed')))
		{
			$this->message = array(
				'title' => '注意',
				'content' => $_SESSION['subscribe_msg'] == 'subscribed' ? '您已经成功订阅该主题。' : '您已经成功取消订阅该主题。'
			);
			unset($_SESSION['subscribe_msg']);
			//return;
		}
		// 订阅
		$oSubscription = Model::factory('forum_subscription');
		if ($this->member)
		{
			$subscribed = $oSubscription->getNumByTopicAndUser($topic->id, $this->member->user_id);
		}
		else
		{
			$subscribed = false;
		}
		if (in_array($this->request->query('act'), array('subscribe', 'unsubscribe')) && Forumfunctions::verify_url())
		{
			if (!$this->member)
			{
				$this->request->redirect('member/login');
			}
			if (!$subscribed && $this->request->query('act') == 'subscribe')
			{
				$create = new stdClass();
				$create->topic_id = $topic->id;
				$create->user_id = $this->member->user_id;
				$oSubscription->create($create);
				$_SESSION['subscribe_msg'] = 'subscribed';
				$this->request->redirect('forum/topic/' . $topic->id);
			}
			elseif ($subscribed && $this->request->query('act') == 'unsubscribe')
			{
				$oSubscription->deleteByTopicAndUser($topic->id, $this->member->user_id);
				$_SESSION['subscribe_msg'] = 'unsubscribed';
				$this->request->redirect('forum/topic/' . $topic->id);
			}
		}
		$canPostLinks = Forumfunctions::antispam_can_post_links($this->member, true);
		// 主题
		$topic->topic_title = Forumfunctions::replace_badwords($topic->topic_title);
		$topic->topic_content = Forumfunctions::markup(Forumfunctions::replace_badwords($topic->topic_content), true, true, true, $canPostLinks, $this->homeUrl);
		$topic->avatar = $topic->avatar ? $this->homeUrl . 'assets/avatar/uploads/' . $topic->avatar : $this->homeUrl . 'assets/uploads/avatar/avatar_middle.jpg';
		$topic->regdate = Functions::makeDate($topic->regdate, 'Y年n月');
		$topic->signature = !$this->config['hide_signatures'] && $topic->signature ? '_______________<br />' . Forumfunctions::markup(Forumfunctions::replace_badwords(stripslashes($topic->signature)), true, true, true, NULL, $can_add_profile_links) : '';
		$topic->creater_ip = $topic->creater_ip && $this->member->level == 3 ? 'IP：<a href="' . $this->homeUrl . 'forum/admin/?act=iplookup&ip=' . $topic->creater_ip . '">' . $topic->creater_ip . '</a>' : '';
		$topic->edit_info = '';
		if ($topic->edited_time && ( $topic->edited_time - $topic->created_time > intval($this->config['show_edited_message_timeout']) ))
		{
			$topic->edit_info = '<a href="' . $this->homeUrl . 'member/profile/' . $topic->editor_id . '/">' . $topic->editor_name . '</a>';
			$topic->edit_info = '&laquo; 由 ' . $topic->edit_info . ' 最后编辑于 ' . Functions::makeDate($topic->edited_time) . '。 &raquo;';
		}
		// 版主
		$forumModerators = Forumfunctions::get_mods_list($topic->forum_id);
		$topic->forumModerators = !$topic->hide_mods_list && $forumModerators ? '版主：' . $forumModerators : '';
		$canPostReply = (!$topic->status_locked || Forumfunctions::auth($this->member, $topic->auth, 'lock', $topic->forum_id) ) && ( $topic->forum_status || $this->member->level == 3 );
		$topic->replyLink = $canPostReply ? '<input type="button" onclick="window.location.href=\'' . $this->homeUrl . 'forum/post_reply/' . $topic->id . '/\'" rel="nofollow" value="发表回复" class="submit" style="float:right;" />' : ( $topic->status_locked ? '<span>主题已锁定</span>' : '' );
		// 用于控制帖子的链接：引用、编辑、删除等等
		$postLinks = array();
		if ($this->member && ( ( $topic->creater_id == $this->member->user_id && ( time() - $this->config['edit_post_timeout'] ) <= $topic->created_time ) || Forumfunctions::auth($this->member, $topic->auth, 'edit', $topic->forum_status) ))
			$postLinks[] = '<a href="' . $this->homeUrl . 'forum/edit_topic/' . $topic->id . '/">编辑</a>';
		if ($this->member && ( ( $topic->creater_id == $this->member->user_id && ( time() - $this->config['edit_post_timeout'] ) <= $topic->created_time ) || Forumfunctions::auth($this->member, $topic->auth, 'delete', $topic->forum_id) ))
			$postLinks[] = '<a href="javascript:void(0);" data-tid="' . $topic->id . '">删除</a>';
		$topic->postLinks = $postLinks ? implode(' | ', $postLinks) : '';
		$topic->created_time = Functions::makeDate($topic->created_time);
		// 级别
		switch ($topic->level)
		{
			case 3:
				$topic->level = '管理员';
				break;
			case 2:
				$topic->level = '版主';
				break;
			case 1:
				$topic->level = '会员';
				break;
		}
		// 回复列表
		// 分页
		$numPerPage = (int) $this->config['posts_per_page'];
		$page = (int) $this->request->query('page');
		$page === -1 and $page = ceil($topic->replies / $numPerPage);
		$page <= 0 and $page = 1;
		$start = ( $page - 1 ) * $numPerPage;
		$topic->pageLinks = Functions::page($page, $topic->replies + 1, $numPerPage, $this->homeUrl . 'forum/topic/' . $topic->id . '/?page=');
		// 根据帖子ID计算页号
		$postId = (int) $this->request->query('post');
		$oPost = Model::factory('forum_post');
		if ($postId > 0)
		{
			$topic->last_post_id;
		}
		$sqlSelect = ', m.avatar';
		$sqlSelect.=!$this->config['hide_signatures'] ? ', p.enable_sig' : '';
		$sqlSelect.=!$this->config['hide_signatures'] ? ', m.signature' : '';
		$posts = Model::factory('forum_post')->findByTopic($topic->id, $sqlSelect, $start, $numPerPage);
		// 循环帖子
		$i = 0;
		foreach ($posts as $node)
		{
			// 帖子数量
			$node->i = ++$i + $start;
			// 用于切换模板中的颜色
			$node->colornum = $i % 2 ? 1 : 2;
			// 带链接的昵称
			$node->poster_name = Forumfunctions::make_profile_link($this->homeUrl, $node->poster_id, $node->nickname, $node->level);
			// 用户头像
			!$node->avatar and $node->avatar = $this->homeUrl . 'assets/uploads/avatar/avatar_middle.jpg';

			// 用于控制帖子的链接：引用、编辑、删除等等
			$postLinks = array();
			if ($this->member && ( ( $node->poster_id == $this->member->user_id && ( time() - $this->config['edit_post_timeout'] ) <= $node->post_time ) || Forumfunctions::auth($this->member, $topic->auth, 'edit', $topic->forum_status) ) && $node->level <= $this->member->level)
				$postLinks[] = '<a href="' . $this->homeUrl . 'forum/edit_post/' . $node->id . '/">编辑</a>';
			if ($this->member && ( ( $node->poster_id == $this->member->user_id && ( time() - $this->config['edit_post_timeout'] ) <= $node->post_time ) || Forumfunctions::auth($this->member, $topic->auth, 'delete', $topic->forum_id) ) && $node->level <= $this->member->level)
				$postLinks[] = '<a href="javascript:void(0);" data-pid="' . $node->id . '">删除</a>';
			if ($canPostReply)
				$postLinks[] = '<a href="' . $this->homeUrl . 'forum/post_reply/' . $topic->id . '/?quotepost=' . $node->id . '" rel="nofollow">引用</a>';
			$node->postLinks = $postLinks ? implode(' | ', $postLinks) : '';
			// 级别
			switch ($node->level)
			{
				case 3:
					$node->level = '管理员';
					break;
				case 2:
					$node->level = '版主';
					break;
				case 1:
					$node->level = '会员';
					break;
			}
			$node->edit_info = '';
			if ($node->edited_time && ( $node->edited_time > ( $node->post_time + intval($this->config['show_edited_message_timeout']) ) ))
			{
				$node->edit_info = '<a href="' . $this->homeUrl . 'member/profile/' . $node->editor_id . '/">' . $node->editor_name . '</a>';
				$node->edit_info = '&laquo; 由 ' . $node->edit_info . ' 最后编辑于 ' . Functions::makeDate($node->edited_time) . '。 &raquo;';
			}
			$can_add_profile_links = Forumfunctions::antispam_can_add_profile_links($node);
			$canPostLinks = Forumfunctions::antispam_can_post_links($node);
			// 帖子
			$node->regdate = Functions::makeDate($node->regdate, 'Y年n月');
			$node->post_time = Functions::makeDate($node->post_time);
			$node->post_content = Forumfunctions::markup(Forumfunctions::replace_badwords($node->content), true, true, true, $canPostLinks, $this->homeUrl);
			$node->signature = !$this->config['hide_signatures'] && $node->signature ? '_______________<br />' . Forumfunctions::markup(Forumfunctions::replace_badwords(stripslashes($node->signature)), true, true, true, NULL, $can_add_profile_links) : '';
			$node->poster_ip = $node->poster_ip && $this->member && $this->member->level == 3 ? 'IP：<a href="' . $this->homeUrl . 'forum/admin/?act=iplookup&ip=' . $node->poster_ip . '">' . $node->poster_ip . '</a>' : '';
		}
		// 控制主题的链接：删除、移动、锁定、置顶等
		$actionLinks = array();
		if ($this->member)
		{
			/* if ( !$subscribed )
			  $actionLinks[] = '<a href="' . $this->homeUrl . 'forum/topic/' . $topic->id . '/?act=subscribe&' . Forumfunctions::generate_token() . '">订阅</a>';
			  else
			  $actionLinks[] = '<a href="' . $this->homeUrl . 'forum/topic/' . $topic->id . '/?act=unsubscribe&' . Forumfunctions::generate_token() . '">取消订阅</a>'; */
		}
		if (Forumfunctions::auth($this->member, $topic->auth, 'delete', $topic->forum_id))
			$actionLinks[] = '<a href="javascript:void(0);" data-tid="' . $topic->id . '">删除主题</a>';
		$forums = Model::factory('forum_forum')->findAll();
		$viewableForumNum = 0;
		foreach ($forums as $node)
		{
			if (Forumfunctions::auth($this->member, $node->auth, 'view', $node->id))
				$viewableForumNum++;
		}
		if (Forumfunctions::auth($this->member, $topic->auth, 'move', $topic->forum_id) && $viewableForumNum > 1)
			$actionLinks[] = '<a href="' . $this->homeUrl . 'forum/move_topic/' . $topic->id . '/">移动主题</a>';
		if (Forumfunctions::auth($this->member, $topic->auth, 'lock', $topic->forum_id))
		{
			/* if ( $topic->status_locked )
			  $actionLinks[] = '<a href="' . $this->homeUrl . 'forum/topic/' . $topic->id . '/?act=unlock&' . Forumfunctions::generate_token() . '">解锁主题</a>';
			  else
			  $actionLinks[] = '<a href="' . $this->homeUrl . 'forum/topic/' . $topic->id . '/?act=lock&' . Forumfunctions::generate_token() . '">锁定主题</a>'; */
		}
		if (Forumfunctions::auth($this->member, $topic->auth, 'sticky', $topic->forum_id))
		{
			if ($topic->status_sticky)
				$actionLinks[] = '<a href="javascript:void(0);" data-sticky="' . $topic->id . '">取消置顶</a>';
			else
				$actionLinks[] = '<a href="javascript:void(0);" data-sticky="' . $topic->id . '">置顶</a>';
		}
		$topic->actionLinks = implode(' ', $actionLinks);
		// 快速回复
		$quickReply = array();
		if ($this->config['enable_quickreply'] && (!$topic->status_locked || Forumfunctions::auth($this->member, $topic->auth, 'lock', $topic->forum_id)) && ($topic->forum_status || $this->member->level == 3) && Forumfunctions::auth($this->member, $topic->auth, 'reply', $topic->forum_id) && isset($_SESSION['antispam_question_posed']))
		{
			$quickReply['subscribe_topic'] = $this->member && $this->member->auto_subscribe_reply ? 1 : 0;
		}
		$_SESSION['viewed_topics']['t' . $topic->id] = time();
		$this->template->title = $topic->topic_title . ' - ' . $this->template->title;
		$this->template->body = View::factory('forum/topic')
			->bind('page', $page)
			->bind('topic', $topic)
			->bind('quickReply', $quickReply)
			->bind('posts', $posts);
	}

	/**
	 * 发表主题
	 */
	public function action_post_topic()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '错误';
			$output->content = '您没有足够的权限进行该操作。';
			$errors = array();
			if (!$this->request->post('subject'))
				$errors[] = '主题';
			if (Forumfunctions::post_empty($this->request->post('content')))
				$errors[] = '内容';
			if ($errors)
			{
				$output->status = 2;
				$output->title = '错误';
				$output->content = '下列内容为空或者错误：<br />' . implode('、', $errors) . '。';
				exit(json_encode($output));
			}
			$floodProtectWaitSec = $this->member->level <= 1 ? $this->config['flood_interval'] - time() + $_SESSION['latest_post'] : 0;
			if ($floodProtectWaitSec > 0)
			{
				$output->status = 2;
				$output->title = '注意';
				$output->content = '发帖时间间隔为 ' . $this->config['flood_interval'] . ' 秒。请 ' . $floodProtectWaitSec . ' 秒钟之后重新提交该表单。';
				exit(json_encode($output));
			}
			$forumId = (int) $this->request->post('fid');
			$oForum = Model::factory('forum_forum');
			$forum = $oForum->load($forumId);
			if (!$forum)
			{
				$output->status = 2;
				$output->title = '错误';
				$output->content = '请求的版块 ' . $forumId . ' 不存在！';
				exit(json_encode($output));
			}
			$oTopic = Model::factory('forum_topic');
			try
			{
				// 创建主题
				$create = new stdClass();
				$create->forum_id = $forum->id;
				$create->topic_title = addslashes(Forumfunctions::unhtml($this->request->post('subject')));
				$create->topic_content = addslashes(Forumfunctions::unhtml($this->request->post('content')));
				$create->created_time = $create->last_post_time = time();
				$create->creater_id = $this->member->user_id;
				$create->creater_name = $this->member->nickname ? $this->member->nickname : $this->member->username;
				$create->status_locked = Forumfunctions::auth($this->member, $forum->auth, 'lock', $forum->id) && $this->request->post('lock_topic') ? 1 : 0;
				$create->status_sticky = Forumfunctions::auth($this->member, $forum->auth, 'sticky', $forum->id) && $this->request->post('sticky_topic') ? 1 : 0;
				$topicId = $oTopic->create($create);
				// 更新版块
				$forumUpdate = new stdClass();
				$forumUpdate->id = $forum->id;
				$forumUpdate->topics = ':+1';
				$forumUpdate->posts = ':+1';
				$forumUpdate->last_topic_id = $topicId;
				$forumUpdate->last_post_time = $create->last_post_time;
				Model::factory('forum_forum')->updateForum($forumUpdate);
				// 更新用户
				$poster = new stdClass();
				$poster->user_id = $this->member->user_id;
				$poster->posts = ':+1';
				Model::factory('forum_member')->updateMember($poster);
				// 更新统计
				$oStat = Model::factory('forum_stat');
				$oStat->setByName('topics', 1, true);
				$oStat->setByName('posts', 1, true);
				// 用户订阅主题
				if ($this->request->post('subscribe_topic'))
				{
					$oSubscription = Model::factory('forum_subscription');
					$create = new stdClass();
					$create->topic_id = $topicId;
					$create->user_id = $this->member->user_id;
					$oSubscription->create($create);
				}
				// 最后发表时间
				$_SESSION['latest_post'] = time();
				$output->status = 1;
				$output->content = $topicId;
				exit(json_encode($output));
			}
			catch (Validation_Exception $e)
			{
				$errors = $e->errors('models');
				$errors = implode('<br />', $errors);
				$output->status = 5;
				$output->title = '主题帖发表失败';
				$output->content = $errors;
				exit(json_encode($output));
			}
			exit;
		}

		$forumId = (int) $this->request->param('id');
		$forum = Model::factory('forum_forum')->load($forumId);
		if (!$forum)
		{
			$this->message = array(
				'title' => '错误',
				'content' => '请求的版块 ' . $forumId . ' 不存在！'
			);
			return;
		}
		!$this->member and $this->request->redirect('member/login');
		if (!$forum->status && $this->member->level != 3)
		{
			$this->message = array(
				'title' => '版块已锁定',
				'content' => '该版块已被锁定。只有认证会员才能发表新主题。'
			);
			return;
		}
		if (!Forumfunctions::auth($this->member, $forum->auth, 'post', $forum->id))
		{
			$this->request->redirect('forum');
		}
		// 产生反垃圾帖问题
		//Forumfunctions::pose_antispam_question();
		$floodProtectWaitSec = ( $this->member->level <= 1 ) ? ( $this->config['flood_interval'] - ( time() - $_SESSION['latest_post'] ) ) : 0;
		$canPostLinks = Forumfunctions::antispam_can_post_links($this->member, true);
		$optionsInput = array();
		if (Forumfunctions::auth($this->member, $forum->auth, 'lock', $forum->id))
			$optionsInput[] = '<input type="checkbox" name="lock_topic" value="1" /> 发表后锁定文章';
		if (Forumfunctions::auth($this->member, $forum->auth, 'sticky', $forum->id))
			$optionsInput[] = '<input type="checkbox" name="sticky_topic" value="1" /> 置顶当前主题';
		$subscribeTopicChecked = $this->member->auto_subscribe_topic ? ' checked="checked"' : '';
		$optionsInput[] = '<input type="checkbox" name="subscribe_topic" value="1"' . $subscribeTopicChecked . '/> 订阅该主题';
		$optionsInput = implode('<br />', $optionsInput);
		$form = array(
			'potential_spammer_notice' => $canPostLinks ? '' : '<div class="potential-spammer-notice">您当前的状态为疑似垃圾信息发布者，您所发表的帖子中的链接将无法点击。请您谅解！</div>',
			'bbcodeControls' => Forumfunctions::get_bbcode_controls($canPostLinks),
			'smileyControls' => Forumfunctions::get_smiley_controls($this->homeUrl),
			'optionsInput' => $optionsInput,
		);
		$this->template->title = '新主题 - ' . $this->template->title;
		$this->template->body = View::factory('forum/post_topic')
			->bind('user', $this->member)
			->bind('forum', $forum)
			->bind('form', $form);
	}

	/**
	 * 编辑主题
	 */
	public function action_edit_topic()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '错误';
			$output->content = '您没有足够的权限进行该操作。';
			$topicId = (int) $this->request->post('tid');
			if ($topicId <= 0)
			{
				$output->status = 2;
				$output->title = '错误';
				$output->content = '非法操作！';
				exit(json_encode($output));
			}
			$oTopic = Model::factory('forum_topic');
			$topic = $oTopic->load($topicId);
			if (!$topic)
			{
				$output->status = 3;
				$output->title = '错误';
				$output->content = '请求的主题 ' . $topicId . ' 不存在！';
				exit(json_encode($output));
			}
			// 主题置顶
			if ($this->request->post('act') == 'sticky')
			{
				$forum = Model::factory('forum_forum')->load($topic->forum_id);
				if (!$this->member || !Forumfunctions::auth($this->member, $forum->auth, 'sticky', $topic->forum_id))
				{
					$output->status = 4;
					$output->title = '错误';
					$output->content = '您无权置顶 ID 为 ' . $topic->id . ' 的帖子。';
					exit(json_encode($output));
				}
				$stickySet = 1;
				$content = '置顶成功。';
				if ($topic->status_sticky == 1)
				{
					$stickySet = 0;
					$content = '取消置顶成功。';
				}
				try
				{
					if ($oTopic->updateTopic($topic->id, array('status_sticky' => $stickySet)))
					{
						$output->status = 1;
						$output->content = $content;
						$output->stickySet = $stickySet;
					}
					else
					{
						$output->status = 4;
						$output->content = '主题置顶修改失败。';
					}
					exit(json_encode($output));
				}
				catch (Validation_Exception $e)
				{
					$errors = $e->errors('models');
					$errors = implode('<br />', $errors);
					$output->status = 5;
					$output->title = '主题置顶失败';
					$output->content = $errors;
					exit(json_encode($output));
				}
				exit;
			}
			// 主题修改
			$errors = array();
			if (!$this->request->post('subject'))
				$errors[] = '主题';
			if (Forumfunctions::post_empty($this->request->post('content')))
				$errors[] = '内容';
			if ($errors)
			{
				$output->status = 2;
				$output->title = '错误';
				$output->content = '下列内容为空或者错误：<br />' . implode('、', $errors) . '。';
				exit(json_encode($output));
			}
			if ($floodProtectWaitSec > 0)
			{
				$output->status = 2;
				$output->title = '注意';
				$output->content = '发帖时间间隔为 ' . $this->config['flood_interval'] . ' 秒。请 ' . $floodProtectWaitSec . ' 秒钟之后重新提交该表单。';
				exit(json_encode($output));
			}
			$forum = Model::factory('forum_forum')->load($topic->forum_id);
			$creater = Model::factory('forum_member')->loadByUser($topic->creater_id);
			if (!$this->member || ( $topic->creater_id != $this->member->user_id || (time() - $this->config['edit_post_timeout'] > $topic->created_time) ) && (!Forumfunctions::auth($this->member, $forum->auth, 'edit', $topic->forum_id) ) || $creater->level > $this->member->level)
			{
				$output->status = 4;
				$output->title = '错误';
				$output->content = '您无权编辑 ID 为 ' . $topic->id . ' 的帖子。';
				exit(json_encode($output));
			}
			try
			{
				$topic->topic_title = addslashes(Forumfunctions::unhtml($this->request->post('subject')));
				$topic->topic_content = addslashes(Forumfunctions::unhtml($this->request->post('content')));
				$topic->edited_time = time();
				$topic->editor_id = $this->member->user_id;
				$topic->editor_name = $this->member->nickname ? $this->member->nickname : $this->member->username;
				$topic->editor_ip = '';
				if ($oTopic->update())
				{
					$output->status = 1;
					$output->content = $topic->id;
				}
				else
				{
					$output->status = 4;
					$output->content = '主题修改失败。';
				}
				exit(json_encode($output));
			}
			catch (Validation_Exception $e)
			{
				$errors = $e->errors('models');
				$errors = implode('<br />', $errors);
				$output->status = 5;
				$output->title = '主题帖编辑失败';
				$output->content = $errors;
				exit(json_encode($output));
			}
			exit;
		}
		$topicId = (int) $this->request->param('id');
		$topicId <= 0 and $this->request->redirect('forum');
		$oTopic = Model::factory('forum_topic');
		$topic = $oTopic->findWithForum($topicId);
		if ($this->member && ( ( $topic->creater_id == $this->member->user_id && (time() - $this->config['edit_post_timeout'] < $topic->created_time) ) || Forumfunctions::auth($this->member, $topic->auth, 'edit', $topic->forum_id) ) && $topic->level <= $this->member->level)
		{
			$canPostLinks = Forumfunctions::antispam_can_post_links($this->member);
			$form = array(
				'potentialSpammerNotice' => $canPostLinks ? '' : '<div>你的状态（暂时）为疑似垃圾信息发布者，即你所发表的帖子中的链接将得不到处理（无法点击）。请你谅解！</div>',
				'bbcodeControls' => Forumfunctions::get_bbcode_controls($canPostLinks),
				'smileyControls' => Forumfunctions::get_smiley_controls($this->homeUrl),
				'allowHtml' => Forumfunctions::auth($this->member, $topic->auth, 'html', $topic->forum_id) ? true : false,
				'token' => Forumfunctions::generate_token()
			);
		}
		else
		{
			$this->message = array(
				'title' => '错误',
				'content' => '您无权编辑 ID 为 ' . $topic->id . ' 的帖子。'
			);
			return;
		}
		$this->template->title = '编辑主题 - ' . $this->template->title;
		$this->template->body = View::factory('forum/edit_topic')
			->bind('topic', $topic)
			->bind('form', $form);
	}

	/**
	 * 编辑主题
	 */
	public function action_move_topic()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '错误';
			$output->content = '您没有足够的权限进行该操作。';
			$topicId = (int) $this->request->post('tid');
			if ($topicId <= 0)
			{
				$output->status = 2;
				$output->title = '错误';
				$output->content = '非法操作！';
				exit(json_encode($output));
			}
			$oTopic = Model::factory('forum_topic');
			$topic = $oTopic->findWithForum($topicId);
			if (!$topic->id)
			{
				$output->status = 3;
				$output->title = '错误';
				$output->content = '请求的主题 ' . $topicId . ' 不存在！';
				exit(json_encode($output));
			}
			if (!$this->member || !Forumfunctions::auth($this->member, $topic->auth, 'move', $topic->forum_id))
			{
				$output->status = 4;
				$output->title = '错误';
				$output->content = '您无权移动 ID 为 ' . $topic->id . ' 的帖子。';
				exit(json_encode($output));
			}
			$destForumId = intval($_POST['dest_forum']);
			if (!Model::factory('forum_forum')->load($destForumId))
			{
				$output->status = 3;
				$output->title = '错误';
				$output->content = '目标版块不存在，无法移动主题。';
				exit(json_encode($output));
			}
			try
			{
				if ($oTopic->updateTopic($topic->id, array('forum_id' => $destForumId)))
				{
					$output->status = 1;
					$output->content = $topic->id;
				}
				else
				{
					$output->status = 4;
					$output->content = '主题移动修改失败。';
				}
				exit(json_encode($output));
			}
			catch (Validation_Exception $e)
			{
				$errors = $e->errors('models');
				$errors = implode('<br />', $errors);
				$output->status = 5;
				$output->title = '主题移动失败';
				$output->content = $errors;
				exit(json_encode($output));
			}
			exit;
		}
		$topicId = (int) $this->request->param('id');
		$topicId <= 0 and $this->request->redirect('forum');
		$oTopic = Model::factory('forum_topic');
		$topic = $oTopic->findWithForum($topicId);
		if (!$this->member || !Forumfunctions::auth($this->member, $topic->auth, 'move', $topic->forum_id))
		{
			$this->message = array(
				'title' => '错误',
				'content' => '您无权移动 ID 为 ' . $topic->id . ' 的帖子。'
			);
			return;
		}
		$forums = Model::factory('forum_forum')->findAll();
		$this->template->title = '移动主题 - ' . $this->template->title;
		$this->template->body = View::factory('forum/move_topic')
			->bind('topic', $topic)
			->bind('forums', $forums);
	}

	/**
	 * 发表回复
	 */
	public function action_post_reply()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '错误';
			$output->content = '您没有足够的权限进行该操作。';
			if (!$this->member)
			{
				$output->status = 0;
				$output->title = '错误';
				$output->content = '用户名错误。';
				exit(json_encode($output));
			}
			$topicId = (int) $this->request->post('tid');
			$oTopic = Model::factory('forum_topic');
			if (!$topic = $oTopic->load($topicId))
			{
				$output->status = 4;
				$output->title = '错误';
				$output->content = '请求的主题ID ' . $topicId . ' 不存在。';
				exit(json_encode($output));
			}
			$oForum = Model::factory('forum_forum');
			$forum = $oForum->load($topic->forum_id);
			if (!Forumfunctions::auth($this->member, $forum->auth, 'reply', $forum->id))
			{
				$output->status = 2;
				$output->title = '错误';
				$output->content = '您没有足够的权限进行该操作。如有疑问，请联系管理员。';
				exit(json_encode($output));
			}
			if (Forumfunctions::post_empty($_POST['content']))
			{
				$output->status = 3;
				$output->title = '错误';
				$output->content = '回复内容为空。';
				exit(json_encode($output));
			}
			$floodProtectWaitSec = $this->member->level <= 1 ? $this->config['flood_interval'] - time() + $_SESSION['latest_post'] : 0;
			if ($floodProtectWaitSec > 0)
			{
				$output->status = 4;
				$output->title = '错误';
				$output->content = '管理员设定的帖子发表时间间隔为 ' . $this->config['flood_interval'] . ' 秒。请等待至少 ' . $floodProtectWaitSec . ' 秒之后再重新提交该表单。';
				exit(json_encode($output));
			}
			// 设置反垃圾问题
			//Forumfunctions::pose_antispam_question();
			$oPost = Model::factory('forum_post');
			$create = new stdClass();
			$create->topic_id = $topic->id;
			$create->poster_id = $this->member->user_id;
			$create->poster_ip = $_SERVER['REMOTE_ADDR'] ? $_SERVER['REMOTE_ADDR'] : getenv('REMOTE_ADDR');
			$create->content = addslashes(Forumfunctions::unhtml($this->request->post('content')));
			$create->post_time = time();
			try
			{
				if ($postId = $oPost->create($create))
				{
					// 更新主题
					$topicStatus = ( Forumfunctions::auth($this->member, $forum->auth, 'lock', $forum->id) && $this->request->post('lock_topic') ) || ( $forum->auto_lock && $topic->replies + 1 >= $topic->auto_lock ) ? 1 : 0;
					$update = array(
						'last_post_id' => $postId,
						'last_post_time' => $create->post_time,
						'last_poster_id' => $this->member->user_id,
						'last_poster_name' => $this->member->nickname,
						'replies' => ':+1',
						'status_locked' => $topicStatus,
					);
					$oTopic->updateTopic($topic->id, $update);
					// 更新版块
					$forumUpdate = new stdClass();
					$forumUpdate->id = $forum->id;
					$forumUpdate->posts = ':+1';
					$forumUpdate->last_post_time = $create->post_time;
					Model::factory('forum_forum')->updateForum($forumUpdate);
					// 更新用户
					$poster = new stdClass();
					$poster->user_id = $this->member->user_id;
					$poster->posts = ':+1';
					Model::factory('forum_member')->updateMember($poster);
					// 更新统计
					$oStat = Model::factory('forum_stat');
					$stat = $oStat->loadByName('posts');
					$stat->content++;
					$oStat->update();
					// E-mail 订阅用户
					$sUsers = Model::factory('forum_subscription')->findByTopicExceptUser($topic->id);
					foreach ($sUsers as $su)
					{
						if (Forumfunctions::auth($this->member, $forum->auth, 'read', $forum->id, false, $su))
						{
							$subject = '在“' . stripslashes($topic->topic_title) . '”中的新回复';
							$poster_name = $this->member->user_id ? stripslashes($this->member->nickname) : '-';
							$topic->topic_title = stripslashes($topic->topic_title);
							$topic_link = Forumfunctions::make_url('topic.php', array(
									'post' => $postId), false) . '#post' . $postId;
							$unsubscribe_link = Forumfunctions::make_url('topic.php', array(
									'id' => $topic->id, 'act' => 'unsubscribe'), false);
							$message = '您好，
		这是由 ' . $this->config['board_name'] . ' 自动发送的邮件。有人（' . $poster_name . '）在您的订阅（"' . $topic->topic_title . '"）中回复了新内容。要查看回复，请点击后面的链接：' . $topic_link . '。
		如果您想取消订阅该主题（需要登录），请点击后面的链接：' . $unsubscribe_link . '。'
								. $this->config['board_name']
								. $this->config['admin_email'];
							$opts = BootPHP::$config->load('email')->default;
							$mail = Mail::instance($opts);
							$mail->setFrom($opts['smtp_user'], $this->config['admin_email']);
							$mail->setTo($this->member->email);
							$mail->setSubject($subject);
							$mail->setBody($message);
							$mail->send();
						}
					}
					// 用户订阅主题
					$oSubscription = Model::factory('forum_subscription');
					$subscribed = $oSubscription->getNumByTopicAndUser($topic->id, $this->member->user_id);
					if (!$subscribed && $this->request->post('subscribe_topic'))
					{
						$subscription = new stdClass();
						$subscription->topic_id = $topic->id;
						$subscription->user_id = $this->member->user_id;
						$oSubscription->create($subscription);
					}
					// 最后发表时间
					$_SESSION['latest_post'] = time();
					$output->status = 1;
					$output->content = '发表回复成功。';
				}
				else
				{
					$output->status = 9;
					$output->title = '错误';
					$output->content = '发表回复失败。';
				}
			}
			catch (Validation_Exception $e)
			{
				$errors = $e->errors('models');
				$errors = implode('<br />', $errors);
				$output->status = 9;
				$output->title = '错误';
				$output->content = $errors;
			}
			exit(json_encode($output));
		}
		$topicId = (int) $this->request->param('id');
		$topicId <= 0 and $this->request->redirect('forum');
		$oTopic = Model::factory('forum_topic');
		$topic = $oTopic->load($topicId);
		if (!$topic)
		{
			$this->message = array(
				'title' => '错误',
				'content' => '该论坛没有 ID 为 ' . $topicId . ' 的主题。'
			);
			return;
		}
		empty($this->member) and $this->request->redirect('member/login');
		$forum = Model::factory('forum_forum')->load($topic->forum_id);
		if ($topic->status_locked && !Forumfunctions::auth($this->member, $forum->auth, 'lock', $forum->id))
		{
			$this->message = array(
				'title' => '主题已锁定',
				'content' => '您正在回复的主题已被锁定。只有认证会员能够回复。'
			);
			return;
		}
		if (!$forum->status && $this->member->level != 3)
		{
			$this->message = array(
				'title' => '版块已锁定',
				'content' => '该版块已被锁定。只有认证会员才能发表新主题。'
			);
			return;
		}
		if (Forumfunctions::auth($this->member, $forum->auth, 'reply', $forum->id))
		{
			$canPostLinks = Forumfunctions::antispam_can_post_links($this->member, true);
			$quotedPost = '';
			$quotedPostId = (int) $this->request->query('quotepost');
			if ($quotedPostId > 0)
			{
				$quotedData = Model::factory('forum_post')->getContentById($quotedPostId, $topic->id);
				if ($quotedData->id)
				{
					$quotedPost = Forumfunctions::replace_badwords(stripslashes($quotedData->content));
					$quotedPost = '[quote=' . str_replace(array('[', ']'), '', $quotedData->nickname) . ']' . "\n" . $quotedPost . "\n" . '[/quote]' . "\n";
				}
			}
			// 订阅
			$oSubscription = Model::factory('forum_subscription');
			$subscribed = $oSubscription->getNumByTopicAndUser($topic->id, $this->member->user_id);
			$lockTopicChecked = '';
			$subscribeTopicChecked = ( $this->member->user_id && $this->member->auto_subscribe_reply ) ? ' checked="checked"' : '';
			$optionsInput = array();
			if (!$topic->status_locked && Forumfunctions::auth($this->member, $forum->auth, 'lock', $forum->id))
				$optionsInput[] = '<input type="checkbox" name="lock_topic" value="1"' . $lockTopicChecked . '/> 发表后锁定文章';
			if ($this->member && !$subscribed)
				$optionsInput[] = '<input type="checkbox" name="subscribe_topic" value="1"' . $subscribeTopicChecked . '/> 订阅该主题';
			$optionsInput = implode('<br />', $optionsInput);
			$postForm = array(
				'potential_spammer_notice' => $canPostLinks ? '' : '<div class="potential-spammer-notice">您的状态（暂时）为疑似垃圾信息发布者，即您所发表的帖子中的链接将得不到处理（无法点击）。请您谅解！</div>',
				'bbcodeControls' => Forumfunctions::get_bbcode_controls($canPostLinks),
				'smileyControls' => Forumfunctions::get_smiley_controls($this->homeUrl),
				'optionsInput' => $optionsInput,
				'quotedPost' => $quotedPost
			);
			if ($this->config['topic_review_posts'])
			{
				// 主题回复功能
				$posts = Model::factory('forum_post')->findByTopic($topic->id, '', 0, $this->config['topic_review_posts']);
				$posts = array_reverse($posts);
				$viewMorePosts = $topic->replies + 1 > $this->config['topic_review_posts'] ? '<a href="' . $this->homeUrl . 'forum/topic/' . $topic->id . '/" target="_blank">查看更多文章</a>' : '';
				$colornum = 1;
				foreach ($posts as $node)
				{
					$node->post_time = Functions::makeDate($node->post_time);
					$node->post_content = Forumfunctions::markup(Forumfunctions::replace_badwords($node->content), true, true, true, Forumfunctions::antispam_can_post_links($node), $this->homeUrl);
					$node->colornum = $colornum;
					$colornum = $colornum === 1 ? 2 : 1;
				}
			}
		}
		else
		{
			// 用户无权在该版块发表回复
			$this->message = array(
				'title' => '错误',
				'content' => '您无权在该版块发表回复。'
			);
			return;
		}
		$this->template->title = '发表回复 - ' . $this->template->title;
		$this->template->body = View::factory('forum/post_reply')
			->bind('topic', $topic)
			->bind('forum', $forum)
			->bind('form', $postForm)
			->bind('posts', $posts)
			->bind('user', $this->member)
			->bind('viewMorePosts', $viewMorePosts);
	}

	/**
	 * 编辑帖子
	 */
	public function action_edit_post()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '错误';
			$output->content = '您没有编辑帖子的权限！';
			$postId = (int) $this->request->post('pid');
			$oPost = Model::factory('forum_post');
			$post = $oPost->loadWithTopic($postId);
			$oTopic = Model::factory('forum_topic');
			$topic = $oTopic->load($post->topic_id);
			$oForum = Model::factory('forum_forum');
			$forum = $oForum->load($topic->forum_id);
			if (!$post)
			{
				$output->status = 0;
				$output->title = '错误';
				$output->content = 'ID 为 ' . $postId . ' 的帖子不存在，或者已被删除。';
				exit(json_encode($output));
			}
			if ($this->member && ( ( $post->poster_id == $this->member->user_id && (time() - $this->config['edit_post_timeout'] < $post->post_time) ) || Forumfunctions::auth($this->member, $forum->auth, 'edit', $forum->id) ) && Forumfunctions::verify_form())
			{
				$errors = array();
				if (Forumfunctions::post_empty($this->request->post('content')))
					$errors[] = '内容';
				if (count($errors))
				{
					$output->status = 2;
					$output->title = '错误';
					$output->content = '您请求的下列内容不存在或者错误：' . implode('、', $errors) . '。';
					exit(json_encode($output));
				}
				$post->content = addslashes(Forumfunctions::unhtml($this->request->post('content')));
				$post->edited_time = time();
				$post->editor_id = $this->member->user_id;
				$post->editor_name = $this->member->username;
				$post->editor_ip = $_SERVER['REMOTE_ADDR'] ? $_SERVER['REMOTE_ADDR'] : getenv('REMOTE_ADDR');
				unset($post->topic_title, $post->forum_id, $post->auth);
				if ($oPost->update())
				{
					$output->status = 1;
					$output->content = $post->topic_id;
				}
			}
			exit(json_encode($output));
		}
		$postId = (int) $this->request->param('id');
		$postId <= 0 and $this->request->redirect('forum');
		$oPost = Model::factory('forum_post');
		$post = $oPost->loadWithTopic($postId);
		if (!$post)
		{
			$this->message = array(
				'title' => '错误',
				'content' => 'ID 为 ' . $postId . ' 的帖子不存在，或者已被删除。'
			);
			return;
		}
		empty($this->member) and $this->request->redirect('member/login');
		$oTopic = Model::factory('forum_topic');
		$topic = $oTopic->load($post->topic_id);
		$oForum = Model::factory('forum_forum');
		$forum = $oForum->load($topic->forum_id);
		if (( $post->poster_id == $this->member->user_id && (time() - $this->config['edit_post_timeout'] < $post->post_time) ) || Forumfunctions::auth($this->member, $forum->auth, 'edit', $forum->id))
		{
			$canPostLinks = Forumfunctions::antispam_can_post_links($this->member);
			$form = array(
				'potentialSpammerNotice' => $canPostLinks ? '' : '<div>你的状态（暂时）为疑似垃圾信息发布者，即你所发表的帖子中的链接将得不到处理（无法点击）。请你谅解！</div>',
				'bbcodeControls' => Forumfunctions::get_bbcode_controls($canPostLinks),
				'smileyControls' => Forumfunctions::get_smiley_controls($this->homeUrl),
				'allowHtml' => Forumfunctions::auth($this->member, $post->auth, 'html', $post->forum_id) ? true : false,
				'token' => Forumfunctions::generate_token()
			);
			$post->topic_title = '<a href="' . $this->homeUrl . 'forum/topic/' . $post->topic_id . '/">' . $post->topic_title . '</a>';
		}
		else
		{
			$this->message = array(
				'title' => '错误',
				'content' => '您无权编辑ID 为 ' . $post->id . ' 的帖子。'
			);
			return;
		}
		$this->template->title = '编辑回复 - ' . $this->template->title;
		$this->template->body = View::factory('forum/edit_post')
			->bind('form', $form)
			->bind('post', $post)
			->bind('user', $this->member);
	}

	/**
	 * 删除帖子
	 */
	public function action_delete_post()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '错误';
			$output->content = '非法操作！';
			$postId = (int) $this->request->post('pid');
			$oPost = Model::factory('forum_post');
			$post = $oPost->loadWithTopic($postId);
			if (!$post)
			{
				$output->status = 2;
				$output->title = '错误';
				$output->content = 'ID 为 ' . $postId . ' 的帖子不存在，或者已被删除。';
				exit(json_encode($output));
			}
			$oTopic = Model::factory('forum_topic');
			$topic = $oTopic->load($post->topic_id);
			$oForum = Model::factory('forum_forum');
			$forum = $oForum->load($topic->forum_id);
			// 如果用户可以删除帖子
			if ($this->member && ( ($post->poster_id == $this->member->user_id && ( time() - $this->config['edit_post_timeout'] ) <= $post->post_time ) || Forumfunctions::auth($this->member, $forum->auth, 'delete', $forum->id) ))
			{
				// 删除帖子
				if ($oPost->delete())
				{
					// 调整主题的最后一个帖子ID
					if ($topic->last_post_id == $post->id)
					{
						$lastPostId = $oPost->getLastPostId($topic->id);
						$topic->last_post_id = $lastPostId;
					}
					$topic->replies--;
					$oTopic->updateTopic($topic->id, array('replies' => $topic->replies));
					// 更新论坛的帖子数
					$forumUpdate = new stdClass();
					$forumUpdate->id = $forum->id;
					$forumUpdate->posts = ':-1';
					Model::factory('forum_forum')->updateForum($forumUpdate);
					// 调整用户发帖数
					if ($post->poster_id > 0)
					{
						$poster = new stdClass();
						$poster->user_id = $post->poster_id;
						$poster->posts = ':-1';
						Model::factory('forum_member')->updateMember($poster);
					}
					// 调整统计
					Model::factory('forum_stat')->setByName('posts', -1, true);
					$output->status = 1;
					$output->title = '删除成功';
					exit(json_encode($output));
				}
				else
				{
					$output->status = 4;
					$output->title = '删除失败';
					$output->content = 'ID 为 ' . $post->id . ' 的帖子删除失败。';
					exit(json_encode($output));
				}
			}
			else
			{
				$output->status = 3;
				$output->title = '错误';
				$output->content = '您没有足够的权限进行该操作。如有疑问，请联系管理员。';
				exit(json_encode($output));
			}
		}
	}

	/**
	 * 删除主题
	 */
	public function action_delete_topic()
	{
		if ($this->request->is_ajax())
		{
			$output = new stdClass();
			$output->status = 0;
			$output->title = '错误';
			$output->content = '非法操作！';
			$topicId = (int) $this->request->post('pid');
			$oTopic = Model::factory('forum_topic');
			$topic = $oTopic->load($topicId);
			if (!$topic)
			{
				$output->status = 2;
				$output->title = '错误';
				$output->content = 'ID 为 ' . $topicId . ' 的主题不存在，或者已被删除。';
				exit(json_encode($output));
			}
			$oForum = Model::factory('forum_forum');
			$forum = $oForum->load($topic->forum_id);
			// 如果用户可以删除帖子
			if ($this->member && Forumfunctions::auth($this->member, $forum->auth, 'delete', $topic->forum_id))
			{
				// 删除主题
				if ($oTopic->delete())
				{
					$forumUpdate = new stdClass();
					$forumUpdate->id = $forum->id;
					$forum->topics > 0 and $forumUpdate->topics = ':-1';
					$forum->posts > $topic->replies and $forumUpdate->posts = ':-' . ($topic->replies + 1);
					// 调整论坛的最后更新的主题
					if ($forum->last_topic_id == $topic->id)
					{
						$lastTopicId = $oTopic->getLastTopicId($topic->forum_id);
						$forumUpdate->last_topic_id = $lastTopicId ? $lastTopicId : 0;
					}
					// 更新论坛统计
					Model::factory('forum_forum')->updateForum($forumUpdate);
					// 调整用户发帖数
					if ($topic->creater_id > 0)
					{
						$poster = new stdClass();
						$poster->user_id = $topic->creater_id;
						$poster->posts = ':-1';
						Model::factory('forum_member')->updateMember($poster);
					}
					// 调整统计
					Model::factory('forum_stat')->setByName('topics', -1, true);
					$output->status = 1;
					$output->title = '删除成功';
					$output->content = $topic->forum_id;
					exit(json_encode($output));
				}
				else
				{
					$output->status = 4;
					$output->title = '删除失败';
					$output->content = 'ID 为 ' . $topic->id . ' 的帖子删除失败。';
					exit(json_encode($output));
				}
			}
			else
			{
				$output->status = 3;
				$output->title = '错误';
				$output->content = '您没有足够的权限进行该操作。如有疑问，请联系管理员。';
				exit(json_encode($output));
			}
		}
	}

}
