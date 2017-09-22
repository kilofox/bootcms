<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">网站缓存</span>
</header>
<article class="module width_full">
	<header><h3>网站缓存</h3></header>
	<form id="cache_form">
		<div class="module_content">
			<ul>
				<li>该网站目前正在使用 <strong class="red"><?php echo Cache::$default; ?></strong> 缓存系统。</li>
				<li>要改变网站的缓存系统，请修改 bootstrap.php 文件。</li>
			</ul>
		</div>
		<footer>
			<div class="submit_link">
				<input type="hidden" name="justvariable" value="1" />
				<input type="submit" value="清除缓存" class="alt_btn" />
			</div>
		</footer>
	</form>
</article>
<script>
	$(function() {
		var homeUrl = '<?php echo $homeUrl; ?>';
		$('#cache_form').submit(function() {
			$.post(homeUrl + 'manage/cache', $(this).serialize(), function(r) {
				showMsg(r.title, r.content);
			}, 'json');
			return false;
		});
	});
</script>