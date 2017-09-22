<?php
include 'header.php';
$failed = false;
?>
<h2>创建数据</h2>
<p>安装向导现在已经做好完成 BootCMS 的安装的准备。单击“开始安装”，开启自动安装进程！</p>
<p id="results" class="fail"></p>
<form>
	<p id="buttons">
		<input type="button" id="prev" value="上一步" disabled="disabled" />
		<input type="hidden" name="action" value="create_database" />
		<input type="submit" value="开始安装" />
	</p>
</form>
<script>
	$(function() {
		$('#results').hide();
		$('input[type="submit"]').focus();
		$('form').submit(function() {
			$('input[type="submit"]').attr('disabled', 'disabled');
			$('#results').html('正在创建数据库，请稍候……');
			$('#results').show();
			$.post('?action=create_database', $('form').serialize(), function(r) {
				if (r.status == 1) {
					$('#results').html(r.content);
					$('#results').removeClass('fail').addClass('pass');
					$('#results').show();
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
