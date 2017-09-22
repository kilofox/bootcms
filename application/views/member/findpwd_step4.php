<div id="site_content">
	<div id="sidebar">
		<?php echo $sidebar; ?>
	</div>
	<div id="content">
		<h1>找回密码</h1>
		<p>您可以使用用户名或 E-mail 登录。</p>
		<form id="login_form">
			<div class="form_settings">
				<p><span>新的密码</span><input type="password" name="password" value="" /></p>
				<p><span>密码确认</span><input type="password" name="password_confirm" value="" /></p>
				<p style="padding-top: 15px">
					<span><input type="hidden" name="code" value="<?php echo $code; ?>" />&nbsp;</span>
					<input class="submit" type="submit" name="contact_submitted" value="提交" />
				</p>
			</div>
		</form>
	</div>
</div>
<script>
	$(function() {
		$('.login_button').click(function() {
			$('form').submit();
		});
		var homeUrl = '<?php echo $homeUrl; ?>';
		var jump = false;
		$('form').submit(function() {
			$.post(homeUrl + 'findpwd/resetpwd', $('form').serialize(), function(r) {
				if (r.status == 1) {
					window.location.href = homeUrl + 'member/login';
				} else {
					showMsg(r.title, r.content);
				}
			}, 'json');
			return false;
		});
	});
</script>