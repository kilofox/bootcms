<div id="site_content">
	<div class="sidebar">
		<?php echo $sidebar; ?>
		<h3>搜索</h3>
		<form method="post" action="#" id="search_form">
			<p>
				<input class="search" type="text" name="search_field" value="关键字" />
				<input name="search" type="image" style="border: 0; margin: 0 0 -9px 5px;" src="<?php echo $homeUrl; ?>assets/images/search.png" alt="搜索" title="搜索" />
			</p>
		</form>
	</div>
	<div id="content">
		<h1>支付完成</h1>
		<?php echo $message; ?>
	</div>
</div>
