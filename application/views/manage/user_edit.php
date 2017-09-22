<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<a href="<?php echo $homeUrl; ?>manage/list_users">用户管理</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">编辑用户</span>
</header>
<article class="module width_full">
	<form>
		<header><h3>账户信息</h3></header>
		<div class="module_content field">
			<ul>
				<li>
					<label>用户名</label>
					<?php echo $user->username; ?>
				</li>
				<li>
					<label>昵称</label>
					<input type="text" name="nickname" value="<?php echo $user->nickname; ?>" />
				</li>
				<li>
					<label>公司名称</label>
					<input type="text" name="company" value="<?php echo $user->company; ?>" />
				</li>
			</ul>
		</div>
		<header><h3>联系信息</h3></header>
		<div class="module_content field">
			<ul>
				<li>
					<label>E-mail</label>
					<input type="text" name="email" value="<?php echo $user->email; ?>" />
				</li>
				<li>
					<label>备用 E-mail</label>
					<input type="text" name="secondary_email" value="<?php echo $user->secondary_email; ?>" />
				</li>
				<li>
					<label>电话号码</label>
					<input type="text" name="phone" value="<?php echo $user->phone; ?>" />
				</li>
				<li>
					<label>地址</label>
					<textarea name="address" class="textarea" /><?php echo $user->address; ?></textarea>
				</li>
			</ul>
		</div>
		<header><h3>账号密码</h3></header>
		<div class="module_content field">
			<ul>
				<li>
					<label>密码</label>
					<input type="password" name="password" value="" />
				</li>
				<li>
					<label>重复密码</label>
					<input type="password" name="password_confirm" value="" />
				</li>
			</ul>
		</div>
		<footer>
			<div class="submit_link">
				<input type="hidden" name="uid" value="<?php echo $user->id; ?>" />
				<input type="submit" value="更新" class="alt_btn" />
				<input type="reset" value="重置" />
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
			$.post(homeUrl + 'manage/edit_user', $('form').serialize(), function(r) {
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
			else
				$('form')[0].reset();
		});
	});
</script>