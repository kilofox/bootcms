<div id="site_content">
	<div id="content">
		<table style="width:100%; border-spacing:0;">
			<tr>
				<td colspan="2" class="forumcat">&raquo; 主题回顾</td>
			</tr>
			<tr>
				<th>作者</th>
				<th>发表</th>
			</tr>
			<?php foreach ($posts as $node): ?>
				<tr class="tr<?php echo $node->colornum; ?>">
					<td class="postername">
						<div class="posternamecontainer"><?php echo $node->poster_name; ?></div>
					</td>
					<td class="postcontent" rowspan="2">
						<div class="post"><?php echo $node->post_content; ?></div>
					</td>
				</tr>
				<tr class="tr<?php echo $node->colornum; ?>">
					<td class="posterinfo">
						<div class="field"><?php echo $node->post_date; ?></div>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
		<p><?php echo $viewMorePosts; ?></p>
	</div>
</div>