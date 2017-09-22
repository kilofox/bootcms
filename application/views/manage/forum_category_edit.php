<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<a href="<?php echo $homeUrl; ?>manage3/list_categories">管理分类</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">编辑分类</span>
</header>
<article class="module width_full">
	<header><h3>编辑分类 <?php echo $node->name; ?></h3></header>
	<form id="edit_form">
		<div class="module_content field">
			<ul>
				<li>
					<label>分类名称</label>
					<input type="text" name="cate_name" value="<?php echo $node->name; ?>" />
				</li>
			</ul>
		</div>
		<footer>
			<div class="submit_link">
				<input type="hidden" name="cid" value="<?php echo $node->id; ?>" />
				<input type="submit" name="publish" value="编辑" class="alt_btn" />
				<input type="reset" value="重置" />
			</div>
		</footer>
	</form>
</article>
<script>
	$(function() {
		var homeUrl = '<?php echo $homeUrl; ?>',
			refresh = false;
		$('#edit_form').submit(function() {
			$.post(homeUrl + 'manage3/edit_category', $(this).serialize(), function(r) {
				showMsg(r.title, r.content);
				if (r.status === 2)
					refresh = true;
			}, 'json');
			return false;
		});
		$('body').on('click', '.close-me', function() {
			if (refresh)
				window.location.href = homeUrl + 'manage3/list_categories';
			else
				window.location.href = homeUrl + 'manage3/edit_category/<?php echo $node->id; ?>';
		});
	});
</script>