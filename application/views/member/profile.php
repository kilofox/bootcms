<div id="site_content">
	<div id="sidebar">
	</div>
	<div id="content">
		<h1><?php echo $member->nickname; ?> 的个人资料</h1>
		<div class="form_settings">
			<p><span>用户名</span><?php echo $member->username; ?> （<em><?php echo $member->nickname; ?></em>）</p>
			<p><span>会员等级</span><?php echo $member->level; ?></p>
			<p><span>注册时间</span><?php echo $member->created; ?></p>
			<p><span>入驻论坛时间</span><?php echo $member->regdate; ?></p>
			<p><span>帖子</span><?php echo $member->posts; ?> （平均每日发帖数：<?php echo $member->pp; ?>）</p>
			<p><span>上次登录</span><?php echo $member->last_login; ?></p>
			<p><span>签名</span><?php echo $member->signature; ?></p>
		</div>
	</div>
</div>