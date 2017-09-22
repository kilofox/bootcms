<?php

defined('SYSPATH') || exit('Access Denied.');

/**
 * 论坛主题模型。
 *
 * @package	BootCMS
 * @category	论坛/模型
 * @author		Tinsh
 * @copyright (C) 2005-2016 Kilofox Studio
 */
class Model_Forum_Topic extends Model {

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
			$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "bb_topics` WHERE `id` = $id";
			$this->_values = $this->_db->select($sql);
			$this->_loaded = true;
		}
		return $this->_values;
	}

	/**
	 * 创建新主题
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
			return $this->_db->insert('bb_topics', $values);
		}
		return false;
	}

	/**
	 * 更新主题
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
			return $this->_db->update('bb_topics', $this->_values, $where);
		}
		return false;
	}

	/**
	 * 删除主题
	 * @return mixed 执行结果
	 */
	public function delete()
	{
		if ($this->_loaded)
		{
			$where = "`id` = {$this->_values->id}";
			return $this->_db->delete('bb_topics', $where);
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
				->rule('forum_id', 'not_empty')
				->rule('forum_id', 'digit')
				->rule('topic_title', 'not_empty');
	}

	/**
	 * 取得所有主题
	 */
	public function findAll($order_by = '')
	{
		$result = array();
		$order_by = $order_by ? $order_by : 'created DESC';
		$sql = "SELECT * FROM `" . $this->_db->tablePrefix . "bb_topics` WHERE `type` = 1 ORDER BY $order_by";
		$result = $this->_db->selectArray($sql);
		return $result;
	}

	/**
	 * 更新主题
	 *
	 * @return 执行结果
	 */
	public function updateTopic($id = 0, $values = array())
	{
		if (is_array($values))
		{
			$where = "`id` = $id";
			return $this->_db->update('bb_topics', $values, $where);
		}
		return false;
	}

	/**
	 * 根据论坛ID取得主题相关信息
	 *
	 * @param integer 论坛ID
	 * @param integer 开始
	 * @param integer 数量
	 * @return array 数据
	 */
	public function getTopicsByForum($fid = 0, $start = 0, $limit = 10)
	{
		$result = array();
		if ($fid > 0 && $limit > 0)
		{
			$sql = "SELECT t.id, t.topic_title, t.last_post_id, t.last_post_time, t.last_poster_id, t.last_poster_name, t.replies, t.views, t.status_locked, t.status_sticky,
				m.user_id, m.nickname, m.level
				FROM {$this->_db->tablePrefix}bb_topics t
				LEFT JOIN `{$this->_db->tablePrefix}bb_members` m ON t.creater_id = m.user_id
				WHERE t.forum_id = $fid ORDER BY t.status_sticky DESC, t.last_post_time DESC LIMIT $start, $limit";
			$result = $this->_db->selectArray($sql);
		}
		return $result;
	}

	/**
	 * 取得主题信息及其所在论坛信息
	 *
	 * @param integer 论坛ID
	 * @param integer 开始
	 * @param integer 数量
	 * @return array 数据
	 */
	public function findWithForum($topicId = 0, $start = 0, $limit = 10)
	{
		$result = NULL;
		if ($topicId)
		{
			$sql = "SELECT t.id, t.topic_title, t.topic_content, t.creater_id, t.created_time, t.creater_ip, t.edited_time, t.editor_id, t.editor_name, t.status_locked, t.status_sticky, t.replies, t.forum_id, t.last_post_id, t.views,
				f.id AS forum_id, f.name AS forum_name, f.status AS forum_status, f.auth, f.hide_mods_list,
				m.user_id, m.nickname, m.regdate, m.level, m.posts, m.avatar, m.hide_signatures, m.signature
				FROM `{$this->_db->tablePrefix}bb_topics` t
				LEFT JOIN `{$this->_db->tablePrefix}bb_forums` f ON f.id = t.forum_id
				LEFT JOIN `{$this->_db->tablePrefix}bb_members` m ON t.creater_id = m.user_id
				WHERE t.id = $topicId";
			$result = $this->_db->select($sql);
		}
		return $result;
	}

	/**
	 * 根据论坛ID取得最后一个主题的ID
	 *
	 * @param integer 论坛ID
	 * @return object 主题ID
	 */
	public function getLastTopicId($forumId = 0)
	{
		$sql = "SELECT t.id FROM `" . $this->_db->tablePrefix . "bb_posts` p LEFT JOIN  `" . $this->_db->tablePrefix . "bb_topics` t ON p.topic_id = t.id WHERE t.forum_id = $forumId ORDER BY p.post_time DESC LIMIT 1";
		$post = $this->_db->select($sql);
		return $post ? $post->id : 0;
	}

}
