<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<span><a href="<?php echo $homeUrl; ?>manage/list_media_groups">媒体库</a></span>
	<div class="breadcrumb_divider"></div>
	<span class="current"><?php echo $group->group_name; ?></span>
</header>
<article class="module width_3_quarter">
	<header><h3 class="tabs_involved"><?php echo $group->group_name; ?>管理</h3></header>
	<div class="tab_container">
		<table class="tablesorter" cellspacing="0">
			<thead>
				<tr>
					<th>缩略图</th>
					<th>分组</th>
					<th>上传时间</th>
					<th>操作</th>
				</tr>
			</thead>
			<tbody id="dataList"></tbody>
			<tr>
				<td colspan="4" class="tar"><span id="page"></span></td>
			</tr>
		</table>
	</div>
</article>
<article class="module width_quarter" id="edit_region_area">
	<header><h3>添加<?php echo $group->group_name; ?></h3></header>
	<div class="message_list">
		<div class="module_content">
			<input type="submit" id="add_new_btn" value="添加新媒体" class="alt_btn" />
		</div>
	</div>
</article>
<script>
	var homeUrl = '<?php echo $homeUrl; ?>',
		page = 1,
		groupId = '<?php echo $group->id; ?>';
	var requestData = function() {
		$.post(homeUrl + 'manage/list_media', {
			"group": groupId,
			"page": page
		}, function(r) {
			$('#dataList').html(r.data);
			$('#page').html(r.pagination);
		}, 'json');
	};
	$(function() {
		requestData();
		$('#dataList').on('click', 'input[data-delete]', function() {
			if (confirm('您确定要删除该媒体吗？')) {
				$.post(homeUrl + 'manage/delete_media', {
					"mid": $(this).attr('data-delete'),
					"from": '<?php echo Request::current()->action(); ?>'
				}, function(r) {
					if (r.status === 1)
						requestData();
					showMsg(r.title, r.content);
				}, 'json');
			}
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
		$('#add_new_btn').click(function() {
			window.location.href = homeUrl + 'manage/create_media/' + groupId;
		});
	});
</script>