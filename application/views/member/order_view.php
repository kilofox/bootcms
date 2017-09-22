<div id="site_content">
	<div class="sidebar">
		<?php echo $sidebar; ?>
	</div>
	<div id="content">
		<h1>订单状态</h1>
		<ul>
			<li>订单编号：<strong><?php echo $order->order_no; ?></strong></li>
			<li>订单状态：<strong><?php echo $order->status; ?></strong></li>
		</ul>
		<h1>商品清单</h1>
		<table style="width:100%; border-spacing:0;">
			<tr>
				<th>商品名称</th>
				<th>价格</th>
				<th>数量</th>
			</tr>
			<?php $amount = 0; ?>
			<?php foreach ($products as $node): ?>
				<?php $amount += $node->price * $node->quantity; ?>
				<tr>
					<td><?php echo $node->product_name; ?></td>
					<td>¥<?php echo $node->price; ?></td>
					<td><?php echo $node->quantity; ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
		<h1>收货人信息</h1>
		<ul>
			<li>姓名：<strong><?php echo $order->consignee; ?></strong></li>
			<li>地址：<strong><?php echo $order->address; ?></strong></li>
			<li>电话：<strong><?php echo $order->phone; ?></strong></li>
			<?php if ($order->message): ?>
				<li>订单附言：<strong><?php echo $order->message; ?></strong></li>
			<?php endif; ?>
		</ul>
		<h1>结算信息</h1>
		<div class="form_settings tar">
			<p>
				<label>商品金额：</label>
				<strong>¥<?php echo number_format($amount, 2); ?></strong>
			</p>
			<p>
				<label>+ 运费：</label>
				<strong>¥<?php echo $order->freight; ?></strong>
			</p>
			<hr style="width:150px;margin:0 0 0 450px" />
			<p>
				<label>付款金额：</label>
				<strong>¥<?php echo $order->amount; ?></strong>
			</p>
		</div>
		<?php if ($order->status == '等待付款'): ?>
			<div class="form_settings">
				<p style="padding-top: 15px">
				<form action="<?php echo $homeUrl; ?>pay/payments/<?php echo $order->id; ?>" method="post">
					<span>&nbsp;</span><input type="submit" value="付款" class="submit" />
				</form>
				</p>
			</div>
		<?php endif; ?>
	</div>
</div>
