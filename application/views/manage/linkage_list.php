<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">内容管理</span>
</header>
<article class="module width_full">
	<header><h3 class="tabs_involved">内容管理</h3></header>
	<div class="tab_container">
		<table class="tablesorter" cellspacing="0">
			<thead>
				<tr>
					<th>ID</th>
					<th>菜单名称</th>
					<th>操作</th>
				</tr>
			</thead>
			<tbody>
			<tbody id="dataList"></tbody>
		</table>
	</div>
</article>
<script>
	var homeUrl = '<?php echo $homeUrl; ?>',
		page = 1;
	var refresh = false;
	// 请求数据
	var requestData = function(id) {
		$.post(homeUrl + 'manage2/list_linkages', {
			"node_id": id
		}, function(r) {
			$('#dataList').html(r.data);
		}, 'json');
	};
	$(function() {
		requestData(0);
		$('#dataList').on('click', 'input[data-edit]', function() {
			//location.href = homeUrl + 'manage2/edit_linkage/' + $(this).attr('data-edit');
		});
		$('#dataList').on('click', 'input[data-list]', function() {
			requestData($(this).attr('data-list'));
		});
		$('#dataList').on('click', 'input[data-home]', function() {
			var title = $(this).parent().siblings().eq(1).html();
			if (confirm('您确定要将 ' + title + ' 设置为首页吗？')) {
				$.post(homeUrl + 'manage2/set_homepage/', {
					"node_id": $(this).attr('data-home')
				}, function(r) {
					if (r.status == 1)
						refresh = true;
					showMsg(r.title, r.content);
				}, 'json');
			}
		});
		$('#dataList').on('click', 'input[data-menu]', function() {
			var title = $(this).parent().siblings().eq(1).html();
			if (confirm('您确定要将 ' + title + ' 放入菜单中吗？')) {
				$.post(homeUrl + 'manage2/insert_menu/', {
					"node_id": $(this).attr('data-menu')
				}, function(r) {
					showMsg(r.title, r.content);
				}, 'json');
			}
		});
		$('body').on('click', '.close-me', function() {
			if (refresh)
				window.location.href = homeUrl + 'manage2/list_pages';
		});
	});
</script>