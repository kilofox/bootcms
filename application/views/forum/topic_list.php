<div id="site_content">
	<h3 style="display: inline;"><a href="<?php echo $homeUrl; ?>forum">论坛</a> &raquo; <a href="<?php echo $homeUrl; ?>forum"><?php echo $forum->name; ?></a></h3>
	<?php if ($forum->moderators): ?><p><?php echo $forum->moderators; ?></p><?php endif; ?>
	<div class="form_settings">
		<p style="padding-top: 15px">
			<span class="page" style="float: none; text-align: center;"><?php echo $forum->pageLinks; ?></span>
			<input type="button" value="新主题" class="submit" onclick="window.location.href = '<?php echo $homeUrl; ?>forum/post_topic/<?php echo $forum->id; ?>/'" style="float:right;" />
		</p>
	</div>
	<table style="width:100%; border-spacing:0;">
		<tr>
			<th></th>
			<th>主题</th>
			<th style="text-align: center;">回复</th>
			<th style="text-align: center;">浏览</th>
			<th>最新帖子</th>
		</tr>
		<?php if ($topics): ?>
			<?php foreach ($topics as $node): ?>
				<tr>
					<td style="text-align: center;"><img src="<?php echo $homeUrl; ?>assets/images/forum/<?php echo $node->topic_icon; ?>" alt="<?php echo $node->topic_status; ?>" /></td>
					<td>
						<div><?php echo $node->topic_name; ?></div>
						<div>&mdash; <?php echo $node->author; ?></div>
					</td>
					<td style="text-align: center;"><?php echo $node->replies; ?></td>
					<td style="text-align: center;"><?php echo $node->views; ?></td>
					<td><?php echo $node->last_post; ?></td>
				</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr>
				<td colspan="5">该版块目前没有帖子。</td>
			</tr>
		<?php endif; ?>
	</table>
	<div class="form_settings">
		<p>
			<span class="page" style="float: none; text-align: center;"><?php echo $forum->pageLinks; ?></span>
			<input type="button" value="新主题" class="submit" onclick="window.location.href = '<?php echo $homeUrl; ?>forum/post_topic/<?php echo $forum->id; ?>/'" style="float:right;" />
		</p>
	</div>
</div>