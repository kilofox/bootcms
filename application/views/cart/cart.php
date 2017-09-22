<div id="site_content">
	<div class="sidebar">
		<h3>搜索</h3>
		<p>
		<form id="search_form" action="<?php echo $homeUrl; ?>homepage/search/" method="get">
			<input type="text" class="search" name="q" placeholder="关键字" />
			<input type="image" id="search" style="border: 0; margin: 0 0 -9px 5px;" src="<?php echo $homeUrl; ?>assets/images/search.png" alt="搜索" title="搜索" />
		</form>
		</p>
	</div>
	<div id="content">
		<h1>我的购物车</h1>
		<?php if ($user->id): ?>
			<?php if (count($products)): ?>
				<form id="cart_form" action="<?php echo $homeUrl; ?>cart/settle_accounts" method="post">
					<table style="width:100%; border-spacing:0;">
						<tr>
							<th>产品</th>
							<th>价格</th>
							<th>数量</th>
							<th>操作</th>
						</tr>
						<?php foreach ($products as $node): ?>
							<tr data-node="<?php echo $node->product_id; ?>">
								<td><?php echo $node->product_name; ?></td>
								<td data-bind="price"><?php echo $node->price; ?></td>
								<td>
									<em data-bind="dec" class="pointer">-</em>
									<span data-bind="num"><?php echo $node->quantity; ?></span>
									<em data-bind="inc" class="pointer">+</em>
								</td>
								<td data-bind="remove" class="pointer">删除</td>
							</tr>
						<?php endforeach; ?>
					</table>
					<p>总金额：<span id="amount"></span></p>
					<div class="form_settings">
						<p style="padding-top: 15px">
							<span><input type="hidden" id="cid" name="cid" value="<?php echo $node->id; ?>" />&nbsp;</span>
							<input type="submit" value="去柜台结算" class="submit" />
						</p>
					</div>
				</form>
			<?php else: ?>
				<p>购物车内暂时没有商品，您可以去<a href="<?php echo $homeUrl; ?>product">产品频道</a>挑选喜欢的商品。</p>
			<?php endif; ?>
		<?php else: ?>
			<p>购物车内暂时没有商品，<a href="<?php echo $homeUrl; ?>member/login">登录</a>后，将显示您之前加入的商品。</p>
			<p>去<a href="<?php echo $homeUrl; ?>product">产品频道</a>挑选喜欢的商品。</p>
		<?php endif; ?>
	</div>
</div>
<script>
	var homeUrl = '<?php echo $homeUrl; ?>';
	var computeAmount = function() {
		var amount = 0;
		$('tr[data-node]').each(function() {
			amount += parseFloat($(this).children('td[data-bind="price"]').html()) * parseInt($(this).children('td').eq(2).children('span[data-bind="num"]').html());
		});
		$('#amount').html('¥' + amount.toFixed(2));
	};
	$(function() {
		$('em[data-bind="dec"]').click(function() {
			var num = parseInt($(this).siblings('span[data-bind="num"]').html());
			if (num > 1) {
				var cid = $('#cid').val();
				var pid = $(this).parents('tr').attr('data-node');
				$.post(homeUrl + 'cart/change_num', {
					"cid": cid,
					"pid": pid,
					"direction": "dec"
				}, function(r) {
					if (r.status == 1)
						$('tr[data-node="' + pid + '"]').children('td').eq(2).children('span[data-bind="num"]').html(r.data);
					computeAmount();
				}, 'json');
			}
		});
		$('em[data-bind="inc"]').click(function() {
			var cid = $('#cid').val();
			var pid = $(this).parents('tr').attr('data-node');
			$.post(homeUrl + 'cart/change_num', {
				"cid": cid,
				"pid": pid,
				"direction": "inc"
			}, function(r) {
				if (r.status == 1)
					$('tr[data-node="' + pid + '"]').children('td').eq(2).children('span[data-bind="num"]').html(r.data);
				computeAmount();
			}, 'json');
		});
		$('td[data-bind="remove"]').click(function() {
			var cid = $('#cid').val();
			var pid = $(this).parents().attr('data-node');
			$.post(homeUrl + 'cart/remove', {
				"cid": cid,
				"pid": pid
			}, function(r) {
				if (r.status == 1)
					window.location.href = homeUrl + 'cart';
				else
					showMsg(r.title, r.content);
			}, 'json');
		});
		$('#cart_form').click(function() {
			var pids = '';
			$('tr[data-node]').each(function() {
				pids += $(this).attr('data-node') + ',';
			});
			$(this).append('<input type="hidden" name="pids" value="' + pids + '" />');
		});
		computeAmount();
	});
</script>
