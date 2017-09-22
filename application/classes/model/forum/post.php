<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 论坛帖子模型
 *
 * @package	BootCMS
 * @category	论坛/模型
 * @author		Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class Model_Forum_Post extends Model {

	private $_values = NULL;
	private $_loaded = false;

	/**
	 * 创建并返回一个新的模型对象
	 *
	 * @return 对象
	 */
	public static function factory($name)
	{
		return parent::factory($name);
	}

	/**
	 * 根据主键加载数据，并返回对象
	 *
	 * @return 对象
	 */
	public function load($id = 0)
	{
		if (is_numeric($id) && $id > 0)
		{
			$sql = "SELECT * FROM `{$this->_db->tablePrefix}bb_posts` WHERE `id` = $id";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 根据主键加载数据，并返回对象
	 *
	 * @return 对象
	 */
	public function loadWithTopic($id = 0)
	{
		if (is_numeric($id) && $id > 0)
		{
			$sql = "SELECT p.*, t.id AS topic_id, t.forum_id, t.topic_title, f.auth FROM `{$this->_db->tablePrefix}bb_posts` p LEFT JOIN `" . $this->_db->tablePrefix . "bb_topics` t ON p.topic_id = t.id LEFT JOIN `" . $this->_db->tablePrefix . "bb_forums` f ON t.forum_id = f.id WHERE p.id = $id";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 创建新节点
	 *
	 * @param array 键值对
	 * @return mixed 插入的数据ID 或 false
	 */
	public function create($data)
	{
		if (is_object($data))
		{
			// 对象转数组（将来将 Validation 改写成操作对象后，就可以省略此步）
			$values = array();
			foreach ($data as $key => $val)
			{
				$values[$key] = $val;
			}
			// 验证数据
			$validation = self::getValidation($values);
			if (!$validation->check())
			{
				throw new Validation_Exception('user', $validation);
			}
			return $this->_db->insert('bb_posts', $values);
		}
		return false;
	}

	/**
	 * 更新节点
	 *
	 * @return 执行结果
	 */
	public function update()
	{
		if ($this->_loaded)
		{
			// 对象转数组（将来将 Validation 改写成操作对象后，就可以省略此步）
			$values = array();
			foreach ($this->_values as $key => $val)
			{
				$values[$key] = $val;
			}
			// 验证数据
			$validation = self::getValidation($values);
			if (!$validation->check())
			{
				throw new Validation_Exception('user', $validation);
			}
			$where = "`id` = {$this->_values->id}";
			return $this->_db->update('bb_posts', $this->_values, $where);
		}
		return false;
	}

	/**
	 * 删除节点
	 *
	 * @return mixed 执行结果
	 */
	public function delete()
	{
		if ($this->_loaded)
		{
			$where = "`id` = {$this->_values->id}";
			return $this->_db->delete('bb_posts', $where);
		}
		return false;
	}

	/**
	 * 数据验证
	 *
	 * @param array $values
	 * @return Validation
	 */
	public static function getValidation($values)
	{
		return Validation::factory($values)
				->rule('topic_id', 'not_empty')
				->rule('topic_id', 'digit')
				->rule('poster_id', 'not_empty')
				->rule('poster_id', 'digit')
				->rule('content', 'not_empty');
	}

	/**
	 * 根据主题ID取得帖子信息
	 *
	 * @param integer 主题ID
	 * @param integer 额外字段
	 * @param integer 开始
	 * @param integer 数量
	 * @return array 数据
	 */
	public function findByTopic($topicId = 0, $select = '', $start = 0, $limit = 10)
	{
		$result = array();
		if ($topicId)
		{
			$sql = "SELECT p.id, p.poster_id, p.poster_ip, p.content, p.post_time, p.enable_bbcode, p.enable_smilies, p.enable_html, p.edited_time, p.editor_id, p.editor_name,
			m.nickname, m.level, m.posts, m.regdate" . $select . "
			FROM `{$this->_db->tablePrefix}bb_posts` p
			LEFT JOIN `{$this->_db->tablePrefix}bb_members` m ON p.poster_id = m.user_id
			WHERE p.topic_id = $topicId ORDER BY p.post_time ASC LIMIT $start, $limit";
			$result = $this->_db->selectArray($sql);
		}
		return $result;
	}

	/**
	 * 根据主题ID取得第一个帖子的ID
	 *
	 * @param integer 主题ID
	 * @return object 帖子ID
	 */
	public function getFirstPostId($topicId = 0)
	{
		$sql = "SELECT id FROM `{$this->_db->tablePrefix}bb_posts` WHERE topic_id = $topicId ORDER BY post_time ASC LIMIT 1";
		$post = $this->_db->select($sql);
		return $post ? $post->id : 0;
	}

	/**
	 * 根据主题ID取得最后一个帖子的ID
	 *
	 * @param integer 主题ID
	 * @return object 帖子ID
	 */
	public function getLastPostId($topicId = 0)
	{
		$sql = "SELECT id FROM `{$this->_db->tablePrefix}bb_posts` WHERE topic_id = $topicId ORDER BY post_time DESC LIMIT 1";
		$post = $this->_db->select($sql);
		return $post ? $post->id : 0;
	}

	/**
	 * 根据主题ID取得最后一个帖子的ID
	 *
	 * @param integer 帖子ID
	 * @param integer 主题ID
	 * @return object 帖子内容
	 */
	public function getContentById($postId = 0, $topicId = 0)
	{
		$sql = "SELECT p.id, p.content, u.nickname FROM `{$this->_db->tablePrefix}bb_posts` p LEFT JOIN `{$this->_db->tablePrefix}bb_members` u ON p.poster_id = u.user_id WHERE p.id = $postId AND p.topic_id = '$topicId'";
		$post = $this->_db->select($sql);
		return $post;
	}

}
