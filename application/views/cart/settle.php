<div id="site_content">
	<div class="sidebar">
	</div>
	<div id="content">
		<h1>商品列表</h1>
		<form id="form_order">
			<table id="goods" style="width:100%; border-spacing:0;">
				<tr>
					<th>产品</th>
					<th>价格</th>
					<th>数量</th>
				</tr>
				<?php $gids = ''; ?>
				<?php foreach ($goods as $node): ?>
					<?php $gids .= $node->id . ','; ?>
					<tr data-bind="<?php echo $node->id; ?>">
						<td><?php echo $node->product_name; ?></td>
						<td>&yen;<span><?php echo $node->price; ?></span></td>
						<td><?php echo $node->quantity; ?></td>
					</tr>
				<?php endforeach; ?>
				<?php $gids = substr($gids, 0, -1); ?>
			</table>
			<div id="address_box" class="form_settings">
				<h1>收货人信息</h1>
				<p><span>收货人</span><input type="text" name="consignee" value="" class="contact" /></p>
				<p>
					<span>省/市/区</span>
					<select name="addr_prov" style="width:100px">
						<option value="0">请选择</option>
						<?php foreach ($selProvs as $node): ?>
							<option value="<?php echo $node->id; ?>"><?php echo $node->name; ?></option>
						<?php endforeach; ?>
					</select>
					<select name="addr_city" style="width:100px">
						<option value="0">请选择</option>
					</select>
					<select name="addr_area" style="width:100px">
						<option value="0">请选择</option>
					</select>
				</p>
				<p><span>详细地址</span><input type="text" name="addr_detail" value="" class="contact" /></p>
				<p><span>电话</span><input type="text" name="phone" value="" class="contact" /></p>
				<p><span>订单附言</span><textarea class="contact textarea" rows="8" cols="50" name="message"></textarea></p>
				<p><span>&nbsp;</span><input type="button" value="保存收货人信息" class="submit" /></p>
			</div>
			<div id="address_show" style="display:none" class="form_settings">
				<h1>收货人信息</h1>
				<p>
					<span style="text-align:right">收货人姓名：</span>&nbsp;<strong id="consignee"></strong>
				</p>
				<p>
					<span style="text-align:right">地址：</span>&nbsp;<strong id="address"></strong>
				</p>
				<p>
					<span style="text-align:right">电话：</span>&nbsp;<strong id="phone"></strong>
				</p>
				<p>
					<span style="text-align:right">订单附言：</span>&nbsp;<strong id="message"></strong>
				</p>
				<p>
					<span>&nbsp;</span>[<a>修改</a>]
				</p>
			</div>
			<div id="shipping_box" style="display:none" class="form_settings">
				<h1>配送方式</h1>
				<table style="width:100%; border-spacing:0;">
					<tr>
						<th>配送方式</th>
						<th>运费</th>
						<th>保价费用</th>
					</tr>
					<?php if (is_array($shippings)): ?>
						<?php foreach ($shippings as $s): ?>
							<?php $s->insurance = $s->support_cod == 1 ? $s->insurance : '不支持'; ?>
							<tr>
								<td><input type="radio" id="sp_<?php echo $s->id; ?>" name="shipping" value="<?php echo $s->id; ?>" /> <label for="sp_<?php echo $s->id; ?>"><?php echo $s->shipping_name; ?></label></td>
								<td><?php echo $s->base_price; ?></td>
								<td><?php echo $s->insurance; ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</table>
				<p>
					<span>&nbsp;</span><input type="button" value="保存配送方式" class="submit" />
				</p>
			</div>
			<div id="shipping_show" class="form_settings" style="display:none">
				<h1>配送方式</h1>
				<p>
					<span style="text-align:right">配送方式：</span>&nbsp;<strong id="ss_name"></strong>
				</p>
				<p>
					<span style="text-align:right">运费：</span>&nbsp;<strong id="ss_price"></strong>
				</p>
				<p>
					<span style="text-align:right">保价费用：</span>&nbsp;<strong id="ss_insurance"></strong>
				</p>
				<p><span>&nbsp;</span>[<a>修改</a>]</p>
			</div>
			<div id="total" class="form_settings" style="display:none">
				<h1>结算信息</h1>
				<p><span style="text-align:right">应付总额：</span>&nbsp;<strong id="amount"></strong></p>
				<p style="padding-top: 15px">
					<span>&nbsp;
						<input type="hidden" name="gids" value="<?php echo $gids; ?>" />
						<input type="hidden" name="ftype" value="3" />
					</span>
					<input type="submit" value="提交" class="submit" />
				</p>
			</div>
		</form>
	</div>
