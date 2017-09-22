<div id="site_content">
	<div class="sidebar">
		<h3>友情提示</h3>
		<ul>
			<li>请您及时付款，以便订单尽快处理！</li>
			<li>立即支付 <?php echo $order->amount; ?> 元，即可完成订单。</li>
			<li>请您在24小时内完成支付，否则订单会被自动取消。</li>
		</ul>
		<?php echo $sidebar; ?>
	</div>
	<div id="content">
		<h1>支付信息</h1>
		<ul>
			<li>订单号：<?php echo $order->order_no; ?></li>
			<li>应付金额：<?php echo $order->amount; ?> 元</li>
		</ul>
		<h1>选择支付方式</h1>
		<div class="form_settings">
			<form id="pay_form" action="<?php echo $homeUrl; ?>pay/pay" method="post">
				<?php
				$amount = 0;
				foreach ($payments as $pay)
				{
					?>
					<h4><input type="radio" name="pid" id="payment_<?php echo $pay->id; ?>" value="<?php echo $pay->id; ?>" /> <label for="payment_<?php echo $pay->id; ?>"><?php echo $pay->name; ?></label></h4>
					<p><?php echo $pay->pay_desc; ?></p>
					<?php
				}
				?>
				<p style="padding-top: 15px">
					<span><input type="hidden" name="oid" value="<?php echo $order->id; ?>" />&nbsp;</span>
					<input type="submit" id="submit" value="确定支付方式" class="submit" />
				</p>
			</form>
		</div>
	</div>
</div>
<script>
	$(function() {
		$('#submit').click(function() {
			if (!$('input[type="radio"]:checked').val()) {
				showMsg('未选择支付方式', '请选择一种支付方式。');
				return false;
			}
		});
	});
</script>