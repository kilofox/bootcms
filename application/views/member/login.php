<div id="site_content">
	<div class="sidebar">
		<h2>还没有帐号？</h2>
		<a href="<?php echo $homeUrl; ?>member/register">免费注册</a>
	</div>
	<div id="content">
		<h1>会员登录</h1>
		<p>您可以使用用户名或 E-mail 登录。</p>
		<form id="login_form" autocomplete="off">
			<div class="form_settings">
				<p><span>用户名或E-mail</span><input type="text" name="username" value="" class="contact" /></p>
				<p><span>密码</span><input type="password" name="password" value="" class="contact" /></p>
				<p style="padding-top: 15px"><span>&nbsp;</span><input type="submit" value="登录" class="submit" /></p>
			</div>
		</form>
		<p>
			<br />
			<br />
		<h4><a href="<?php echo $homeUrl; ?>findpwd">忘记了密码？</a></h4>
		</p>
	</div>
</div>
<script>
	$(function() {
		var homeUrl = '<?php echo $homeUrl; ?>';
		$('#login_form').submit(function() {
			$('input[type="submit"]').prop('disabled', true);
			var canPost = true;
			$('input').each(function() {
				if (!$(this).val()) {
					$(this).focus();
					canPost = false;
					$('input[type="submit"]').prop('disabled', false);
				}
			});
			if (canPost) {
				$.post(homeUrl + 'member/login', $('#login_form').serialize(), function(r) {
					if (r.status === 1) {
						window.location.href = '<?php echo $refererTo; ?>';
					} else {
						$('input[name="password"]').val('');
						$('input[type="submit"]').prop('disabled', false);
						showMsg(r.title, r.content);
					}
				}, 'json');
			}
			return false;
		});
	});
</script>