</div>
<script>
	var homeUrl = '<?php echo $homeUrl; ?>',
		from = '<?php echo Request::factory()->current()->action(); ?>';
	var addressChanged = false;
	var computeFreight = function() {
		addressChanged = false;
		$.post(homeUrl + 'cart/shippings', {
			"from": from
		}, function(r) {
			if (r.status == 1) {
				$('input[name="shipping"]').each(function() {
					for (i in r.data) {
						if (r.data[i].id == $(this).val()) {
							if (r.data[i].price_type == 1) {
								var areas = r.data[i].areas;
								for (j in areas) {
									var aids = areas[j].area_id.split(',');
									for (k in aids) {
										if ($('select[name="addr_prov"]').val() == aids[k] || $('select[name="addr_city"]').val() == aids[k] || $('select[name="addr_area"]').val() == aids[k]) {
											$(this).parent().siblings().eq(0).html(parseInt(areas[j].base_price || 0).toFixed(2));
											$(this).prop('disabled', false);
											break;
										} else {
											$(this).prop('disabled', true);
										}
									}
								}
							} else {
								$(this).parent().siblings().eq(0).html(r.data[i].base_price);
							}
						}
					}
				});
			}
		}, 'json');
		$('#shipping_show').hide();
		$('#shipping_box').show('slow');
	};
	$(function() {
		$('#address_box').find('input[type="button"]').click(function() {
			$('#consignee').html($('input[name="consignee"]').val());
			$('#address').html($('select[name="addr_prov"] > option:selected').text()
				+ $('select[name="addr_city"] > option:selected').text()
				+ $('select[name="addr_area"] > option:selected').text()
				+ $('input[name="addr_detail"]').val());
			$('#phone').html($('input[name="phone"]').val());
			$('#message').html($('textarea[name="message"]').val());
			if (!$('input[name="consignee"]').val()
				|| !$('select[name="addr_prov"]').val()
				|| !$('select[name="addr_city"]').val()
				|| !$('input[name="addr_detail"]').val()
				|| !$('input[name="phone"]').val()
				) {
				showMsg('信息不完整', '请将信息填写完整！');
				return false;
			}
			if (addressChanged)
				computeFreight();
			$('#address_box').hide();
			$('#address_show').show();
		});
		$('#address_show').find('a').click(function() {
			$('#address_show').hide();
			$('#address_box').show();
		});
		$('select[name="addr_prov"]').change(function() {
			$.post(homeUrl + 'cart/linkage', {
				"mid": $(this).val(),
				"from": from
			}, function(r) {
				if (r.status == 1) {
					var options = '<option value="0">请选择</option>';
					for (i in r.data) {
						options += '<option value="' + r.data[i].id + '">' + r.data[i].name + '</option>';
					}
					$('select[name="addr_city"]').empty().append(options);
				}
			}, 'json');
			addressChanged = true;
		});
		$('select[name="addr_city"]').change(function() {
			$.post(homeUrl + 'cart/linkage', {
				"mid": $(this).val(),
				"from": from
			}, function(r) {
				if (r.status == 1) {
					var options = '<option value="0">请选择</option>';
					for (i in r.data) {
						options += '<option value="' + r.data[i].id + '">' + r.data[i].name + '</option>';
					}
					if (r.data.length == 0)
						options = '';
					$('select[name="addr_area"]').empty().append(options);
				}
			}, 'json');
			addressChanged = true;
		});
		$('select[name="addr_area"]').change(function() {
			addressChanged = true;
		});
		$('#shipping_box').find('input[type="button"]').click(function() {
			var shipping = $('input[name="shipping"]:checked');
			if (!shipping.val()) {
				showMsg('信息不完整', '请选择一种配送方式。');
				return false;
			}
			$('#ss_name').html($(shipping).siblings().text());
			$('#ss_price').html($(shipping).parent().siblings().eq(0).text());
			$('#ss_insurance').html($(shipping).parent().siblings().eq(1).text() == '不支持' ? '0.00' : $(shipping).parent().siblings().eq(1).text());
			$('#shipping_box').hide();
			$('#shipping_show').show();
			var goodsPrice = 0;
			$('#goods').find('tr[data-bind]').each(function() {
				goodsPrice += parseFloat($(this).children('td').eq(1).children('span').text()) * parseInt($(this).children('td').eq(2).text());
			});
			var freight = parseFloat($('#ss_price').text());
			var insurance = parseFloat($('#ss_insurance').text());
			var amount = (goodsPrice + freight + insurance).toFixed(2);
			$('#amount').html(goodsPrice.toFixed(2) + ' + ' + freight.toFixed(2) + ' + ' + insurance.toFixed(2) + ' = &yen;' + amount);
			$('#total').show();
		});
		$('#shipping_show').find('a').click(function() {
			$('#shipping_show').hide();
			$('#shipping_box').show();
		});
		$('#form_order').submit(function() {
			$('input[type="submit"]').prop('disabled', true);
			$.post(homeUrl + 'order/create', $('#form_order').serialize(), function(r) {
				showMsg(r.title, r.content);
				if (r.status == 1) {
					window.location.href = homeUrl + 'pay/payments/' + r.order_id + '/';
				} else {
					$('input[type="submit"]').prop('disabled', false);
				}
			}, 'json');
			return false;
		});
		$('#apply_area_p').change(function() {
			computeFreight();
			computeAmount();
		});
	});
</script>
