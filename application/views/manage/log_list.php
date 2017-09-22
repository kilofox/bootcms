<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">管理日志</span>
</header>
<article class="module width_3_quarter">
	<header><h3 class="tabs_involved">日志管理</h3></header>
	<div class="tab_container">
		<table class="tablesorter" cellspacing="0">
			<thead>
				<tr>
					<th>ID</th>
					<th>类型</th>
					<th>操作人员</th>
					<th>操作内容</th>
					<th>操作时间</th>
				</tr>
			</thead>
			<tbody id="dataList"></tbody>
			<tr>
				<td colspan="5" class="tar"><span id="page"></span></td>
			</tr>
		</table>
	</div>
</article>
<article class="module width_quarter">
	<header><h3>清理日志</h3></header>
	<div class="message_list">
		<div class="module_content">
			<ul>
				<li>系统将保留最近 90 天的日志。</li>
				<li>系统将保留 90 天以前的 100 条日志。</li>
			</ul>
		</div>
	</div>
	<footer>
		<div id="page" class="submit_link"><input type="submit" id="clear_logs" value="删除日志" class="alt_btn" /></div>
	</footer>
</article>
<script>
	var homeUrl = '<?php echo $homeUrl; ?>',
		refresh = false,
		page = 1,
		sort = 'id-desc';
	var requestData = function() {
		$.post(homeUrl + 'manage/list_logs', {
			"page": page,
			"sort": sort
		}, function(r) {
			$('#dataList').html(r.data);
			$('#page').html(r.pagination);
		}, 'json');
	};
	$(function() {
		requestData();
		$('#clear_logs').click(function() {
			if (confirm('您确定要删除日志吗？')) {
				$.post(homeUrl + 'manage/delete_logs', {
					"from": '<?php echo Request::current()->action(); ?>'
				}, function(r) {
					if (r.status == 1)
						requestData();
					showMsg(r.title, r.content);
				}, 'json');
			}
		});
		$('#tab th').click(function() {
			switch ($(this).index()) {
				case 0:
					sort = sort == 'id-asc' ? 'id-desc' : 'id-asc';
					break;
				case 1:
					sort = sort == 'type-asc' ? 'type-desc' : 'type-asc';
					break;
				case 2:
					sort = sort == 'user-asc' ? 'user-desc' : 'user-asc';
					break;
				case 3:
					sort = sort == 'cont-asc' ? 'cont-desc' : 'cont-asc';
					break;
				case 4:
					sort = sort == 'time-asc' ? 'time-desc' : 'time-asc';
					break;
			}
			requestData();
		});
	});
</script>