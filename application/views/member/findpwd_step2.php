<div id="site_content">
	<div id="sidebar">
		<?php echo $sidebar; ?>
	</div>
	<div id="content">
		<h1>找回密码</h1>
		<p>您可以使用用户名或 E-mail 登录。</p>
		<form id="find_form">
			<div class="form_settings">
				<p><span>E-mail</span><?php echo $email; ?></p>
				<p style="padding-top: 15px"><span>&nbsp;</span><input class="submit" type="submit" id="send_btn" value="发送验证邮件" /></p>
			</div>
		</form>
	</div>
</div>
<script>
	$(function() {
		var homeUrl = '<?php echo $homeUrl; ?>';
		var sended = false;
		$('#send_btn').click(function() {
			if (!sended) {
				sended = true;
				$.post(homeUrl + 'findpwd/sendemail', {
					"uid": '<?php echo $uid; ?>'
				}, function(r) {
					if (r.status == 1)
						window.location.href = homeUrl + 'findpwd/sendemail';
				}, 'json');
			}
			return false;
		});
	});
</script>
