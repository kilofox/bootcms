<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">产品分类管理</span>
</header>
<article class="module width_3_quarter">
	<header><h3 class="tabs_involved">产品分类管理</h3></header>
	<div class="tab_container">
		<table class="tablesorter" cellspacing="0">
			<thead>
				<tr>
					<th>分类名称</th>
					<th>显示顺序</th>
					<th>操作</th>
				</tr>
			</thead>
			<tbody id="dataList"></tbody>
		</table>
	</div>
	<footer>
		<div class="submit_link">
			<input type="submit" id="category_sort_btn" value="排序" class="alt_btn" />
		</div>
	</footer>
</article>
<article class="module width_quarter" data-area="region_edit">
	<header><h3>创建产品分类</h3></header>
	<div class="message_list">
		<div class="module_content">
			<label>分类名称</label>
			<input type="text" id="category_name" value="" />
		</div>
	</div>
	<footer>
		<div class="submit_link">
			<input type="submit" id="category_create_btn" value="创建" class="alt_btn" />
		</div>
	</footer>
</article>
<script>
	var homeUrl = '<?php echo $homeUrl; ?>',
		refresh = false,
		from = '<?php echo Request::current()->action(); ?>';
	function requestData() {
		$.post(homeUrl + 'manage2/list_product_categories', {
			"page": 0
		}, function(r) {
			$('#dataList').html(r.data);
		}, 'json');
	}
	$(function() {
		requestData();
		$('#dataList').on('click', 'input[data-edit]', function() {
			window.location.href = homeUrl + 'manage2/edit_product_category/' + $(this).attr('data-edit');
		});
		$('#dataList').on('click', 'input[data-delete]', function() {
			if (confirm('您确定要删除该分类吗？')) {
				$.post(homeUrl + 'manage2/delete_product_category', {
					"cid": $(this).attr('data-delete'),
					"from": from
				}, function(r) {
					showMsg(r.title, r.content);
					refresh = true;
				}, 'json');
			}
		});
		$('#category_create_btn').click(function() {
			$.post(homeUrl + 'manage2/create_product_category', {
				"category_name": $('#category_name').val()
			}, function(r) {
				if (r.status == 1)
					requestData();
				showMsg(r.title, r.content);
			}, 'json');
		});
		$('#category_sort_btn').click(function() {
			var cateSort = '';
			$('input[data-sort]').each(function() {
				cateSort += $(this).attr('data-sort') + '|' + $(this).val() + ',';
			});
			$.post(homeUrl + 'manage2/sort_product_categories', {
				"cate_sort": cateSort
			}, function(r) {
				if (r.status == 1)
					requestData();
				showMsg(r.title, r.content);
			}, 'json');
		});
		$('body').on('click', '.close-me', function() {
			if (refresh)
				requestData();
		});
	});
</script>