<header class="breadcrumbs">
	<a href="<?php echo $homeUrl; ?>manage">网站管理</a>
	<div class="breadcrumb_divider"></div>
	<a href="<?php echo $homeUrl; ?>manage2/list_shippings">配送方式管理</a>
	<div class="breadcrumb_divider"></div>
	<span class="current">编辑配送方式</span>
</header>
<article class="module width_full">
	<header><h3>编辑配送方式</h3></header>
	<form>
		<div class="module_content field">
			<ul>
				<li>
					<label>配送方式名称</label>
					<input type="text" name="shipping_name" value="<?php echo $node->shipping_name; ?>" />
				</li>
				<li>
					<label>配送方式描述</label>
					<textarea name="shipping_desc" class="textarea"><?php echo $node->shipping_desc; ?></textarea>
				</li>
				<li>
					<label>首重</label>
					<select name="base_weight">
						<?php foreach ($arrWeights as $w): ?>
							<option value="<?php echo $w[0]; ?>"<?php if ($w[0] == $node->base_weight): ?> selected="selected"<?php endif; ?>><?php echo $w[1]; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li>
					<label>续重单位</label>
					<select name="step_weight">
						<?php foreach ($arrWeights as $w): ?>
							<option value="<?php echo $w[0]; ?>"<?php if ($w[0] == $node->step_weight): ?> selected="selected"<?php endif; ?>><?php echo $w[1]; ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li>
					<label>计费类型</label>
					<input type="radio" name="price_type" value="0"<?php if ($node->price_type == 0): ?> checked="checked"<?php endif; ?> /> 全国统一计费
					<input type="radio" name="price_type" value="1"<?php if ($node->price_type == 1): ?> checked="checked"<?php endif; ?> /> 按配送地区计费
				</li>
				<li id="setpt0">
					<label>配送费用</label>
					首重费用：<input type="text" name="base_price" value="<?php echo $node->base_price; ?>" class="input_narrow" />
					续重费用：<input type="text" name="step_price" value="<?php echo $node->step_price; ?>" class="input_narrow" />
				</li>
				<li id="area_list">
					<?php $node->areas = (array) unserialize($node->areas); ?>
					<?php foreach ($node->areas as $k => $area): ?>
						<?php $label = $k == 0 ? '按配送地区设置' : ''; ?>
						<div>
							<label><?php echo $label; ?></label>
							<input type="hidden" name="area_id[]" value="<?php echo $area->area_id; ?>" />
							地区：<input type="text" name="area_name[]" value="<?php echo $area->area_name; ?>" readonly="readonly" />
							首重费用：<input type="text" name="base_price[]" value="<?php echo $area->base_price; ?>" class="input_narrow" />
							续重费用：<input type="text" name="step_price[]" value="<?php echo $area->step_price; ?>" class="input_narrow" />
							<a><img src="<?php echo $homeUrl; ?>assets_manage/images/icn_logout.png" /></a>
						</div>
					<?php endforeach; ?>
				</li>
				<li id="area_add">
					<label></label>
					<input type="button" id="area_add_btn" value="添加配送地区与运费" class="alt_btn" />
				</li>
				<li>
					<label>保价费用</label>
					<input type="text" name="insurance" value="<?php echo $node->insurance; ?>" />
				</li>
				<li>
					<label>货到付款</label>
					<input type="checkbox" name="support_cod" value="1"<?php if ($node->support_cod == 1): ?> checked="checked"<?php endif; ?> /> 支持
				</li>
				<li>
					<label>排序</label>
					<input type="text" name="list_order" value="<?php echo $node->list_order; ?>" />
				</li>
				<li>
					<label>状态</label>
					<input type="radio" name="status" value="0"<?php if ($node->status == 0): ?> checked="checked"<?php endif; ?> /> 开启
					<input type="radio" name="status" value="1"<?php if ($node->status == 1): ?> checked="checked"<?php endif; ?> /> 关闭
				</li>
				<li class="clear"></li>
			</ul>
		</div>
		<footer>
			<div class="submit_link">
				<input type="hidden" name="fid" value="<?php echo $node->id; ?>" />
				<input type="submit" value="提交" class="alt_btn" />
				<input type="reset" value="重置" />
				<input type="button" value="取消" id="cancel" />
			</div>
		</footer>
	</form>
</article>
<div id="area_template" style="display:none">
	<div>
		<label></label>
		<input type="hidden" name="area_id[]" value="" />
		地区：<input type="text" name="area_name[]" value="" readonly="true" />
		首重费用：<input type="text" name="base_price[]" value="" class="input_narrow" />
		续重费用：<input type="text" name="step_price[]" value="" class="input_narrow" />
		<a><img src="<?php echo $homeUrl; ?>assets_manage/images/icn_logout.png" /></a>
	</div>
