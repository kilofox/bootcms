<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">管理用户</span>
</header>
<article class="module width_full">
	<header><h3 class="tabs_involved">内容管理</h3></header>
	<div class="tab_container">
		<table class="tablesorter" cellspacing="0">
			<thead>
				<tr>
					<th>ID</th>
					<th>用户名</th>
					<th>昵称</th>
					<th>E-mail</th>
					<th>电话</th>
					<th>注册时间</th>
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
		page = 1,
		nickname = $('.search_text').val(),
		time_start = $('.datepicker_start').val(),
		time_end = $('.datepicker_end').val();
	var requestData = function() {
		$.post(homeUrl + 'manage/list_users', {
			"page": page,
			"nickname": nickname,
			"created_from": time_start,
			"created_to": time_end
		}, function(r) {
			$('#dataList').html(r.data);
			$('#page').html(r.pagination);
		}, 'json');
	}
	$(function() {
		requestData();
		// 搜索
		$('#common_user_search .search_btn').click(function() {
			var type = '';
			$("#common_user_search input[type='checkbox']").each(function() {
				if ($(this).is(':checked')) {
					type += $(this).attr('value') + ',';
				}
			});
			type = type.substring(0, type.length - 1);
			time_start = $('.time_start').val();
			time_end = $('.time_end').val();
			nickname = $('.search_text').val();
			requestData();
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
		$('#dataList').on('click', 'input[data-use="edit"]', function() {
			window.location.href = homeUrl + 'manage/edit_user/' + $(this).attr('data-val');
		});
		$('#dataList').on('click', 'input[data-use="delete"]', function() {
			if (confirm('您确定要删除该用户吗？')) {
				$.post(homeUrl + 'manage/delete_user', {
					"user_id": $(this).attr('data-val'),
					"from": '<?php echo Request::current()->action(); ?>'
				}, function(r) {
					showMsg(r.title, r.content);
				}, 'json');
			}
		});
		$('body').on('click', '.close-me', function() {
			window.location.href = homeUrl + 'manage/list_users';
		});
	});
</script>