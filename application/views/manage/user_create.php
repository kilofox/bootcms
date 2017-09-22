<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<a href="<?php echo $homeUrl; ?>manage/list_users">用户管理</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">创建新用户</span>
</header>
<article class="module width_full">
	<header><h3>登录信息</h3></header>
	<form>
		<div class="module_content field">
			<ul>
				<li>
					<label>用户名</label>
					<input type="text" name="username" value="" />
				</li>
				<li>
					<label>密码</label>
					<input type="password" name="password" value="" />
				</li>
				<li>
					<label>确认密码</label>
					<input type="password" name="password_confirm" value="" />
				</li>
			</ul>
		</div>
		<header><h3>账户信息</h3></header>
		<div class="module_content field">
			<ul>
				<li>
					<label>昵称</label>
					<input type="text" name="nickname" value="" />
				</li>
				<li>
					<label>姓名</label>
					<input type="text" name="first_name" value="" />
				</li>
				<li>
					<label>公司名称</label>
					<input type="text" name="company" value="" />
				</li>
				<li class="clear"></li>
			</ul>
		</div>
		<header><h3>联系信息</h3></header>
		<div class="module_content field">
			<ul>
				<li>
					<label>电子邮箱</label>
					<input type="text" name="email" value="" />
				</li>
				<li>
					<label>备用电子邮箱</label>
					<input type="text" name="secondary_email" value="" />
				</li>
				<li>
					<label>电话</label>
					<input type="text" name="phone" value="" />
				</li>
				<li>
					<label>地址</label>
					<textarea name="address" class="textarea" /></textarea>
				</li>
			</ul>
		</div>
		<footer>
			<div class="submit_link">
				<input type="submit" name="publish" value="创建用户" class="alt_btn" />
			</div>
		</footer>
	</form>
</article>
<script>
	$(function() {
		var homeUrl = '<?php echo $homeUrl; ?>',
			toList = false;
		$('form').submit(function() {
			$('input[type="submit"]').prop('disabled', true);
			$.post(homeUrl + 'manage/create_user', $('form').serialize(), function(r) {
				showMsg(r.title, r.content);
				if (r.status == 1)
					toList = true;
				$('input[type="submit"]').prop('disabled', false);
			}, 'json');
			return false;
		});
		$('body').on('click', '.close-me', function() {
			if (toList)
				window.location.href = homeUrl + 'manage/list_users';
		});
	});
</script>
