<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<a href="<?php echo $homeUrl; ?>manage2/list_product_categories">产品分类管理</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">编辑产品分类</span>
</header>
<article class="module width_full">
	<header><h3>编辑产品分类</h3></header>
	<div class="module_content field">
		<ul>
			<li>
				<label>产品分类名</label>
				<input type="text" id="cate_name" value="<?php echo $node->name; ?>" />
			</li>
			<li>
				<label>显示顺序</label>
				<input type="text" id="cate_order" value="<?php echo $node->list_order; ?>" />
			</li>
		</ul>
	</div>
	<footer>
		<div class="submit_link">
			<input type="hidden" id="id" value="<?php echo $node->id; ?>" />
			<input type="submit" id="publish" value="更新" class="alt_btn" />
			<input type="reset" value="重置" />
		</div>
	</footer>
</article>
<script>
	$(function() {
		var homeUrl = '<?php echo $homeUrl; ?>',
			refresh = false;
		$('#publish').click(function() {
			var cateName = $('#cate_name').val();
			var cateOrder = $('#cate_order').val();
			$.post(homeUrl + 'manage2/edit_product_category', {
				"cid": '<?php echo $node->id; ?>',
				"cate_name": cateName,
				"cate_order": cateOrder
			}, function(r) {
				showMsg(r.title, r.content);
				if (r.status == '2')
					refresh = true;
			}, 'json');
		});
		$('body').on('click', '.close-me', function() {
			if (refresh)
				window.location.href = homeUrl + 'manage2/list_product_categories';
			else
				window.location.href = homeUrl + 'manage2/edit_product_category/<?php echo $node->id; ?>';
		});
	});
</script>