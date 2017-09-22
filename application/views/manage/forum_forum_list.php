<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">管理版块</span>
</header>
<article class="module width_full">
	<header><h3 class="tabs_involved">管理版块</h3></header>
	<div class="tab_container">
		<table class="tablesorter" cellspacing="0">
			<thead>
				<tr>
					<th>版块名称</th>
					<th>排序编号</th>
					<th>操作</th>
				</tr>
			</thead>
			<?php
			$catId = 0;
			foreach ($forums as $node):
				if ($node->cat_id <> $catId):
					?>
					<tr data-cid="<?php echo $node->cat_id; ?>">
						<td colspan="3" style="font-weight:bold;font-style:italic"><?php echo $node->cat_name; ?></td>
					</tr>
				<?php endif; ?>
				<tr data-fid="<?php echo $node->id; ?>">
					<td><?php echo $node->name; ?></td>
					<td><input type="text" name="sort" value="<?php echo $node->sort_id; ?>" /></td>
					<td><a title="编辑" data-use="edit">编辑</a> <a title="删除" data-use="del">删除</a></td>
				</tr>
				<?php
				$catId = $node->cat_id;
			endforeach;
			?>
		</table>
	</div>
	<footer>
		<div class="submit_link">
			<input type="submit" name="sort_btn" value="保存" class="alt_btn" />
		</div>
	</footer>
</article>
<script>
	var homeUrl = '<?php echo $homeUrl; ?>',
		refresh = false,
		from = '<?php echo Request::current()->action(); ?>';
	$(function() {
		$('a[data-use="edit"]').click(function() {
			window.location.href = homeUrl + 'manage3/edit_forum/' + $(this).parents('tr[data-fid]').attr('data-fid');
		});
		$('a[data-use="del"]').click(function() {
			if (confirm('您确定要删除该版块吗？')) {
				$.post(homeUrl + 'manage3/delete_forum', {
					"pid": $(this).parents('tr[data-cid]').attr('data-cid'),
					"from": from
				}, function(r) {
					showMsg(r.title, r.content);
					refresh = true;
				}, 'json');
			}
		});
		$('body').on('click', '.close-me', function() {
			if (refresh)
				window.location.reload();
		});
		$('input[name="sort_btn"').click(function() {
			var cateSort = '';
			$('input[name="sort"]').each(function() {
				cateSort += $(this).parents('tr[data-fid]').attr('data-fid') + '|' + $(this).val() + ',';
			});
			$.post(homeUrl + 'manage3/sort_forums', {
				"cate_sort": cateSort,
				"from": from
			}, function(r) {
				if (r.status === 1)
					window.location.reload();
				else
					showMsg(r.title, r.content);
			}, 'json');
		});
	});
</script>