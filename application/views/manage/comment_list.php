<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">评论管理</span>
</header>
<article class="module width_full">
	<header><h3 class="tabs_involved">评论管理</h3></header>
	<div class="tab_container">
		<table class="tablesorter" cellspacing="0">
			<thead>
				<tr>
					<th>ID</th>
					<th>评论者</th>
					<th>文章标题</th>
					<th>评论内容</th>
					<th>发表时间</th>
					<th>操作</th>
				</tr>
			</thead>
			<tbody id="dataList"></tbody>
			<tr>
				<td colspan="7" class="tar"><span id="page"></span></td>
			</tr>
		</table>
	</div>
</article>
<script>
	var homeUrl = '<?php echo $homeUrl; ?>',
		page = 1;
	var requestData = function() {
		$.post(homeUrl + 'manage/list_comments', {
			"pid": '<?php echo $pageId; ?>',
			"page": page
		}, function(r) {
			$('#dataList').html(r.data);
			$('#page').html(r.pagination);
		}, 'json');
	};
	$(function() {
		requestData();
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
		$('#dataList').on('click', 'input[data-trash]', function() {
			var title = $(this).parent().siblings().eq(3).html();
			if (confirm('您确定要删除“' + title.substr(0, 100) + '”这条评论吗？')) {
				$.post(homeUrl + 'manage/delete_comment', {
					"node_id": $(this).attr('data-trash'),
					"from": '<?php echo Request::current()->action(); ?>'
				}, function(r) {
					if (r.status == 1)
						requestData();
					showMsg(r.title, r.content);
				}, 'json');
			}
		});
	});
</script>