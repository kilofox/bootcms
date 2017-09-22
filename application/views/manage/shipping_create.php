<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<a href="<?php echo $homeUrl; ?>manage2/list_shippings">配送方式管理</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">创建新配送方式</span>
</header>
<article class="module width_full">
	<header><h3>创建新配送方式</h3></header>
	<form>
		<div class="module_content field">
			<ul>
				<li>
					<label>配送方式名称</label>
					<input type="text" name="shipping_name" value="" />
				</li>
				<li>
					<label>配送方式描述</label>
					<textarea name="shipping_desc" class="textarea"></textarea>
				</li>
				<li>
					<label>首重</label>
					<select name="base_weight">
						<?php foreach ($arrWeights as $w): ?>
							<option value="<?php echo $w[0]; ?>"><?php echo $w[1]; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li>
					<label>续重单位</label>
					<select name="step_weight">
						<?php foreach ($arrWeights as $w): ?>
							<option value="<?php echo $w[0]; ?>"><?php echo $w[1]; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li>
					<label>首重费用</label>
					<input type="text" name="base_price" value="" />
				</li>
				<li>
					<label>续重费用</label>
					<input type="text" name="step_price" value="" />
				</li>
				<li>
					<label>计费类型</label>
					<input type="radio" name="price_type" value="0" /> 全国统一计费
					<input type="radio" name="price_type" value="1" /> 按配送地区计费
				</li>
				<li>
					<label>保价费用</label>
					<input type="text" name="insurance" value="" />
				</li>
				<li>
					<label>货到付款</label>
					<input type="checkbox" name="support_cod" value="1" />
				</li>
				<li>
					<label>排序</label>
					<input type="text" name="list_order" value="" />
				</li>
				<li>
					<label>状态</label>
					<input type="radio" name="status" value="0" /> 开启
					<input type="radio" name="status" value="1" /> 关闭
				</li>
			</ul>
		</div>
		<footer>
			<div class="submit_link">
				<input type="submit" value="创建配送方式" class="alt_btn" />
			</div>
		</footer>
	</form>
</article>
<script>
	$(function() {
		var homeUrl = '<?php echo $homeUrl; ?>',
			refresh = false;
		$('form').submit(function() {
			$('input[type="submit"]').prop('disabled', true);
			$.post(homeUrl + 'manage2/create_shipping', $('form').serialize(), function(r) {
				showMsg(r.title, r.content);
				if (r.status === 0)
					refresh = true;
				$('input[type="submit"]').prop('disabled', false);
			}, 'json');
			return false;
		});
		$('body').on('click', '.close-me', function() {
			if (refresh)
				window.location.href = homeUrl + 'manage2/list_shippings';
		});
	});
</script>