</div>
<link href="<?php echo $homeUrl; ?>assets_manage/css/area.css" rel="stylesheet" />
<script>
	$(function() {
		var homeUrl = '<?php echo $homeUrl; ?>',
			toList = false,
			fid = '<?php echo $node->id; ?>';
		if ($('input[name="price_type"]:checked').val() == '0') {
			$('#setpt0').show();
			$('#area_list').hide();
			$('#area_add').hide();
		} else {
			$('#setpt0').hide();
			$('#area_list').show();
			$('#area_add').show();
		}
		$('input[name="price_type"]').click(function() {
			if ($(this).val() == '0') {
				$('#setpt0').show();
				$('#area_list').hide();
				$('#area_add').hide();
			} else if ($(this).val() == '1') {
				$('#setpt0').hide();
				$('#area_list').show();
				$('#area_add').show();
			}
		});
		$('#area_list').on('click', 'input[name="area_name[]"]', function() {
			$('#area_box').remove();
			var from = $('input[name="area_name[]"]').index(this);
			var selected = $('input[name="area_id[]"]').eq(from).val();
			$.post(homeUrl + 'manage2/get_linkages', {
				"node_id": 1,
				"selected": selected
			}, function(r) {
				var node = '';
				for (var i = 0; i < r.data.length; i++) {
					var button = r.data[i].has_child == '1' ? '<span><input type="image" data-nid="' + r.data[i].id + '" src="' + homeUrl + 'assets_manage/images/icn_edit.png" title="展开" />' : '<span>';
					r.data[i].checked = r.data[i].checked ? ' checked="checked"' : '';
					node += '<li>' + button + '<input type="checkbox" data-area="' + r.data[i].id + '"' + r.data[i].checked + '/>' + r.data[i].name + '</span></li>';
				}
				var area = '<div id="area_box" style="display:inline">\
				<h3>选择配送地区</h3>\
				<div class="msg_box">\
					<ul>' + node + '</ul>\
				</div>\
				<h3>\
					<span class="alt_btn" data-ok="' + from + '">确定</span>\
					<span class="ccl_btn" data-cancel="' + from + '">取消</span>\
				</h3>\
			</div>';
				$('body').append(area);
			}, 'json');
		});
		$('#area_add_btn').click(function() {
			$('#area_list').append($('#area_template div').clone());
		});
		$('#area_list').on('click', 'a', function() {
			if ($('#area_list > div').size() == 1)
				$('#area_list > div:first').children('a').remove();
			else
				$(this).parent().remove();
		});
		$('form').submit(function() {
			if ($('input[name="price_type"]:checked').val() == '1') {
				$('input[name="base_price"]').remove();
				$('input[name="step_price"]').remove();
			} else {
				$('input[name="area_id[]"]').remove();
				$('input[name="area_name[]"]').remove();
				$('input[name="base_price[]"]').remove();
				$('input[name="step_price[]"]').remove();
			}
			$('input[type="submit"]').prop('disabled', true);
			$.post(homeUrl + 'manage2/edit_shipping', $('form').serialize(), function(r) {
				if (r.status <= 3)
					toList = true;
				showMsg(r.title, r.content);
				$('input[type="submit"]').prop('disabled', false);
			}, 'json');
			return false;
		});
		$('body').on('click', '.close-me', function() {
			if (toList)
				window.location.href = homeUrl + 'manage2/list_shippings';
		});
		$('#cancel').click(function() {
			window.location.href = homeUrl + 'manage2/list_shippings';
		});
		// 配送地区选择框
		$('body').on('click', 'input[data-nid]', function() {
			var node = $(this).parent();
			if (node.siblings().is('ul')) {
				node.siblings().toggle();
			} else {
				$.post(homeUrl + 'manage2/get_linkages', {
					"node_id": $(this).attr('data-nid')
				}, function(r) {
					var area = '';
					for (var i = 0; i < r.data.length; i++) {
						var button = r.data[i].has_child == '1' ? '<span><input type="image" data-nid="' + r.data[i].id + '" src="' + homeUrl + 'assets_manage/images/icn_edit.png" title="展开" />' : '<span>';
						r.data[i].checked = r.data[i].checked ? ' checked="checked"' : '';
						area += '<li>' + button + '<input type="checkbox" data-area="' + r.data[i].id + '"' + r.data[i].checked + '/>' + r.data[i].name + '</span></li>';
					}
					node.after('<ul>' + area + '</ul>');
					var from = $('#area_box').find('span[data-ok]').attr('data-ok');
					var selected = $('input[name="area_id[]"]').eq(from).val().split(',');
					for (var i = 0; i < selected.length; i++) {
						$('#area_box').find('input[data-area="' + selected[i] + '"]').prop('checked', true);
					}
				}, 'json');
			}
		});
		$('body').on('click', 'input[data-area]', function() {
			var isChecked = $(this).is(':checked');
			$(this).parent().parent().find('input[data-area]').each(function() {
				$(this).prop('checked', isChecked);
			});
			var parent = $(this).parent().parent().parent().siblings('span');
			parent.children('input[data-area]').prop('checked', parent.siblings().find('input[data-area]:checked').size() != 0);
			var grandParent = parent.parent().parent().siblings('span');
			grandParent.children('input[data-area]').prop('checked', grandParent.siblings().find('input[data-area]:checked').size() != 0);
		});
		$('body').on('click', 'span[data-ok]', function() {
			var areaIds = areaNames = '';
			$('input[data-area]:checked').each(function() {
				areaIds += $(this).attr('data-area') + ',';
				areaNames += $(this).parent().text() + ',';
			});
			$('input[name="area_name[]"]').eq($(this).attr('data-ok')).val(areaNames.slice(0, -1));
			$('input[name="area_id[]"]').eq($(this).attr('data-ok')).val(areaIds.slice(0, -1));
			$('#area_box').hide();
		});
		$('body').on('click', 'span[data-cancel]', function() {
			$('#area_box').hide();
		});
	});
</script>
