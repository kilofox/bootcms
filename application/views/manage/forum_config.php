<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">常规设置</span>
</header>
<article class="module width_full">
	<form id="setting_form">
		<header><h3>一般设置</h3></header>
		<div class="module_content field">
			<ul>
				<li>
					<label>论坛名称</label>
					<input type="text" name="board_name" value="<?php echo $config['board_name']; ?>" class="input"/>
				</li>
				<li>
					<label>论坛描述</label>
					<input type="text" name="board_descr" value="<?php echo $config['board_descr']; ?>" class="input"/>
				</li>
				<li>
					<label>论坛关键词</label>
					<input type="text" name="board_keywords" value="<?php echo $config['board_keywords']; ?>" class="input"/>
				</li>
				<li>
					<label>关闭论坛</label>
					<input type="checkbox" name="board_closed" value="1"<?php if ($config['board_closed'] == 1): ?> checked="checked"<?php endif; ?>/> 是
				</li>
				<li>
					<label>论坛关闭原因</label>
					<textarea name="board_closed_reason" class="textarea"/><?php echo $config['board_closed_reason']; ?></textarea>
				</li>
				<li>
					<label>管理员 E-mail 地址</label>
					<input type="text" name="admin_email" value="<?php echo $config['admin_email']; ?>"/>
				</li>
			</ul>
		</div>
		<header><h3>附加特性</h3></header>
		<div class="module_content field">
			<ul>
				<li>
					<label>启用论坛统计</label>
					<input type="checkbox" name="enable_forum_stats_box" value="1"<?php if ($config['enable_forum_stats_box'] == 1): ?> checked="checked"<?php endif; ?>/> 是
				</li>
				<li>
					<label>启用详细在线列表</label>
					<input type="checkbox" name="enable_detailed_online_list" value="1"<?php if ($config['enable_detailed_online_list'] == 1): ?> checked="checked"<?php endif; ?>/> 是
				</li>
				<li>
					<label>启用会员列表</label>
					<input type="checkbox" name="enable_memberlist" value="1"<?php if ($config['enable_memberlist'] == 1): ?> checked="checked"<?php endif; ?>/> 是
				</li>
				<li>
					<label>启用快速回复</label>
					<input type="checkbox" name="enable_quickreply" value="1"<?php if ($config['enable_quickreply'] == 1): ?> checked="checked"<?php endif; ?>/> 是
				</li>
				<li>
					<label>启用版主列表</label>
					<input type="checkbox" name="enable_stafflist" value="1"<?php if ($config['enable_stafflist'] == 1): ?> checked="checked"<?php endif; ?>/> 是
				</li>
				<li>
					<label>启用统计页面</label>
					<input type="checkbox" name="enable_stats" value="1"<?php if ($config['enable_stats'] == 1): ?> checked="checked"<?php endif; ?>/> 是
				</li>
				<li>
					<label>隐藏所有个性签名</label>
					<input type="checkbox" name="hide_signatures" value="1"<?php if ($config['hide_signatures'] == 1): ?> checked="checked"<?php endif; ?>/> 是
				</li>
			</ul>
		</div>
		<header><h3>页面计数</h3></header>
		<div class="module_content field">
			<ul>
				<li>
					<label>活跃主题数限制</label>
					<input type="text" name="active_topics_count" value="<?php echo $config['active_topics_count']; ?>"/>
				</li>
				<li>
					<label>每页主题数</label>
					<input type="text" name="topics_per_page" value="<?php echo $config['topics_per_page']; ?>"/>
				</li>
				<li>
					<label>每页文章数</label>
					<input type="text" name="posts_per_page" value="<?php echo $config['posts_per_page']; ?>"/>
				</li>
				<li>
					<label>主题评论数计数</label>
					<input type="text" name="topic_review_posts" value="<?php echo $config['topic_review_posts']; ?>"/>
				</li>
				<li>
					<label>每页会员数</label>
					<input type="text" name="members_per_page" value="<?php echo $config['members_per_page']; ?>"/>
				</li>
			</ul>
		</div>
		<header><h3>高级设置</h3></header>
		<div class="module_content field">
			<ul>
				<li>
					<label>发帖时间间隔（秒）</label>
					<input type="text" name="flood_interval" value="<?php echo $config['flood_interval']; ?>"/>
				</li>
				<li>
					<label>显示编辑信息最短时间间隔（秒）</label>
					<input type="text" name="show_edited_message_timeout" value="<?php echo $config['show_edited_message_timeout']; ?>"/> 如果文章发表后在指定的秒数内进行编辑，则不显示编辑注释，否则会在帖子底部显示一条编辑信息。
				</li>
				<li>
					<label>新帖时间（分钟）</label>
					<input type="text" name="new_post_minutes" value="<?php echo $config['new_post_minutes']; ?>"/> 超过设定时间后，主题将不再显示为新帖。
				</li>
				<li>
					<label>编辑帖子超时时间</label>
					<input type="text" name="edit_post_timeout" value="<?php echo $config['edit_post_timeout']; ?>"/> 用户只能在帖子发表后指定的秒数内编辑他的帖子。
				</li>
			</ul>
		</div>
		<footer>
			<div class="submit_link">
				<input type="submit" value="更新" class="alt_btn"/>
				<input type="reset" value="重置"/>
			</div>
		</footer>
	</form>
</article>
<script>
	$(function() {
		var homeUrl = '<?php echo $homeUrl; ?>',
			refresh = false;
		$('#setting_form').submit(function() {
			$.post(homeUrl + 'manage3/config', $(this).serialize(), function(r) {
				if (r.status === 1)
					refresh = true;
				showMsg(r.title, r.content);
			}, 'json');
			return false;
		});
		$('body').on('click', '.close-me', function() {
			if (refresh)
				window.location.reload();
		});
	});
</script>