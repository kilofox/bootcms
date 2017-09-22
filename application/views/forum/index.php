<div id="site_content">
	<?php foreach ($categories as $cat): ?>
		<?php if ($cat['forums']): ?>
			<table style="width:100%; border-spacing:0;">
				<tr>
					<td colspan="5"><a href="<?php echo $cat['cat_url']; ?>" id="<?php echo $cat['cat_anchor']; ?>" rel="nofollow">&raquo;</a> <?php echo $cat['cat_name']; ?></td>
				</tr>
				<tr>
					<th></th>
					<th>论坛</th>
					<th style="text-align: center;">主题</th>
					<th style="text-align: center;">帖子</th>
					<th>最后回复时间</th>
				</tr>
				<?php foreach ($cat['forums'] as $node): ?>
					<tr>
						<td style="text-align: center;"><img src="<?php echo $homeUrl; ?>assets/images/forum/<?php echo $node->icon; ?>" alt="<?php echo $node->status; ?>"/></td>
						<td><?php echo $node->name; ?><div><?php echo $node->descr; ?></div></td>
						<td style="text-align: center;"><?php echo $node->topics; ?></td>
						<td style="text-align: center;"><?php echo $node->posts; ?></td>
						<td><?php echo $node->last_post_time; ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
		<?php endif; ?>
	<?php endforeach; ?>
	<table style="width:100%; border-spacing:0;">
		<tr>
			<th><?php echo $config['board_name']; ?> 论坛统计</th>
		</tr>
		<tr>
			<td>该论坛有帖子 <?php echo $stats['posts']; ?> 篇，主题 <?php echo $stats['topics']; ?> 个，共 <?php echo $stats['members']; ?> 位注册会员。<br />
				<?php echo $stats['latest_member']; ?></td>
		</tr>
	</table>
</div>