<article id="login">
	<header><h3>登录BootCMS</h3></header>
	<div class="field">
		<form id="login_form" autocomplete="off">
			<ul>
				<li>
					<label>用户名</label>
					<input type="text" name="username" value="" placeholder="用户名" />
				</li>
				<li>
					<label>口令</label>
					<input type="password" name="password" value="" placeholder="口令" />
				</li>
				<li>
					<label><input type="hidden" name="change_user" value="true" readonly="readonly" /></label>
					<input type="submit" value="登录" class="alt_btn" />
				</li>
			</ul>
		</form>
	</div>
	<p><?php echo Setup::copyright('login'); ?></p>
</article>
<script>
	$(function() {
		var homeUrl = '<?php echo $homeUrl; ?>';
		$('#login_form').submit(function() {
			var canPost = true;
			$('input').each(function() {
				if (!$(this).val()) {
					$(this).focus();
					canPost = false;
				}
			});
			if (canPost) {
				$('input[type="submit"]').prop('disabled', true);
				$.post(homeUrl + 'manage/login', $('#login_form').serialize(), function(r) {
					if (r.status == 1) {
						window.location.href = homeUrl + 'manage';
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