<?php
include 'header.php';
$failed = false;
?>
<h2>数据库信息</h2>
<p>如果您不确定这些设置，问问主机提供商。您必须在安装之前创建数据库。</p>
<p id="results" class="fail"></p>
<form>
	<table cellspacing="0" id="form">
		<tr>
			<th><label for="db_host">数据库服务器</label></th>
			<td><input type="text" id="db_host" name="db_host" value="localhost" /></td>
		</tr>
		<tr>
			<th><label for="db_user">数据库用户名</label></th>
			<td><input type="text" id="db_user" name="db_user" value="" /></td>
		</tr>
		<tr>
			<th><label for="db_pass">数据库密码</label></th>
			<td><input type="password" id="db_pass" name="db_pass" value="" /></td>
		</tr>
		<tr>
			<th><label for="db_name">数据库名</label></th>
			<td><input type="text" id="db_name" name="db_name" value="" /></td>
		</tr>
		<tr>
			<th><label for="db_prefix">数据库表前缀</label></th>
			<td><input type="text" id="db_prefix" name="db_prefix" value="bc_" /></td>
		</tr>
	</table>
	<p id="buttons">
		<input type="button" id="prev" value="上一步" />
		<input type="hidden" name="action" value="config_database" />
		<input type="submit" value="下一步" />
	</p>
</form>
<script>
	$(function() {
		$('#results').hide();
		$('form').submit(function() {
			$('input[type="submit"]').attr('disabled', 'disabled');
			$.post('?action=config_database', $('form').serialize(), function(r) {
				if (r.status == 1) {
					$('#results').html(r.content);
					$('#results').removeClass('fail').addClass('pass');
					$('#results').show();
					$('#form').remove();
					$('#prev').attr('disabled', 'disabled');
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
