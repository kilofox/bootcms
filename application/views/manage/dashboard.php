<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">控制面板</span>
</header>
<h4 class="alert_info">您好 <span class="red"><?php echo $user->first_name; ?></span>，这是您的个人信息。如果哪里不正确，<a href="<?php echo $homeUrl; ?>manage/edit_user">点击这里</a>修改。</h4>
<article class="module width_full">
	<header><h3>您的资料</h3></header>
	<div class="module_content">
		<ul>
			<li><label>用户级别：</label><span><?php echo $user->role; ?></span></li>
			<li><label>公司名称：</label><span><?php echo $user->company; ?></span></li>
			<li><label>联 系 人：</label><span><?php echo $user->first_name; ?></span></li>
			<li><label>E-mail：</label><span><?php echo $user->email; ?></span></li>
			<li><label>备用 E-mail：</label><span><?php echo $user->secondary_email ? $user->secondary_email : '无'; ?></span></li>
			<li><label>电　　话：</label><span><?php echo $user->phone; ?></span></li>
			<li><label>地　　址：</label><span><?php echo nl2br($user->address); ?></span></li>
		</ul>
	</div>
</article>
