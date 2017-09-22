<div id="site_content">
	<div id="sidebar">
	</div>
	<div id="content">
		<h1>修改密码</h1>
		<form id="user_form">
			<div class="form_settings">
				<p><span>昵称</span><?php echo $user->nickname; ?></p>
				<p><span>新的密码</span><input type="password" name="password" value="" /></p>
				<p><span>密码确认</span><input type="password" name="password_confirm" value="" /></p>
				<p style="padding-top: 15px">
					<span><input type="hidden" name="uid" value="<?php echo $user->id; ?>" />&nbsp;</span>
					<input class="submit" type="submit" name="contact_submitted" value="提交" />
				</p>
			</div>
		</form>
	</div>
</div>
<script>
	$(function() {
		var homeUrl = '<?php echo $homeUrl; ?>',
			jump = false;
		$('#user_form').submit(function() {
			$('input[type="submit"]').prop('disabled', true);
			$.post(homeUrl + 'member/panel', $(this).serialize(), function(r) {
				if (r.status == 1) {
					jump = true;
				} else {
					$('input[type="submit"]').prop('disabled', false);
				}
				showMsg(r.title, r.content);
			}, 'json');
			return false;
		});
		$('body').on('click', '.close-me', function() {
			if (jump)
				window.location.href = homeUrl + 'member/login';
		});
	});
</script>