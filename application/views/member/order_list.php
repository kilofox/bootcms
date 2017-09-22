<div id="site_content">
	<div class="sidebar">
	</div>
	<div id="content">
		<h1>我的订单</h1>
		<table style="width:100%; border-spacing:0;">
			<tr>
				<td>订单编号</td>
				<td>收货人</td>
				<td>订单金额</td>
				<td style="width:20%">生成时间</td>
				<td>订单状态</td>
				<td>操作</td>
			</tr>
			<tbody id="dataList"></tbody>
		</table>
		<p id="page"></p>
	</div>
</div>
<script>
	var homeUrl = '<?php echo $homeUrl; ?>',
		page = 1;
	var requestData = function() {
		$.post(homeUrl + 'member/orders', {
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
			var curPage = parseInt($('#page a.curr').text());
			if (page == '上一页/Previous')
				page = curPage - 1;
			if (page == '下一页/Next')
				page = curPage + 1;
			if (page != curPage)
				requestData();
		});
	});
</script>