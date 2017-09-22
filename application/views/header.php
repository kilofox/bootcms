<?php $website = Setup::siteInfo(); ?>
<div id="main">
	<div id="header">
		<div id="logo">
			<div id="logo_text">
				<h1><a href="<?php echo $homeUrl; ?>" title="<?php echo $website->site_title; ?>"><?php echo $website->site_title; ?></a></h1>
				<h2><?php echo $website->site_description; ?></h2>
			</div>
			<div id="login">
				<a href="<?php echo $homeUrl; ?>cart">购物车</a>
				<a href="<?php echo $homeUrl; ?>member/orders">我的订单</a>
				<?php if (isset($user)): ?>
					<a href="<?php echo $homeUrl; ?>member/panel"><?php echo $user->nickname; ?></a>
					<a href="<?php echo $homeUrl; ?>member/logout">退出</a>
				<?php else: ?>
					<a href="<?php echo $homeUrl; ?>member/login">登录</a>
					<a href="<?php echo $homeUrl; ?>member/register">注册</a>
				<?php endif; ?>
			</div>
		</div>
		<div id="menubar">
			<ul id="menu">
				<?php echo Setup::makeMainMenu($controller, $homeUrl); ?>
			</ul>
		</div>
	</div>
