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
		<div class="form_settings">
			<p style="padding-top: 15px">
				<span>&nbsp;</span>
				<?php echo $html; ?>
			</p>
		</div>
	</div>
</div>
