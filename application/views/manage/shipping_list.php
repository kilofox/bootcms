<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">管理配送方式</span>
</header>
<article class="module width_full">
	<header><h3 class="tabs_involved">配送方式列表</h3></header>
	<div class="tab_container">
		<table class="tablesorter" cellspacing="0">
			<thead>
				<tr>
					<th>ID</th>
					<th>配送方式名称</th>
					<th>保价费用</th>
					<th>货到付款</th>
					<th>排序</th>
					<th>操作</th>
					<th>状态</th>
				</tr>
			</thead>
			<tbody id="dataList"></tbody>
		</table>
	</div>
</article>
<script>
	var homeUrl = '<?php echo $homeUrl; ?>';
	var requestData = function() {
		$.post(homeUrl + 'manage2/list_shippings', {
			"page": 0
		}, function(r) {
			$('#dataList').html(r.data);
		}, 'json');
	}
	$(function() {
		requestData();
		$('#dataList').on('click', 'input[data-use="edit"]', function() {
			window.location.href = homeUrl + 'manage2/edit_shipping/' + $(this).attr('data-val');
		});
		$('#dataList').on('click', 'input[data-use="delete"]', function() {
			if (confirm('您确定要删除该配送方式吗？')) {
				$.post(homeUrl + 'manage2/delete_shipping', {
					"eid": $(this).attr('data-val'),
					"from": '<?php echo Request::current()->action(); ?>'
				}, function(r) {
					showMsg(r.title, r.content);
				}, 'json');
			}
		});
		$('#submit').click(function() {
			$.post(homeUrl + 'manage2/create_shipping', {
				"ename": $('#new_ex_name').val(),
				"price": $('#new_ex_price').val(),
				"from": '<?php echo Request::current()->action(); ?>'
			}, function(r) {
				showMsg(r.title, r.content);
			}, 'json');
		});
		$('body').on('click', '.close-me', function() {
			window.location.href = homeUrl + 'manage2/list_shippings';
		});
	});
</script>