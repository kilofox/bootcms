<div id="site_content">
	<div class="sidebar">
		<h2>已经有帐号？</h2>
		<a href="<?php echo $homeUrl; ?>member/login">立即登录</a>
	</div>
	<div id="content">
		<h1>会员注册</h1>
		<p>您可以使用用户名或 E-mail 登录。</p>
		<form id="reg_form" autocomplete="off">
			<div class="form_settings">
				<p><span>用户名</span><input type="text" name="username" value="" class="contact" /></p>
				<p><span>E-mail</span><input type="text" name="email" value="" class="contact" /></p>
				<p><span>密码</span><input type="password" name="password" value="" class="contact" /></p>
				<p><span>确认密码</span><input type="password" name="password_confirm" value="" class="contact" /></p>
				<p style="padding-top: 15px"><span>&nbsp;</span><input type="submit" value="注册" class="submit" /></p>
			</div>
		</form>
	</div>
</div>
<script>
	$(function() {
		var homeUrl = '<?php echo $homeUrl; ?>';
		var jump = false;
		$('#reg_form').submit(function() {
			var canPost = true;
			$('input').each(function() {
				if (!$(this).val()) {
					canPost = false;
					$(this).focus();
				}
			});
			if (canPost) {
				$('input[type="submit"]').prop('disabled', true);
				$.post(homeUrl + 'member/register', $('#reg_form').serialize(), function(r) {
					if (r.status == 1) {
						jump = true;
					}
					showMsg(r.title, r.content);
					$('input[type="submit"]').prop('disabled', false);
				}, 'json');
			}
			return false;
		});
		$('body').on('click', '.close-me', function() {
			if (jump)
				window.location.href = homeUrl + 'member/login';
		});
	});
</script>