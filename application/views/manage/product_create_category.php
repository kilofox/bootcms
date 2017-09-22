<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">创建新产品分类</span>
</header>
<article class="module width_full">
	<header><h3>创建新产品分类</h3></header>
	<div class="module_content field">
		<ul>
			<li>
				<label>产品分类名称</label>
				<input type="text" id="category_name" value="" />
			</li>
		</ul>
	</div>
	<footer>
		<div class="submit_link">
			<input type="submit" id="publish" value="创建产品分类" class="alt_btn" />
		</div>
	</footer>
</article>
<script>
	$(function() {
		var homeUrl = '<?php echo $homeUrl; ?>',
			refresh = false;
		$('#publish').click(function() {
			var categoryName = $('#category_name').val();
			$.post(homeUrl + 'manage2/create_product_category', {
				"category_name": categoryName,
				"from": '<?php echo Request::current()->action(); ?>'
			}, function(r) {
				showMsg(r.title, r.content);
				if (r.status == '0')
					refresh = true;
			}, 'json');
		});
		$('body').on('click', '.close-me', function() {
			if (refresh)
				window.location.href = homeUrl + 'manage2/list_product_categories';
		});
	});
</script>
