<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">管理订单</span>
</header>
<article class="module width_full">
	<header>
		<h3 class="tabs_involved">订单管理</h3>
		<ul class="tabs">
			<li><a data-val="0">未付款</a></li>
			<li class="active"><a data-val="1">待发货</a></li>
			<li><a data-val="2">已发货</a></li>
			<li><a data-val="3">已完成</a></li>
			<li><a data-val="4">已取消</a></li>
		</ul>
	</header>
	<div class="tab_container">
		<table class="tablesorter" cellspacing="0">
			<thead>
				<tr>
					<th>订单号</th>
					<th>收货人</th>
					<th>产品</th>
					<th>单价</th>
					<th>数量</th>
					<th>运费</th>
					<th>总额</th>
					<th>时间</th>
					<th>状态</th>
					<th>操作</th>
				</tr>
			</thead>
			<tbody id="dataList"></tbody>
			<tr>
				<td colspan="10" class="tar"><span id="page"></span></td>
			</tr>
		</table>
	</div>
</article>
<script>
	var homeUrl = '<?php echo $homeUrl; ?>',
		refresh = false,
		page = 1,
		status = '1',
		time_start = $('.datepicker_start').val(),
		time_end = $('.datepicker_end').val();
	var requestData = function() {
		$.post(homeUrl + 'manage2/list_orders', {
			"page": page,
			"status": status,
			"created_from": time_start,
			"created_to": time_end
		}, function(r) {
			$('#dataList').html(r.data);
			$('#page').html(r.pagination);
		}, 'json');
	};
	$(function() {
		requestData();
		$('#dataList').on('click', 'a[data-use="markpaid"]', function() {
			if (confirm('您确定该订单【已付款】了吗？')) {
				$.post(homeUrl + 'manage2/mark_paid', {
					"oid": $(this).attr('data-val')
				}, function(r) {
					if (r.status == 1)
						requestData();
					showMsg(r.title, r.content);
				}, 'json');
			}
		});
		$('#dataList').on('click', 'a[data-use="markdelivered"]', function() {
			if (confirm('您确定该订单【已发货】了吗？')) {
				$.post(homeUrl + 'manage2/mark_delivered', {
					"oid": $(this).attr('data-val')
				}, function(r) {
					if (r.status == 1)
						requestData();
					showMsg(r.title, r.content);
				}, 'json');
			}
		});
		$('#dataList').on('click', 'a[data-use="markcompleted"]', function() {
			if (confirm('您确定该订单【已完成】了吗？')) {
				$.post(homeUrl + 'manage2/mark_completed', {
					"oid": $(this).attr('data-val')
				}, function(r) {
					if (r.status == 1)
						requestData();
					showMsg(r.title, r.content);
				}, 'json');
			}
		});
		$('#dataList').on('click', 'a[data-use="markcancelled"]', function() {
			if (confirm('您确定要【取消】该订单吗？')) {
				$.post(homeUrl + 'manage2/mark_cancelled', {
					"oid": $(this).attr('data-val')
				}, function(r) {
					if (r.status == 1)
						requestData();
					showMsg(r.title, r.content);
				}, 'json');
			}
		});
		$('.tabs li').click(function() {
			$(this).addClass('active').siblings().removeClass('active');
			status = $(this).children('a').attr('data-val');
			requestData();
		});
	});
</script>