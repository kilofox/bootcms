<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">支付方式设置</span>
</header>
<article class="module width_full">
	<header><h3>配置支付宝</h3></header>
	<form id="setting_form">
		<div class="module_content field">
			<ul>
				<li>
					<label>收款产品</label>
					<select name="service_type">
						<option value="0"<?php if ($alipay->config->service_type == '0') echo ' selected="selected"'; ?>>担保交易收款</option>
						<option value="1"<?php if ($alipay->config->service_type == '1') echo ' selected="selected"'; ?> disabled="disabled">即时到账收款</option>
						<option value="2"<?php if ($alipay->config->service_type == '2') echo ' selected="selected"'; ?> disabled="disabled">双功能收款</option>
					</select>
				</li>
				<li>
					<label>支付宝账户名</label>
					<input type="text" name="account" value="<?php echo $alipay->config->account; ?>" />
				</li>
				<li>
					<label>合作者身份（PID）</label>
					<input type="text" name="partner" value="<?php echo $alipay->config->partner; ?>" />
				</li>
				<li>
					<label>交易安全校验码（Key）</label>
					<input type="text" name="key" value="<?php echo $alipay->config->key; ?>" class="input" />
				</li>
				<li>
					<label>支付方式描述</label>
					<textarea name="pay_desc" class="textarea"><?php echo $alipay->pay_desc; ?></textarea>
				</li>
				<li>
					<label>排序</label>
					<input type="text" name="list_order" value="<?php echo $alipay->list_order; ?>" class="input_narrow" />
				</li>
			</ul>
		</div>
		<footer>
			<div class="submit_link">
				<input type="hidden" name="pid" value="<?php echo $alipay->id; ?>" />
				<input type="submit" value="更新" class="alt_btn" />
				<input type="reset" value="重置" />
			</div>
		</footer>
	</form>
</article>
<script>
	$(function() {
		var homeUrl = '<?php echo $homeUrl; ?>';
		$('#setting_form').submit(function() {
			$.post(homeUrl + 'manage/payment_setting', $('#setting_form').serialize(), function(r) {
				showMsg(r.title, r.content);
			}, 'json');
			return false;
		});
	});
</script>