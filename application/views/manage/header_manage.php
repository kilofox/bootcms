<script>
	var update = function() {
		var td = new Date();
		var hh = td.getHours();
		var mm = td.getMinutes();
		var ss = td.getSeconds();
		if (hh < 10)
			hh = '0' + hh;
		if (mm < 10)
			mm = '0' + mm;
		if (ss < 10)
			ss = '0' + ss;
		$('.greet').html(td.getFullYear() + ' 年 ' + (td.getMonth() + 1) + ' 月 ' + td.getDate() + ' 日 ' + hh + ':' + mm + ':' + ss);
		window.setTimeout('update()', 1000);
	};
	$(function() {
		update();
		var tallest = 575;
		$('.column').each(function() {
			if ($(this).height() > tallest)
				tallest = $(this).height();
		});
		$('.column').each(function() {
			$(this).height(tallest);
		});
		var action = '/manage/<?php echo $action; ?>';
		$('ul.toggle > li').each(function() {
			if ($(this).children('a').attr('href').indexOf(action) != -1)
				$(this).addClass('active');
		});
	});
</script>
<header id="header">
	<hgroup>
		<h1 class="site_title"><a href="<?php echo $homeUrl; ?>manage">网站管理</a></h1>
		<div class="btn_view_site"><a href="<?php echo $homeUrl; ?>" target="_blank">查看站点</a></div>
		<div class="btn_view_site"><a href="http://www.kilofox.net" target="_blank">官方网站</a></div>
	</hgroup>
</header>
<section id="secondary_bar">
	<div class="user">
		<p><?php echo $user->nickname; ?></p>
		<a class="logout_user" href="<?php echo $homeUrl; ?>manage/logout" title="退出">退出</a>
	</div>
	<div class="welcome">
		<p class="greet"></p>
	</div>
</section>
<aside id="sidebar" class="column">
	<h3>网站</h3>
	<ul class="toggle">
		<?php if (Admin::minimumLevel('setting_general') <= $user->role_id): ?>
			<li class="icn_settings"><a href="<?php echo $homeUrl; ?>manage/general_setting">常规设置</a></li>
		<?php endif; ?>
		<?php if (Admin::minimumLevel('setting_payment') <= $user->role_id): ?>
			<li class="icn_settings"><a href="<?php echo $homeUrl; ?>manage/payment_setting">支付方式设置</a></li>
		<?php endif; ?>
		<?php if (Admin::minimumLevel('cache') <= $user->role_id): ?>
			<li class="icn_security"><a href="<?php echo $homeUrl; ?>manage/cache">网站缓存</a></li>
		<?php endif; ?>
		<?php if (Admin::minimumLevel('list_logs') <= $user->role_id): ?>
			<li class="icn_jump_back"><a href="<?php echo $homeUrl; ?>manage/list_logs">管理日志</a></li>
		<?php endif; ?>
	</ul>
	<h3>内容</h3>
	<ul class="toggle">
		<li class="icn_edit_article"><a href="<?php echo $homeUrl; ?>manage/list_pages">管理内容</a></li>
		<li class="icn_new_article"><a href="<?php echo $homeUrl; ?>manage/create_page">创建新内容</a></li>
		<li class="icn_categories"><a href="<?php echo $homeUrl; ?>manage/list_regions">管理区域与块</a></li>
	</ul>
	<h3>媒体</h3>
	<ul class="toggle">
		<?php if (Admin::minimumLevel('list_media_groups') <= $user->role_id): ?>
			<li class="icn_photo"><a href="<?php echo $homeUrl; ?>manage/list_media_groups">媒体库</a></li>
		<?php endif; ?>
	</ul>
	<h3>用户</h3>
	<ul class="toggle">
		<?php if (Admin::minimumLevel('list_users') <= $user->role_id): ?>
			<li class="icn_view_users"><a href="<?php echo $homeUrl; ?>manage/list_users">管理用户</a></li>
		<?php endif; ?>
		<?php if (Admin::minimumLevel('create_user') <= $user->role_id): ?>
			<li class="icn_add_user"><a href="<?php echo $homeUrl; ?>manage/create_user">创建新用户</a></li>
		<?php endif; ?>
		<?php if (Admin::minimumLevel('edit_user') <= $user->role_id): ?>
			<li class="icn_profile"><a href="<?php echo $homeUrl; ?>manage/edit_user">您的资料</a></li>
		<?php endif; ?>
	</ul>
	<h3>订单</h3>
	<ul class="toggle">
		<li class="icn_categories"><a href="<?php echo $homeUrl; ?>manage2/list_orders">全部订单</a></li>
		<li class="icn_jump_back"><a href="<?php echo $homeUrl; ?>manage2/list_linkages">联动菜单</a></li>
	</ul>
	<h3>产品</h3>
	<ul class="toggle">
		<?php if (Admin::minimumLevel('list_products') <= $user->role_id): ?>
			<li class="icn_categories"><a href="<?php echo $homeUrl; ?>manage2/list_products">管理产品</a></li>
		<?php endif; ?>
		<?php if (Admin::minimumLevel('create_product') <= $user->role_id): ?>
			<li class="icn_new_article"><a href="<?php echo $homeUrl; ?>manage2/create_product">添加新产品</a></li>
		<?php endif; ?>
		<?php if (Admin::minimumLevel('list_product_categories') <= $user->role_id): ?>
			<li class="icn_categories"><a href="<?php echo $homeUrl; ?>manage2/list_product_categories">管理产品分类</a></li>
		<?php endif; ?>
	</ul>
	<h3>配送方式</h3>
	<ul class="toggle">
		<?php if (Admin::minimumLevel('list_shippings') <= $user->role_id): ?>
			<li class="icn_tags"><a href="<?php echo $homeUrl; ?>manage2/list_shippings">管理配送方式</a></li>
		<?php endif; ?>
		<?php if (Admin::minimumLevel('create_shipping') <= $user->role_id): ?>
			<li class="icn_new_article"><a href="<?php echo $homeUrl; ?>manage2/create_shipping">添加配送方式</a></li>
		<?php endif; ?>
	</ul>
	<h3>论坛</h3>
	<ul class="toggle">
		<?php if (Admin::minimumLevel('forum_edit_config') <= $user->role_id): ?>
			<li class="icn_settings"><a href="<?php echo $homeUrl; ?>manage3/config">常规设置</a></li>
		<?php endif; ?>
		<?php if (Admin::minimumLevel('list_forum_categories') <= $user->role_id): ?>
			<li class="icn_categories"><a href="<?php echo $homeUrl; ?>manage3/list_categories">管理分类</a></li>
		<?php endif; ?>
		<?php if (Admin::minimumLevel('list_forum_forums') <= $user->role_id): ?>
			<li class="icn_categories"><a href="<?php echo $homeUrl; ?>manage3/list_forums">管理版块</a></li>
		<?php endif; ?>
		<?php if (Admin::minimumLevel('create_forum_category') <= $user->role_id): ?>
			<li class="icn_new_article"><a href="<?php echo $homeUrl; ?>manage3/create_forum">添加版块</a></li>
		<?php endif; ?>
	</ul>
</aside>
<section id="main" class="column">
