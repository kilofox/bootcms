<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">内容管理</span>
</header>
<article class="module width_full">
	<header>
		<h3 class="tabs_involved">内容管理</h3>
		<ul class="tabs">
			<li><a id="list_trashed_pages">垃圾筒中的单页</a></li>
		</ul>
	</header>
	<div class="tab_container">
		<table class="tablesorter" cellspacing="0">
			<thead>
				<tr>
					<th>ID</th>
					<th>类型</th>
					<th>标题</th>
					<th>作者</th>
					<th>创建时间</th>
					<th>状态</th>
					<th>操作</th>
				</tr>
			</thead>
			<tbody id="dataList"></tbody>
			<tr>
				<td colspan="6" class="tar"><span id="page"></span></td>
			</tr>
		</table>
	</div>
</article>
<script>
	var homeUrl = '<?php echo $homeUrl; ?>',
		page = 1,
		trashed = '0';
	var requestData = function() {
		$.post(homeUrl + 'manage/list_pages', {
			"page": page,
			"trashed": trashed
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
		$('#dataList').on('click', 'input[data-edit]', function() {
			window.location.href = homeUrl + 'manage/edit_page/' + $(this).attr('data-edit');
		});
		$('#dataList').on('click', 'input[data-trash]', function() {
			var title = $(this).parent().siblings().eq(2).html();
			if (confirm('您确定要将“' + title + '”放入垃圾筒吗？')) {
				$.post(homeUrl + 'manage/trash_page', {
					"node_id": $(this).attr('data-trash'),
					"from": '<?php echo Request::current()->action(); ?>'
				}, function(r) {
					if (r.status == 1)
						requestData();
					showMsg(r.title, r.content);
				}, 'json');
			}
		});
		$('#dataList').on('click', 'input[data-home]', function() {
			var title = $(this).parent().siblings().eq(2).html();
			if (confirm('您确定要将“' + title + '”设置为首页吗？')) {
				$.post(homeUrl + 'manage/set_homepage/', {
					"node_id": $(this).attr('data-home')
				}, function(r) {
					if (r.status == 1)
						requestData();
					showMsg(r.title, r.content);
				}, 'json');
			}
		});
		$('#dataList').on('click', 'input[data-menu]', function() {
			var title = $(this).parent().siblings().eq(2).html();
			if (confirm('您确定要将“' + title + '”放入菜单中吗？')) {
				$.post(homeUrl + 'manage/insert_menu/', {
					"node_id": $(this).attr('data-menu')
				}, function(r) {
					if (r.status == 1)
						requestData();
					showMsg(r.title, r.content);
				}, 'json');
			}
		});
		$('#dataList').on('click', 'input[data-comment]', function() {
			window.location.href = homeUrl + 'manage/list_comments/' + $(this).attr('data-comment');
		});
		$('#list_trashed_pages').click(function() {
			$(this).parent().toggleClass('active');
			trashed = $(this).parent().hasClass('active') ? '1' : '0';
			page = 1;
			requestData();
		});
	});
</script>