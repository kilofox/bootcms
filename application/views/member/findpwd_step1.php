<div id="site_content">
	<div id="sidebar">
		<?php echo $sidebar; ?>
	</div>
	<div id="content">
		<h1>找回密码</h1>
		<p>您可以使用用户名或 E-mail 登录。</p>
		<form id="login_form">
			<div class="form_settings">
				<p><span>E-mail</span><input type="text" name="username" value="" class="contact" /></p>
				<p><span>&nbsp;</span><div id="warning" style="display:none"></div></p>
				<p style="padding-top: 15px"><span>&nbsp;</span><input class="submit" type="submit" name="contact_submitted" value="下一步" /></p>
			</div>
		</form>
	</div>
</div>
<script>
	$(function() {
		var homeUrl = '<?php echo $homeUrl; ?>';
		$('form').submit(function() {
			$.post(homeUrl + 'findpwd/findpwd', $('form').serialize(), function(r) {
				if (r.status == 1)
					window.location.href = homeUrl + 'findpwd/findpwd';
				else
					$('#warning').html(r.content).css('display', 'inline');
			}, 'json');
			return false;
		});
	});
</script>
