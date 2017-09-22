<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">产品管理</span>
</header>
<article class="module width_full">
	<header><h3 class="tabs_involved">产品管理</h3></header>
	<div class="tab_container">
		<table class="tablesorter" cellspacing="0">
			<thead>
				<tr>
					<th>产品名称</th>
					<th>所属分类</th>
					<th>显示顺序</th>
					<th>添加时间</th>
					<th>操作</th>
				</tr>
			</thead>
			<tbody id="dataList"></tbody>
			<tr>
				<td colspan="5" class="tar"><span id="page"></span></td>
			</tr>
		</table>
	</div>
	<footer>
		<div class="submit_link"><input type="submit" id="sort" value="排序" class="alt_btn" /></div>
	</footer>
</article>
<script>
	var homeUrl = '<?php echo $homeUrl; ?>',
		refresh = false,
		from = '<?php echo Request::current()->action(); ?>';
	var requestData = function() {
		$.post(homeUrl + 'manage2/list_products', {
			"page": 0
		}, function(r) {
			$('#dataList').html(r.data);
			$('#page').html(r.pagination);
		}, 'json');
	};
	$(function() {
		requestData();
		$('#dataList').on('click', 'input[data-edit]', function() {
			window.location.href = homeUrl + 'manage2/edit_product/' + $(this).attr('data-edit');
		});
		$('#dataList').on('click', 'input[data-delete]', function() {
			if (confirm('您确定要删除该产品吗？')) {
				$.post(homeUrl + 'manage2/delete_product', {
					"pid": $(this).attr('data-delete'),
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
		$('#sort').click(function() {
			var prodSort = '';
			$('input[data-sortp]').each(function() {
				prodSort += $(this).attr('data-sortp') + '|' + $(this).val() + ',';
			});
			$.post(homeUrl + 'manage2/sort_products', {
				"prod_sort": prodSort,
				"from": from
			}, function(r) {
				if (r.status != 0)
					requestData();
				showMsg(r.title, r.content);
			}, 'json');
		});
		$('#page').on('click', 'a', function() {
			page = $(this).text();
			var curPage = parseInt($('#page a.active').text());
			if (page == '上一页')
				page = curPage - 1;
			if (page == '下一页')
				page = curPage + 1;
			if (page != curPage)
				requestData();
		});
	});
</script>