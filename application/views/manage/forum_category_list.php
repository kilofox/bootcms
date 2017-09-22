<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">管理分类</span>
</header>
<article class="module width_3_quarter">
	<header><h3 class="tabs_involved">管理分类</h3></header>
	<div class="tab_container">
		<table class="tablesorter" cellspacing="0">
			<thead>
				<tr>
					<th>分类名称</th>
					<th>排序编号</th>
					<th>操作</th>
				</tr>
			</thead>
			<tbody id="dataList"></tbody>
		</table>
	</div>
	<footer>
		<div class="submit_link">
			<input type="submit" name="sort_btn" value="保存" class="alt_btn" />
		</div>
	</footer>
</article>
<article class="module width_quarter" data-area="region_edit">
	<header><h3>创建新分类</h3></header>
	<form id="new_form">
		<div class="message_list">
			<div class="module_content">
				<label>分类名称</label>
				<input type="text" name="category_name" value="" />
			</div>
		</div>
		<footer>
			<div class="submit_link">
				<input type="submit" name="create_btn" value="创建" class="alt_btn" />
			</div>
		</footer>
	</form>
</article>
<script>
	var homeUrl = '<?php echo $homeUrl; ?>',
		refresh = false,
		from = '<?php echo Request::current()->action(); ?>';
	var requestData = function() {
		$.post(homeUrl + 'manage3/list_categories', {
			"page": 0
		}, function(r) {
			$('#dataList').html(r.data);
			$('#page').html(r.pagination);
		}, 'json');
	};
	$(function() {
		requestData();
		$('#dataList').on('click', 'a[data-use="edit"]', function() {
			window.location.href = homeUrl + 'manage3/edit_category/' + $(this).parents('tr[data-cid]').attr('data-cid');
		});
		$('#dataList').on('click', 'a[data-use="del"]', function() {
			if (confirm('您确定要删除该分类吗？')) {
				$.post(homeUrl + 'manage3/delete_category', {
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
				requestData();
		});
		$('input[name="sort_btn"').click(function() {
			var cateSort = '';
			$('input[name="sort"]').each(function() {
				cateSort += $(this).parents('tr[data-cid]').attr('data-cid') + '|' + $(this).val() + ',';
			});
			$.post(homeUrl + 'manage3/sort_categories', {
				"cate_sort": cateSort,
				"from": from
			}, function(r) {
				if (r.status != 0)
					requestData();
				showMsg(r.title, r.content);
			}, 'json');
		});
		$('#new_form').submit(function() {
			$.post(homeUrl + 'manage3/create_category', $(this).serialize(), function(r) {
				if (r.status === 1)
					requestData();
				showMsg(r.title, r.content);
			}, 'json');
			return false;
		});
	});
</script>