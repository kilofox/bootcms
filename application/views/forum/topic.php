<div id="site_content">
	<h3 style="display: inline;"><a href="<?php echo $homeUrl; ?>forum">论坛</a> &raquo; <a href="<?php echo $homeUrl; ?>forum/forum/<?php echo $topic->forum_id; ?>/"><?php echo $topic->forum_name; ?></a> &raquo; <a href="<?php echo $homeUrl; ?>forum/topic/<?php echo $topic->id; ?>/"><?php echo $topic->topic_title; ?></a></h3>
	<?php if ($topic->forumModerators): ?><p><?php echo $topic->forumModerators; ?></p><?php endif; ?>
	<div class="form_settings">
		<p style="padding-top:15px;">
			<span class="page" style="float: none; text-align: center;"><?php echo $topic->pageLinks; ?></span>
			<span style="float:right;"><?php echo $topic->replyLink; ?></span>
		</p>
	</div>
	<table style="width:100%; border-spacing:0;" id="firstpost">
		<tr>
			<th style="text-align: center; width: 170px;">作者</th>
			<th>帖子</th>
		</tr>
		<?php if ($page == 1): ?>
			<tr class="tr<?php echo $topic->colornum; ?>">
				<td style="text-align: center; vertical-align: top;">
					<a href="<?php echo $homeUrl; ?>member/profile/<?php echo $topic->user_id; ?>/"><img src="<?php echo $topic->avatar; ?>" /></a><br />
					<a href="<?php echo $homeUrl; ?>member/profile/<?php echo $topic->user_id; ?>/"><?php echo $topic->nickname; ?></a><br />
					<?php echo $topic->level; ?><br />
					注册于：<?php echo $topic->regdate; ?><br />
					发帖数：<?php echo $topic->posts; ?>
				</td>
				<td style="vertical-align: top;">
					<h3 style="border-bottom: 1px dashed #ffffff; padding: 5px 0 15px;"><?php echo $topic->topic_title; ?></h3>
					<a href="<?php echo $homeUrl; ?>forum/topic/<?php echo $topic->id; ?>/#firstpost" rel="nofollow">#楼主</a> <?php echo $topic->created_time; ?>
					<span style="float:right;"><?php echo $topic->postLinks; ?></span>
					<p style="padding:16px 0;">
						<?php echo $topic->topic_content; ?>
					</p>
					<p>
						<?php if ($topic->edit_info): ?>
							<em><?php echo $topic->edit_info; ?></em>
						<?php endif; ?>
						<?php echo $topic->signature; ?><br />
						<?php echo $topic->creater_ip; ?>
					</p>
				</td>
			</tr>
		<?php endif; ?>
		<?php foreach ($posts as $k => $node): ?>
			<tr class="tr<?php echo $node->colornum; ?>" id="post<?php echo $node->id; ?>">
				<td style="text-align: center; vertical-align: top;"<?php if ($k + 1 === count($posts)): ?> id="lastpost"<?php endif; ?>>
					<a href="<?php echo $homeUrl; ?>member/profile/<?php echo $node->poster_id; ?>/"><img src="<?php echo $node->avatar; ?>" /></a><br />
					<a href="<?php echo $homeUrl; ?>member/profile/<?php echo $node->poster_id; ?>/"><?php echo $node->nickname; ?></a><br />
					<?php echo $node->level; ?><br />
					注册于：<?php echo $node->regdate; ?><br />
					发帖数：<?php echo $node->posts; ?>
				</td>
				<td style="vertical-align: top;">
					<a href="#post<?php echo $node->id; ?>" rel="nofollow">#<?php echo $node->i; ?>楼</a> <?php echo $node->post_time; ?>
					<span style="float:right;"><?php echo $node->postLinks; ?></span>
					<p style="padding:16px 0;">
						<?php echo $node->post_content; ?>
					</p>
					<p>
						<?php if ($node->edit_info): ?>
							<em><?php echo $node->edit_info; ?></em>
						<?php endif; ?>
						<?php echo $node->signature; ?><br />
						<?php echo $node->poster_ip; ?>
					</p>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
	<div class="form_settings">
		<p>
			<span class="page" style="float: none; text-align: center;"><?php echo $topic->pageLinks; ?></span>
			<span style="float:right;"><?php echo $topic->replyLink; ?></span>
		</p>
	</div>
	<p><?php echo $topic->actionLinks; ?></p>
	<?php if ($quickReply): ?>
		<h1>快速回复</h1>
		<form id="post_form">
			<div class="form_settings">
				<p>
					<span>回复内容</span>
					<textarea rows="5" cols="60" name="content" accesskey="q"></textarea>
				</p>
				<p style="padding-top: 15px">
					<span>
						&nbsp;
						<input type="hidden" name="enable_bbcode" value="1" />
						<input type="hidden" name="enable_smilies" value="1" />
						<input type="hidden" name="enable_sig" value="1" />
						<input type="hidden" name="tid" value="<?php echo $topic->id; ?>" />
						<input type="hidden" name="subscribe_topic" value="<?php echo $subscribe_topic; ?>" />
					</span>
					<input class="submit" type="submit" name="submit" value="回复" accesskey="s" />
				</p>
			</div>
		</form>
	<?php endif; ?>
</div>
<script>
	$(function() {
		var homeUrl = '<?php echo $homeUrl; ?>';
		var topicId = '<?php echo $topic->id; ?>';
		$('#post_form').submit(function() {
			if (!$('textarea[name="content"]').val()) {
				$('textarea[name="content"]').focus();
				return false;
			}
			$.post(homeUrl + 'forum/post_reply/', $(this).serialize(), function(r) {
				$('input[type="submit"]').prop('disabled', true);
				if (r.status === 1) {
					$('input[type="submit"]').prop('disabled', false);
					window.location.href = homeUrl + 'forum/topic/' + topicId + '/?page=-1#lastpost';
				} else {
					$('input[type="submit"]').prop('disabled', false);
					showMsg(r.title, r.content);
				}
			}, 'json');
			return false;
		});
		$('a[data-pid]').click(function() {
			if (confirm('您确定要删除这个帖子吗？')) {
				$.post(homeUrl + 'forum/delete_post', {
					"pid": $(this).attr('data-pid')
				},
					function(r) {
						if (r.status === 1) {
							window.location.reload();
						} else {
							showMsg(r.title, r.content);
						}
					}, 'json');
				return false;
			}
		});
		$('a[data-tid]').click(function() {
			if (confirm('您确定要删除这个主题帖吗？')) {
				$.post(homeUrl + 'forum/delete_topic', {
					"pid": $(this).attr('data-tid')
				}, function(r) {
					if (r.status === 1) {
						window.location.href = homeUrl + 'forum/forum/' + r.content + '/';
					} else {
						showMsg(r.title, r.content);
					}
				}, 'json');
				return false;
			}
		});
		$('a[data-sticky]').click(function() {
			var that = $(this);
			$.post(homeUrl + 'forum/edit_topic', {
				"tid": $(this).attr('data-sticky'),
				"act": 'sticky'
			}, function(r) {
				if (r.status === 1) {
					showMsg('操作成功', r.content);
					that.text(r.stickySet === 1 ? '取消置顶' : '置顶');
				} else {
					showMsg(r.title, r.content);
				}
			}, 'json');
			return false;
		});
	});
</script>