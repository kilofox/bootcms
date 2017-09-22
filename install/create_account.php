<?php
include 'header.php';
$failed = false;
?>
<h2>创建账户</h2>
<p>请仔细填写这个表单。这将在网站上为您自己创建一个管理账户。</p>
<p id="results" class="fail"></p>
<form>
	<table cellspacing="0" id="form">
		<tr>
			<th><label for="username">用户名</label></th>
			<td><input type="text" id="username" name="username" value="" /></td>
		</tr>
		<tr>
			<th><label for="password">密码</label></th>
			<td><input type="password" id="password" name="password" /></td>
		</tr>
		<tr>
			<th><label for="password_confirm">确认密码</label></th>
			<td><input type="password" id="password_confirm" name="password_confirm" /></td>
		</tr>
		<tr>
			<th><label for="email">电子邮箱</label></th>
			<td><input type="text" id="email" name="email" /></td>
		</tr>
	</table>
	<p id="buttons">
		<input type="button" id="prev" value="上一步" disabled="disabled" />
		<input type="hidden" name="action" value="create_admin_account" />
		<input type="submit" value="下一步" />
	</p>
</form>
<script>
	$(function() {
		$('#results').hide();
		$('form').submit(function() {
			$('input[type="submit"]').attr('disabled', 'disabled');
			$.post('?action=create_account', $('form').serialize(), function(r) {
				if (r.status == 1) {
					$('#results').html(r.content);
					$('#results').removeClass('fail').addClass('pass');
					$('#results').show();
					$('#form').remove();
					$('input[type="submit"]').remove();
					$('#buttons').append('<input type="button" id="next" value="下一步" />');
					$('#next').focus();
				} else {
					$('input[type="submit"]').removeAttr('disabled');
					$('#results').html(r.content);
					$('#results').show();
				}
			}, 'json');
			return false;
		});
		$('#prev').click(function() {
			window.location.href = '?action=<?php echo $this->prevStep; ?>';
		});
		$('#buttons').on('click', '#next', function() {
			window.location.href = '?action=<?php echo $this->nextStep; ?>';
		});
	});
</script>
<?php include 'footer.php'; ?>
